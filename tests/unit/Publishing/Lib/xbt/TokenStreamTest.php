<?hh
namespace App\Publishing\Lib\xbt;

use Mockery;

class TokenStreamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tokens = [
            new Token(T_XHP_LABEL, ':h1', 1),
            new Token(T_XHP_LABEL, ':h2', 1),
            new Token(T_XHP_LABEL, ':h3', 1),
            new Token(Token::T_XHP_EOF),
        ];
    }

    public function test_ensure_stream_contains_at_least_one_token_when_given_empty_tokens()
    {
        $stream = new TokenStream([]);
        $this->assertEquals(Token::T_XHP_EOF, $stream->getCurrent()->type);
    }

    public function test_ensure_stream_contains_eof_token_when_there_is_none_at_the_end()
    {
        $tokens = $this->tokens;

        array_pop($tokens);

        $stream = new TokenStream($tokens);

        $tokenStream = $stream->getTokens();

        $this->assertEquals(Token::T_XHP_EOF, $tokenStream[count($tokenStream)-1]->type);
    }

    public function test_getTokens_returns_all_token_in_stream()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertEquals($this->tokens, $stream->getTokens());
    }

    public function test_getCurrent_returns_current_token_in_stream()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertEquals($this->tokens[0], $stream->getCurrent());
    }

    public function test_getPosition_returns_current_position_in_stream()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertEquals(0, $stream->getPosition());
    }

    public function test_isEOF_returns_true_when_the_current_token_is_of_type_T_EOF()
    {
        $stream = new TokenStream($this->tokens);
        $stream->next();
        $stream->next();
        $stream->next();
        $this->assertTrue($stream->isEOF());
    }

    public function test_isEOF_returns_false_when_the_current_token_is_not_of_type_T_EOF()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertFalse($stream->isEOF());
    }

    public function test_next_returns_current_token_and_advance_position_to_the_next_token()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertEquals($this->tokens[0], $stream->next());
        $this->assertEquals($this->tokens[1], $stream->next());
    }

    public function test_peek_returns_next_token_without_advancing_position()
    {
        $stream = new TokenStream($this->tokens);
        $this->assertEquals($this->tokens[1], $stream->peek());
        $this->assertEquals($this->tokens[0], $stream->next());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_peek_throws_error_if_supplied_with_noninteger()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':foobar'),
            new Token(Token::T_XHP_EOF),
        ];

        $stream = new TokenStream($tokens);
        $stream->peek(':foobar');
    }

    public function test_peek_returns_the_very_last_token_when_there_are_no_more_tokens_ahead()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':foobar'),
            new Token(Token::T_XHP_EOF),
        ];

        $stream = new TokenStream($tokens);
        $this->assertEquals($tokens[1], $stream->peek());
    }

    public function test_skip_over_white_space_tokens()
    {
        $tokens = [
            new Token(T_WHITESPACE, ' '),
            new Token(T_WHITESPACE, "\t"),
            new Token(T_WHITESPACE, "\n"),
            new Token(T_XHP_LABEL, ':p'),
            new Token(Token::T_XHP_EOF),
        ];
        $stream = new TokenStream($tokens);
        $stream->skip(T_WHITESPACE);

        $this->assertEquals(T_XHP_LABEL, $stream->getCurrent()->type);
        $this->assertEquals(':p', $stream->getCurrent()->value);
    }

    public function test_match_type_and_value()
    {
        $stream = new TokenStream([
            new Token(T_XHP_LABEL, ':h1', 1),
        ]);
        $this->assertTrue($stream->match(T_XHP_LABEL, ':h1'));
        $this->assertFalse($stream->match(T_XHP_LABEL, ':h2'));
    }

    public function test_match_just_type()
    {
        $stream = new TokenStream([
            new Token(T_XHP_LABEL, ':h2', 1),
        ]);
        $this->assertTrue($stream->match(T_XHP_LABEL));
        $this->assertFalse($stream->match(T_XHP_ATTRIBUTE));
    }

    public function test_expect_calls_match_and_next_when_current_token_is_what_it_expects()
    {
        $tokens = [
            new Token(Token::T_XHP_EOF),
        ];

        $stream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $stream->shouldReceive('match')->once()->andReturn(true);
        $stream->shouldReceive('next')->once();

        $stream->expect(Token::T_XHP_EOF);
    }

    public function test_expect_on_type_and_return_expected_token_from_stream()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':h1', 1),
            new Token(T_XHP_LABEL, ':h2', 1),
        ];
        $stream = new TokenStream($tokens);
        $this->assertEquals($tokens[0], $stream->expect(T_XHP_LABEL));
        $this->assertEquals($tokens[1], $stream->expect(T_XHP_LABEL));
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_expect_throws_exception_on_unexpected_token()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':h1', 1),
            new Token(T_XHP_LABEL, ':h2', 1),
        ];
        $stream = new TokenStream($tokens);
        $this->assertEquals($tokens[0], $stream->expect(T_XHP_ATTRIBUTE));
        $this->assertEquals($tokens[1], $stream->expect(T_XHP_LABEL));
    }


    public function test_consume_calls_match_and_next_when_current_token_is_what_it_expects()
    {
        $tokens = [
            new Token(Token::T_XHP_EOF),
        ];

        $stream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $stream->shouldReceive('match')->once()->andReturn(true);
        $stream->shouldReceive('next')->once();

        $stream->consume(Token::T_XHP_EOF);
    }
}
