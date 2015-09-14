<?hh

namespace Lib\xbt;

use App;
use Mockery as m;
use Illuminate\Filesystem\Filesystem;

class LaravelCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_getCompiledPath_returns_the_path_to_the_compiled_class_invocation()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $this->assertEquals('/cache/path/' . md5($path), $compiler->getCompiledPath($path));
    }

    public function test_getCompiledClassPath_returns_the_path_to_the_compiled_class_definition()
    {
        $path = 'tipu-tipu.xbt.php';

        $compiledClassPath = '/class/path/' . '__xbt_' . md5($path) . '.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = m::mock(LaravelCompiler::class, [$mockFS, '/cache/path', '/class/path', []])->makePartial();
        $compiler->shouldReceive('getClassName')->with($path)->once()->andReturn('__xbt_' . md5($path));

        $this->assertEquals($compiledClassPath, $compiler->getCompiledClassPath($path));
    }

    public function test_getClassName_returns_the_compiled_class_name_md5ed_and_prefixed_with_xbt()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $this->assertEquals('__xbt_' . md5($path), $compiler->getClassName($path));
    }

    public function test_isExpired_returns_true_if_compiled_class_doesnt_exist()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $mockFS->shouldReceive('exists')->with($compiler->getCompiledClassPath($path))->once()->andReturn(false);
        $mockFS->shouldReceive('exists')->with($compiler->getCompiledPath($path))->never()->andReturn(true);

        $this->assertTrue($compiler->isExpired($path));
    }

    public function test_isExpired_returns_true_if_cache_doesnt_exist()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $mockFS->shouldReceive('exists')->with($compiler->getCompiledClassPath($path))->once()->andReturn(true);
        $mockFS->shouldReceive('exists')->with($compiler->getCompiledPath($path))->once()->andReturn(false);

        $this->assertTrue($compiler->isExpired($path));
    }

    public function test_compileInvocation_instantiates_the_xhp_component_with_local_vars_and_renders_it()
    {
        $mockFS = m::mock(Filesystem::class)->makePartial();
        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);
        $className = 'ClassName';
        $expected = "echo (new ClassName(get_defined_vars()))->render();";
        $this->assertEquals($expected, $compiler->compileInvocation($className));
    }

    public function test_compile_calls_files_put_twice()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();
        $mockFS->shouldReceive('get')->once()->andReturn('<xbt:template>empty</xbt:template>');
        $mockFS->shouldReceive('put')->twice();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $compiler->compile($path);
     }
}

