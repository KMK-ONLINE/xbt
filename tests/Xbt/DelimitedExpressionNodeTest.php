<?php
namespace App\Publishing\Lib\Xbt;

class DelimitedExpressionNodeTest extends \PHPUnit_Framework_TestCase
{
    public function test_class_implements_Node()
    {
        $tagNode = new DelimitedExpressionNode('{"this is a delimited expression"}');

        $this->assertTrue($tagNode instanceof Node);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_delimited_expression()
    {
        $stringNode = new StringNode('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_expression_without_closing_braces()
    {
        $stringNode = new StringNode('{"foobar"');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_expression_without_opening_braces()
    {
        $stringNode = new StringNode('"foobar"}');
    }

    public function test_render_tag_returns_expression_as_is()
    {
        $expr = '{"this is a delimited expression"}';

        $tagNode = new DelimitedExpressionNode($expr);

        $this->assertEquals($expr, $tagNode->render());
    }
}

