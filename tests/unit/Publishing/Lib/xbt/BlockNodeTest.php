<?hh

namespace App\Publishing\Lib\xbt;

use Mockery as m;

use App\Publishing\Lib\xbt\BlockNode;
use App\Publishing\Lib\xbt\TagAttributes;
use Node;
use App\Publishing\Lib\xbt\StringNode;
use App\Publishing\Lib\xbt\NodeList;

class BlockNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_block_name_must_not_contain_dots()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => m::mock(StringNode::class, ['"foo.bar"'])->makePartial()}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $block = new BlockNode($attributes, $children);
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_block_name_must_not_contain_dashes()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => m::mock(StringNode::class, ['"foo-bar"'])->makePartial()}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $block = new BlockNode($attributes, $children);
    }

    /**
     * @expectedException App\Publishing\Lib\xbt\SyntaxError
     */
    public function test_block_name_must_not_contain_anything_other_than_alphanumeric_and_underscores()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => m::mock(StringNode::class, ['"^foo@bar!"'])->makePartial()}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $block = new BlockNode($attributes, $children);
    }

    public function test_render_renders_the_block_call()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => m::mock(StringNode::class, ['"foo_bar"'])->makePartial()}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $block = new BlockNode($attributes, $children);

        $expected =<<<'EXPECTED'
{call_user_func($this->resolveBlock('foo_bar'), $__params)}
EXPECTED;

        $this->assertEquals($expected, $block->render());
    }

    public function test_renderBody_renders_the_body_of_the_block()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => m::mock(StringNode::class, ['"foo_bar"'])->makePartial()}])->makePartial();

        $p = m::mock(Node::class)->makePartial();
        $p->shouldReceive('render')->andReturn('<p>This is just a paragraph</p>');

        $children = m::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();

        $block = new BlockNode($attributes, $children);

        $expected =<<<'EXPECTED'
        'foo_bar' => function($__params = []) {
            extract($__params);
            return <x:frag><p>This is just a paragraph</p></x:frag>;
        },
EXPECTED;

        $this->assertEquals($expected, $block->renderBody());
    }
}

