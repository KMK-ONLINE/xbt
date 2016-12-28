<?php
namespace App\Publishing\Lib\Xbt;

class StringNodeTest extends \PHPUnit_Framework_TestCase
{
    public function test_class_implements_Node()
    {
        $stringNode = new StringNode('"foobar"');

        $this->assertTrue($stringNode instanceof Node);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_string()
    {
        $stringNode = new StringNode('foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_string_without_closing_quotes()
    {
        $stringNode = new StringNode('"foobar');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_class_throws_exception_if_constructor_is_given_invalid_string_without_opening_quotes()
    {
        $stringNode = new StringNode('foobar"');
    }

    public function test_render_returns_a_StringNode_representation()
    {
        $string = '"foobar"';

        $stringNode = new StringNode($string);

        $this->assertEquals($string, $stringNode->render());
    }

    public function test_toString_returns_the_string_without_enclosing_quotes()
    {
        $string = 'foobar';

        $stringNode = new StringNode("\"$string\"");

        $this->assertEquals($string, (string) $stringNode);
    }
}

