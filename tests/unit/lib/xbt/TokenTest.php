<?hh

namespace App\Lib\xbt;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function test_throws_exception_when_instantiated_with_unrecognized_string()
    {
        $token = new Token(Token::T_XHP_TOKEN, '%$%^');

        $this->assertEquals(Token::T_XHP_TOKEN, $token->type);
    }

    public function test_match_returns_true_if_they_are_of_the_same_type_and_value()
    {
        $token = new Token(T_XHP_LABEL, ':h1', 1);

        $this->assertTrue($token->match(T_XHP_LABEL));
    }

    public function test_match_returns_false_if_they_are_not_of_the_same_type_or_value()
    {
        $token = new Token(T_XHP_LABEL, ':h1', 1);

        $this->assertFalse($token->match(T_XHP_LABEL, ':h2'));

        $token = new Token(T_XHP_LABEL, ':h2', 1);

        $this->assertFalse($token->match(T_XHP_TEXT, ':h2'));
    }

    public function test_toString_returns_token_value_as_string()
    {
        $token = new Token(T_XHP_TEXT, 'what the fug', 1);
        $this->assertTrue(is_string($token->toString()));
        $this->assertEquals('what the fug', $token->toString());
    }

    public function test_toString_returns_xhp_label_token_value_as_substring()
    {
        $token = new Token(T_XHP_LABEL, ':h1', 1);
        $this->assertTrue(is_string($token->toString()));
        $this->assertEquals('h1', $token->toString());
    }
}
