<?hh

namespace Lib\xbt;

use Mockery as m;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_include_doctype_when_doctype_attribute_is_set_to_true()
    {
        $node = m::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = m::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = m::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = m::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = m::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $doctype = m::mock(StringNode::class, ['"true"'])->makePartial();

        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':doctype' => $doctype}])->makePartial();

        $template = new Template($attributes, $children, $blocks);

        $class = '__xbt_' . md5('foobar_with_doctype');

        $expected =<<<EXPECTED
class $class
{
    public function __construct(\$params = [])
    {
        \$this->params = \$params;
    }

    public function render()
    {
        extract(\$this->params);
        return <x:doctype>foobar</x:doctype>;
    }

    public function block_for_the_win()
    {
        extract(\$this->params);
        return <x:frag><p /></x:frag>;
    }
}
EXPECTED;

        $this->assertEquals($expected, $template->compile($class, null));

    }

    /**
     * @expectedException Lib\xbt\SyntaxError
     */
    public function test_include_doctype_when_doctype_attribute_is_set_to_something_other_than_a_literal_true_or_false_string()
    {
        $node = m::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = m::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = m::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = m::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = m::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $doctype = m::mock(ExpressionNode::class, ['{"true"}'])->makePartial();

        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':doctype' => $doctype}])->makePartial();

        $template = new Template($attributes, $children, $blocks);
    }

    public function test_compile_ouputs_class()
    {

        $node = m::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = m::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = m::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = m::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = m::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $template = new Template($attributes, $children, $blocks);

        $class = '__xbt_' . md5('foobar');

        $expected =<<<EXPECTED
class $class
{
    public function __construct(\$params = [])
    {
        \$this->params = \$params;
    }

    public function render()
    {
        extract(\$this->params);
        return <x:frag>foobar</x:frag>;
    }

    public function block_for_the_win()
    {
        extract(\$this->params);
        return <x:frag><p /></x:frag>;
    }
}
EXPECTED;

        $this->assertEquals($expected, $template->compile($class, null));
    }

    /**
     * @expectedException Lib\xbt\SyntaxError
     */
    public function test_extends_attribute_must_be_a_string_node()
    {
        $attributes = new TagAttributes(Map<string, ExpressionNode> {':extends' => new DelimitedExpressionNode('{1}')});
        $children   = new NodeList(Vector<Node> {});
        $blocks     = Map<string, BlockNode> {};
        $template   = new Template($attributes, $children, $blocks);
    }
}

