<?hh

namespace Lib\xbt;

use Mockery as m;

class ParentNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_getBlockName_returns_the_parent_block_name()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $parentNode = new ParentNode('foobar', $attributes);

        $this->assertEquals('foobar', $parentNode->getBlockName());
    }

    public function test_ParentNode_renders_into_current_blocks_parent_method_call()
    {
        $attributes = m::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $parentNode = new ParentNode('foobar', $attributes);

        $expected = '{is_callable(\'parent::block_foobar\') ? parent::block_foobar() : null}';

        $actual = $parentNode->render();

        $this->assertEquals($expected, $actual);
    }
}

