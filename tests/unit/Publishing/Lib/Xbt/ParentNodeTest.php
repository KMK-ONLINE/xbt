<?hh
namespace App\Publishing\Lib\Xbt;

use Mockery;

class ParentNodeTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_getBlockName_returns_the_parent_block_name()
    {
        $attributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $parentNode = new ParentNode('foobar', $attributes);

        $this->assertEquals('foobar', $parentNode->getBlockName());
    }

    public function test_ParentNode_renders_into_current_blocks_parent_method_call()
    {
        $attributes = Mockery::mock(TagAttributes::class, [Map<string, ExpressionNode> {}])->makePartial();

        $parentNode = new ParentNode('foobar', $attributes);

        $expected = '{($_ = $__this->resolveParentBlock(\'foobar\')) ? call_user_func($_, $__this, $__params) : null}';

        $actual = $parentNode->render();

        $this->assertEquals($expected, $actual);
    }
}

