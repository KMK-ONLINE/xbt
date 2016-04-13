<?hh
namespace App\Publishing\Lib\xbt;

use Mockery;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_getStream_returns_TokenStream()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $this->assertEquals($tokenStream, $parser->getStream());
    }

    public function test_getBlocks_returns_vector_of_BlockNode()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $this->assertTrue($parser->getBlocks() instanceof Map<string, BlockNode>);
    }

    public function test_addBlock_adds_a_block()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $block = Mockery::mock(BlockNode::class)->makePartial();
        $block->shouldReceive('getNameAttribute')->andReturn('acegol');
        $parser->addBlock($block);

        $this->assertEquals(1, count($parser->getBlocks()));
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_addBlock_throws_an_exception_if_theres_a_block_with_a_same_name()
    {
        $tokenStream = Mockery::mock(TokenStream::class, [[]])->makePartial();
        $parser = new Parser($tokenStream);

        $block = Mockery::mock(BlockNode::class)->makePartial();
        $block->shouldReceive('getNameAttribute')->andReturn('acegol');
        $parser->addBlock($block);

        $duplicateBlock = Mockery::mock(BlockNode::class)->makePartial();
        $duplicateBlock->shouldReceive('getNameAttribute')->andReturn('acegol');
        $parser->addBlock($duplicateBlock);
    }

    public function test_parse_returns_a_Template()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $this->assertTrue($parser->parse() instanceof Template);
    }

    public function test_parseDelimitedExpression_returns_DelimitedExpressionNode_object()
    {
        $tokens = [
            new Token(Token::T_XHP_BRACE_OPEN, '{'),
            new Token(T_LNUMBER, '1'),
            new Token(Token::T_XHP_TOKEN, '+'),
            new Token(T_LNUMBER, '2'),
            new Token(Token::T_XHP_BRACE_CLOSE, '}'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $expressionNode = $parser->parseDelimitedExpression();

        $this->assertTrue($expressionNode instanceof DelimitedExpressionNode);

        $this->assertEquals('{1+2}', $expressionNode->render());
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseDelimitedExpression_throws_exception_when_not_properly_closed()
    {
        $tokens = [
            new Token(Token::T_XHP_BRACE_OPEN, '{'),
            new Token(T_LNUMBER, '1'),
            new Token(Token::T_XHP_TOKEN, '+'),
            new Token(T_LNUMBER, '2'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $parser->parseDelimitedExpression();
    }

    public function test_parseText_returns_TextNode_object()
    {
        $tokens = [
            new Token(T_XHP_TEXT ,'this is the text inside'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $textNode = $parser->parseText();

        $this->assertTrue($textNode instanceof TextNode);

        $this->assertEquals('this is the text inside', $textNode->render());
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseText_throws_exception_when_not_properly_bound()
    {
        $tokens = [
            new Token(T_XHP_TEXT ,'this is the text inside'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $parser->parseText();
    }

    public function test_parseComment_returns_CommentNode_object()
    {
        $tokens = [
            new Token(T_COMMENT, '<!-- hoo haa -->'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $commentNode = $parser->parseComment();

        $this->assertTrue($commentNode instanceof CommentNode);

        $this->assertEquals('<raw-string>{\'<!-- hoo haa -->\'}</raw-string>', $commentNode->render());
    }

    public function test_parseIncludeTag_returns_an_includeNode_and_render_properly()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:include'),
            new Token(T_WHITESPACE, ' '),
            new Token(T_XHP_LABEL, ':template'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(T_XHP_TEXT, '"sidebar"'),
            new Token(T_WHITESPACE, ' '),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];
        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);
        $includeNode = $parser->parseIncludeTag();

        $this->assertTrue($includeNode instanceof IncludeNode);
        $this->assertEquals('<raw-string>{(true) ? $__params[\'__env\']->make(\'sidebar\', [])->render() : \'\'}</raw-string>', $includeNode->render());
    }

    public function test_parseTag_a_specific_tag_returns_TagNode_object_and_renders_properly()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':p'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $tagNode = $parser->parseTag(':p');

        $this->assertTrue($tagNode instanceof TagNode);

        $this->assertEquals('<p />', $tagNode->render());
    }

    public function test_parseTag_any_tag_returns_TagNode_object_and_renders_properly()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_XHP_TEXT, 'i am being emphasized'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $tagNode = $parser->parseTag(':em');

        $this->assertTrue($tagNode instanceof TagNode);

        $this->assertEquals('<em>i am being emphasized</em>', $tagNode->render());
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseTag_throws_an_exception_when_trying_to_parse_a_malformed_closing_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_XHP_TEXT, 'i am being emphasized'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $parser->parseTag(':em');
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseTag_throws_an_exception_when_trying_to_parse_a_malformed_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $parser->parseTag(':em');
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseTag_throws_an_exception_when_trying_to_parse_an_unclosed_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $parser->parseTag(':em');
    }

    public function test_parseTag_returns_TagNode_object_for_nested_tags()
    {
        $text = 'this is just your everyday standard text element';

        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':p'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TEXT, 'begin '),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':b'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TEXT, $text),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':b'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TEXT, ' end'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':p'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $tagNode = $parser->parseTag(':p');

        $this->assertTrue($tagNode instanceof TagNode);

        $this->assertEquals("<p>begin <b>{$text}</b> end</p>", $tagNode->render());
    }

    public function test_parseTagAttributes_returns_an_instance_of_TagAttributes()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':class'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(T_XHP_TEXT, '"foobar"'),
            new Token(T_WHITESPACE, ' '),
            new Token(T_XHP_LABEL, ':data-origin'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(Token::T_XHP_BRACE_OPEN, '{'),
            new Token(T_VARIABLE, '$foobar'),
            new Token(Token::T_XHP_BRACE_CLOSE, '}'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_XHP_TEXT, 'foobar'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':p'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);
        $attributes = $parser->parseTagAttributes();

        $this->assertTrue($attributes instanceof TagAttributes);
        $this->assertEquals('"foobar"', $attributes->getAttributes()[':class']->render());
        $this->assertEquals('{$foobar}', $attributes->getAttributes()[':data-origin']->render());
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseTagAttributes_throws_an_exception_when_the_attribute_value_is_missing()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':class'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);
        $parser->parseTagAttributes();
     }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parseTagAttributes_throws_an_exception_when_the_attribute_value_is_malformed()
    {
        $tokens = [
            new Token(T_XHP_LABEL, ':key'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(T_XHP_LABEL, ':value'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);
        $parser->parseTagAttributes();
    }

    public function test_matchClosingTag_consume_closing_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':p'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);
        $this->assertTrue($parser->matchClosingTag(':p'));
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parse_throws_exception_when_template_tag_is_nested()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $parser->parse();
    }

    public function test_parse_parent_tag_is_inside_a_block_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:block'),
            new Token(T_XHP_LABEL, ':name'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(T_XHP_TEXT, '"blockname"'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:parent'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:block'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $block = $parser->parseTag(':xbt:block');

        $parent = $block->getChildren()->getNodes()[0];

        $this->assertTrue($parent instanceof ParentNode);

        $this->assertEquals('blockname', $parent->getBlockName());
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parse_parent_tag_is_outside_of_any_block_tag()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:parent'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $parser->parse();
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_parse_parent_tag_has_children()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:block'),
            new Token(T_XHP_LABEL, ':name'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(T_XHP_TEXT, '"blockname"'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':xbt:parent'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TEXT, 'this is inside of :xbt:parent'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:parent'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:block'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':xbt:template'),
            new Token(T_XHP_TAG_GT, '>'),

            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();

        $parser = new Parser($tokenStream);

        $parser->parse();
    }

    public function test_parseTag_the_closing_tag_contains_whitespace_between_closing_label_and_GT()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_XHP_TEXT, 'THE CLOSING TAG CONTAINS WHITESPACE BETWEEN CLOSING LABEL AND GT'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_WHITESPACE, ' '),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $tagNode = $parser->parseTag(':em');

        $this->assertTrue($tagNode instanceof TagNode);
    }

    public function test_parseTag_the_closing_tag_contains_whitespace_between_slash_and_closing_label()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_XHP_TEXT, 'THE CLOSING TAG CONTAINS WHITESPACE BETWEEN SLASH AND CLOSING LABEL'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_WHITESPACE, ' '),
            new Token(T_XHP_LABEL, ':em'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $tagNode = $parser->parseTag(':em');

        $this->assertTrue($tagNode instanceof TagNode);
    }

    public function test_parseTag_kagak_mencret_kalo_ada_kurung_kurawal_di_dalem_kurung_kurawal()
    {
        $tokens = [
            new Token(Token::T_XHP_BRACE_OPEN, '{'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, ':foobar'),
            new Token(T_WHITESPACE, ' '),
            new Token(T_XHP_LABEL, ':pukimak'),
            new Token(Token::T_XHP_ATTRIBUTE_EQUAL, '='),
            new Token(Token::T_XHP_BRACE_OPEN, '{'),
            new Token(T_VARIABLE, '$blockname'),
            new Token(Token::T_XHP_BRACE_CLOSE, '}'),
            new Token(T_WHITESPACE, ' '),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(Token::T_XHP_BRACE_CLOSE, '}'),
            new Token(Token::T_XHP_EOF),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $expr = $parser->parseDelimitedExpression();

        $this->assertEquals('{<foobar pukimak={$blockname} />}', $expr->render());
    }

    public function test_parseTag_can_parse_xml_comments()
    {
        $tokens = [
            new Token(T_XHP_TAG_LT, '<'),
            new Token(T_XHP_LABEL, 'div'),
            new Token(T_XHP_TAG_GT, '>'),
            new Token(T_COMMENT, '<!-- foo bar -->'),
            new Token(T_XHP_TAG_LT, '<'),
            new Token(Token::T_XHP_TAG_SLASH, '/'),
            new Token(T_XHP_LABEL, 'div'),
            new Token(T_XHP_TAG_GT, '>'),
        ];

        $tokenStream = Mockery::mock(TokenStream::class, [$tokens])->makePartial();
        $tokenStream->shouldReceive('getTokens')->andReturn($tokens);

        $parser = new Parser($tokenStream);

        $expr = $parser->parseTag(':div');

        $this->assertEquals('<div><raw-string>{\'<!-- foo bar -->\'}</raw-string></div>', $expr->render());
    }
}

