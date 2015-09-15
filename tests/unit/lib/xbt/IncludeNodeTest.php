<?hh

namespace Lib\xbt;

use Mockery as m;

class IncludeNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException Lib\xbt\SyntaxError
     */
    public function test_include_node_must_have_template_attribute()
    {
        $template = new StringNode('"foobar"');

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {':name' => $template});

        $includeNode = new IncludeNode($tagAttributes);
    }

    /**
     * @expectedException Lib\xbt\SyntaxError
     */
    public function test_template_attribute_must_be_StringNode()
    {
        $template = new DelimitedExpressionNode('{"foobar"}');

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {':template' => $template});

        $includeNode = new IncludeNode($tagAttributes);
    }

    public function test_render_prints_out_call_to_env_make()
    {
        $template = new StringNode('"foobar"');

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {':template' => $template});

        $includeNode = new IncludeNode($tagAttributes);

        $expected = '<raw-string>{$__env->make(\'foobar\', [])->render()}</raw-string>';

        $this->assertEquals($expected, $includeNode->render());
    }


    public function test_include_node_params_attribute_renders_into_env_make_with_params()
    {
        $template = new StringNode('"foobar"');

        $params = new DelimitedExpressionNode("{['foo' => 'bar']}");

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {
            ':template' => $template,
            ':params'   => $params,
        });

        $includeNode = new IncludeNode($tagAttributes);

        $expected = '<raw-string>{$__env->make(\'foobar\', [\'foo\' => \'bar\'])->render()}</raw-string>';

        $this->assertEquals($expected, $includeNode->render());
    }

    /**
     * @expectedException Lib\xbt\SyntaxError
     */
    public function test_params_attribute_is_not_a_delimited_expression()
    {
        $template = new StringNode('"foobar"');

        $params = new StringNode('"quoted string tee hee"');

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {
            ':template' => $template,
            ':params'   => $params,
        });

        $includeNode = new IncludeNode($tagAttributes);
    }

    public function test_params_attribute_can_span_multiple_lines()
    {
        $template = new StringNode('"foobar"');

        $params = new DelimitedExpressionNode("{['foo' => 'bar',\n'baz' => 'zulu']}");

        $tagAttributes = new TagAttributes(Map<string, ExpressionNode> {
            ':template' => $template,
            ':params'   => $params,
        });

        $includeNode = new IncludeNode($tagAttributes);

        $expected = "<raw-string>{\$__env->make('foobar', ['foo' => 'bar',\n'baz' => 'zulu'])->render()}</raw-string>";

        $this->assertEquals($expected, $includeNode->render());

    }
}

