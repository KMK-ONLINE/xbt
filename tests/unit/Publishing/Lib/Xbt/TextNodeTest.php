<?hh
namespace App\Publishing\Lib\Xbt;

class TextNodeTest extends \PHPUnit_Framework_TestCase
{
    public function test_class_implements_Node()
    {
        $textNode = new TextNode('this is a simple text');

        $this->assertTrue($textNode instanceof Node);
    }

    public function test_render_tag_returns_expression_as_is()
    {
        $expr = 'this is a simple text {"this is an expression"} and another simple text';

        $textNode = new TextNode($expr);

        $this->assertEquals($expr, $textNode->render());
    }

    public function test_isWhitespace_returns_true_if_text_consists_of_only_whitespaces()
    {
        $expr = "  \t \n  ";

        $textNode = new TextNode($expr);

        $this->assertTrue($textNode->isWhitespace());
    }

    public function test_isWhitespace_returns_false_if_text_does_not_consist_of_only_whitespaces()
    {
        $expr = "  \t . \n  ";

        $textNode = new TextNode($expr);

        $this->assertFalse($textNode->isWhitespace());
    }
}

