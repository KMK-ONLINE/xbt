<?hh
namespace App\Lib\xbt;

use Mockery;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_include_doctype_when_doctype_attribute_is_set_to_true()
    {
        $node = Mockery::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = Mockery::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = Mockery::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = Mockery::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = Mockery::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $doctype = Mockery::mock(StringNode::class, ['"true"'])->makePartial();

        $attributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':doctype' => $doctype}])->makePartial();

        $template = new Template($attributes, $children, $blocks);

        $class = '__xbt_' . md5('foobar_with_doctype');

        $expected =<<<EXPECTED
return new \App\Lib\\xbt\TemplateRuntime(
    null,
    function(\$__params = []) {
        extract(\$__params);
        return <x:doctype>foobar</x:doctype>;
    },
    [
        'for_the_win' => function(\$__params = []) {
            extract(\$__params);
            return <x:frag><p /></x:frag>;
        },
    ]
);
EXPECTED;

        $this->assertEquals($expected, $template->compile());

    }

    /**
     * @expectedException App\Lib\xbt\SyntaxError
     */
    public function test_include_doctype_when_doctype_attribute_is_set_to_something_other_than_a_literal_true_or_false_string()
    {
        $node = Mockery::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = Mockery::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = Mockery::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = Mockery::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = Mockery::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $doctype = Mockery::mock(ExpressionNode::class, ['{"true"}'])->makePartial();

        $attributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':doctype' => $doctype}])->makePartial();

        $template = new Template($attributes, $children, $blocks);
        $expected =<<<EXPECTED
return new \App\Lib\\xbt\TemplateRuntime(
    null,
    function(\$__params = []) {
        extract(\$__params);
        return <x:doctype>foobar</x:doctype>;
    },
    [
        'for_the_win' => function(\$__params = []) {
            extract(\$__params);
            return <x:frag><p /></x:frag>;
        },
    ]
);
EXPECTED;

        $this->assertEquals($expected, $template->compile());
    }


    public function test_compile_outputs_template_runtime_instance()
    {

        $node = Mockery::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = Mockery::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = Mockery::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = Mockery::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = Mockery::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $attributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $template = new Template($attributes, $children, $blocks);

        $expected =<<<EXPECTED
return new \App\Lib\\xbt\TemplateRuntime(
    null,
    function(\$__params = []) {
        extract(\$__params);
        return <x:frag>foobar</x:frag>;
    },
    [
        'for_the_win' => function(\$__params = []) {
            extract(\$__params);
            return <x:frag><p /></x:frag>;
        },
    ]
);
EXPECTED;

        $this->assertEquals($expected, $template->compile());
    }

    public function test_compile_outputs_template_runtime_instance_with_parent() {

        $node = Mockery::mock(Node::class)->makePartial();
        $node->shouldReceive('render')->andReturn('');

        $children = Mockery::mock(NodeList::class, [Vector<Node> {$node}])->makePartial();
        $children->shouldReceive('render')->andReturn('foobar');

        $blockName = Mockery::mock(StringNode::class, ['"for_the_win"'])->makePartial();
        $blockName->shouldReceive('render')->andReturn('"for_the_win"');

        $blockAttributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {':name' => $blockName}])->makePartial();
        $blockAttributes->shouldReceive('render')->andReturn('name="for_the_win"');

        $p = new TagNode(':p', new TagAttributes, new NodeList);

        $blockChildren = Mockery::mock(NodeList::class, [Vector<Node> {$p}])->makePartial();
        $blockChildren->shouldReceive('render')->andReturn('<p />');

        $blockNode = Mockery::mock(BlockNode::class, [$blockAttributes, $blockChildren])->makePartial();
        $blocks = Map<string, BlockNode> {'for_the_win' => $blockNode};

        $attributes = Mockery::mock(TagAttributes::class, [Map<string, StringNode> {':extends' => new StringNode('"layouts.mobile"')}])->makePartial();

        $template = new Template($attributes, $children, $blocks);

        $expected =<<<EXPECTED
return new \App\Lib\\xbt\TemplateRuntime(
    app()['xbt.compiler']->compileExtends('layouts.mobile'),
    function(\$__params = []) {
        extract(\$__params);
        return <x:frag>foobar</x:frag>;
    },
    [
        'for_the_win' => function(\$__params = []) {
            extract(\$__params);
            return <x:frag><p /></x:frag>;
        },
    ]
);
EXPECTED;

        $this->assertEquals($expected, $template->compile());

    }

    /**
     * @expectedException App\Lib\xbt\SyntaxError
     */
    public function test_extends_attribute_must_be_a_string_node()
    {
        $attributes = new TagAttributes(Map<string, ExpressionNode> {':extends' => new DelimitedExpressionNode('{1}')});
        $children   = new NodeList(Vector<Node> {});
        $blocks     = Map<string, BlockNode> {};
        $template   = new Template($attributes, $children, $blocks);
    }
}

