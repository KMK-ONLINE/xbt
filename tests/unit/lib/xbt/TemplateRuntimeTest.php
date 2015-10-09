<?hh

namespace Lib\xbt;

use Mockery as m;

class TemplateRuntimeTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {
        parent::setUp();
        $this->parent = new TemplateRuntime(
            null,
            function() {
                return "parent render";
            },
            [
                'foobar' => function() {
                    return "parent foobar";
                },
                'zulu' => function() {
                    return "parent zulu";
                }
            ]
        );

        $this->child = new TemplateRuntime(
            $this->parent,
            function() {
                return "child render";
            },
            [
                'foobar' => function() {
                    return "child foobar";
                },
                'bujug' => function() {
                    return "child bujug";
                },
            ]
        );
    }

    public function test_resolveBlock() {
        $foobarBlockClosure = $this->child->resolveBlock('foobar');
        $this->assertEquals('child foobar', $foobarBlockClosure());
        $barfooBlockClosure = $this->child->resolveBlock('barfoo');
        $this->assertEquals(null, $barfooBlockClosure);
    }
    
    public function test_resolveParentBlock() {
        $foobarBlockClosure = $this->child->resolveParentBlock('foobar');
        $this->assertEquals('parent foobar', $foobarBlockClosure());
        $zuluBlockClosure = $this->child->resolveParentBlock('zulu');
        $this->assertEquals('parent zulu', $zuluBlockClosure());
        $bujugBlockClosure = $this->child->resolveParentBlock('bujug');
        $this->assertEquals(null, $bujugBlockClosure);
    }

    public function test_resolveRender() {
        $renderClosure = $this->child->resolveRender();
        $this->assertEquals('parent render', $renderClosure());
    }
}
