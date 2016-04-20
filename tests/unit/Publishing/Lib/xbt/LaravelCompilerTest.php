<?hh

namespace App\Publishing\Lib\xbt;

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

        $this->assertEquals('/cache/path/' . md5($path) . '.php', $compiler->getCompiledPath($path));
    }

    public function test_getCompiledTemplateDefinitionPath_returns_the_path_to_the_compiled_class_definition()
    {
        $path = 'tipu-tipu.xbt.php';

        $compiledClassPath = '/class/path/'  . md5($path) . '.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = m::mock(LaravelCompiler::class, [$mockFS, '/cache/path', '/class/path', []])->makePartial();

        $this->assertEquals($compiledClassPath, $compiler->getCompiledTemplateDefinitionPath($path));
    }

    public function test_isExpired_returns_true_if_compiled_class_doesnt_exist()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $mockFS->shouldReceive('exists')->with($compiler->getCompiledTemplateDefinitionPath($path))->once()->andReturn(false);
        $mockFS->shouldReceive('exists')->with($compiler->getCompiledPath($path))->never()->andReturn(true);

        $this->assertTrue($compiler->isExpired($path));
    }

    public function test_isExpired_returns_true_if_cache_doesnt_exist()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = new LaravelCompiler($mockFS, '/cache/path', '/class/path', []);

        $mockFS->shouldReceive('exists')->with($compiler->getCompiledTemplateDefinitionPath($path))->once()->andReturn(true);
        $mockFS->shouldReceive('exists')->with($compiler->getCompiledPath($path))->once()->andReturn(false);

        $this->assertTrue($compiler->isExpired($path));
    }

    public function test_compile_calls_files_put_twice()
    {
        $path = 'tipu-tipu.xbt.php';

        $mockFS = m::mock(Filesystem::class)->makePartial();

        $compiler = m::mock(LaravelCompiler::class, [$mockFS, '/cache/path', '/class/path', []])->makePartial();
        $compiler->shouldReceive('compileDefinition')->once();
        $compiler->shouldReceive('compileInvocation')->once();

        $compiler->compile($path);
    }
}

