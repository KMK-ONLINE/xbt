<?hh

namespace Lib\xbt;

use Mockery as m;

class TagNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_class_implements_NestableNode()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertTrue($tagNode instanceof NestableNode);
    }

    public function test_getAttributes()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals($attributes, $tagNode->getAttributes());
    }

    public function test_render_tag_without_attributes_nor_children()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1 />', $tagNode->render());
    }

    public function test_render_tag_with_attributes_and_no_children()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':foo' => m::mock(StringNode::class)}])->makePartial();
        $attributes->shouldReceive('render')->andReturn('foo="bar"');

        $children = m::mock(NodeList::class, [Vector<Node> {}])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1 foo="bar" />', $tagNode->render());
    }

    public function test_render_tag_without_attributes_but_with_children()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $grandChildren = m::mock(NodeList::class, [Vector<Node> {}]);

        $child = m::mock(TagNode::class, [':p', $attributes, $grandChildren])->makePartial();
        $child->shouldReceive('render')->andReturn('<p />');

        $children = m::mock(NodeList::class, [Vector<Node> {$child, $child}])->makePartial();
        $children->shouldReceive('render')->andReturn('<p /><p />');

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1><p /><p /></h1>', $tagNode->render());
    }

    public function test_renderChildren_renders_only_children_without_the_containing_root_node()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $grandChildren = m::mock(NodeList::class, [Vector<Node> {m::mock(TextNode::class)->makePartial()->shouldReceive('render')->andReturn('inside of p')}])->makePartial();
        $grandChildren->shouldReceive('render')->andReturn('inside of p');

        $children = new NodeList(Vector<Node> {
            new TextNode('begin'),
                new TagNode(':p', $attributes, $grandChildren),
            new TextNode('end'),
        });

        $expected = 'begin<p>inside of p</p>end';

        $tagNode = m::mock(TagNode::class, [':h1', $attributes, $children])->makePartial();
        $tagNode->shouldReceive('getChildren')->andReturn($children);

        $this->assertEquals($expected, $tagNode->renderChildren());
    }
}

