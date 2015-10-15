<?hh
namespace App\Lib\xbt;

use Mockery;

class TagAttributesTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_class_implements_ArrayAccess()
    {
        $expression = Mockery::mock(StringNode::class)->makePartial();
        $expression->shouldReceive('render')->andReturn('"this is just a string"');

        $attributes = new TagAttributes(Map<string, ExpressionNode> {':foo' => $expression});

        $this->assertTrue($attributes instanceof \ArrayAccess);
    }

    public function test_render_attributes_as_key_value_string()
    {
        $text = '"this is just a string"';
        $expression = Mockery::mock(StringNode::class)->makePartial();
        $expression->shouldReceive('render')->andReturn($text);

        $attributes = new TagAttributes(Map<string, ExpressionNode> {':foo' => $expression});
        $this->assertEquals('foo=' . $text, $attributes->render());
    }
}

