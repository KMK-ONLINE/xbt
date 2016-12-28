<?php
namespace Xbt;

use Mockery;

class TagNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_class_implements_NestableNode()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[]])->makePartial();

        $children = Mockery::mock(NodeList::class, [[]])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertTrue($tagNode instanceof NestableNode);
    }

    public function test_getAttributes()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[]])->makePartial();

        $children = Mockery::mock(NodeList::class, [[]])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals($attributes, $tagNode->getAttributes());
    }

    public function test_render_tag_without_attributes_nor_children()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[]])->makePartial();

        $children = Mockery::mock(NodeList::class, [[]])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1 />', $tagNode->render());
    }

    public function test_render_tag_with_attributes_and_no_children()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[':foo' => Mockery::mock(StringNode::class)]])->makePartial();
        $attributes->shouldReceive('render')->andReturn('foo="bar"');

        $children = Mockery::mock(NodeList::class, [[]])->makePartial();

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1 foo="bar" />', $tagNode->render());
    }

    public function test_render_tag_without_attributes_but_with_children()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[]])->makePartial();

        $grandChildren = Mockery::mock(NodeList::class, [[]]);

        $child = Mockery::mock(TagNode::class, [':p', $attributes, $grandChildren])->makePartial();
        $child->shouldReceive('render')->andReturn('<p />');

        $children = Mockery::mock(NodeList::class, [[$child, $child]])->makePartial();
        $children->shouldReceive('render')->andReturn('<p /><p />');

        $tagNode = new TagNode(':h1', $attributes, $children);

        $this->assertEquals('<h1><p /><p /></h1>', $tagNode->render());
    }

    public function test_renderChildren_renders_only_children_without_the_containing_root_node()
    {
        $attributes = Mockery::mock(TagAttributes::class, [[]])->makePartial();

        $grandChildren = Mockery::mock(NodeList::class, [[Mockery::mock(TextNode::class)->makePartial()->shouldReceive('render')->andReturn('inside of p')]])->makePartial();
        $grandChildren->shouldReceive('render')->andReturn('inside of p');

        $children = new NodeList([
            new TextNode('begin'),
                new TagNode(':p', $attributes, $grandChildren),
            new TextNode('end'),
        ]);

        $expected = 'begin<p>inside of p</p>end';

        $tagNode = Mockery::mock(TagNode::class, [':h1', $attributes, $children])->makePartial();
        $tagNode->shouldReceive('getChildren')->andReturn($children);

        $this->assertEquals($expected, $tagNode->renderChildren());
    }
}

