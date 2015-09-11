<?hh

namespace Lib\xbt;

use Illuminate\View\Compilers\CompilerInterface;

use Illuminate\Filesystem\Filesystem;

use Illuminate\View\FileViewFinder;

use Config;

class LaravelCompiler implements CompilerInterface
{
    protected $files;
    protected $cachePath;
    protected $classPath;
    protected $finder;

    public function __construct(Filesystem $files, $cachePath, $classPath, $paths)
    {
        $this->files = $files;
        $this->cachePath = $cachePath;
        $this->classPath = $classPath;
        $this->finder = new FileViewFinder($this->files, $paths, ['xbt.php']);
    }

    public function getCompiledPath($path)
    {
        return $this->cachePath . '/' . md5($path);
    }

    public function getCompiledClassPath($path)
    {
        return $this->classPath . '/' . $this->getClassName($path) . '.php';
    }

    public function getClassName($path)
    {
        return '__xbt_' . md5($path);
    }

    public function isExpired($path)
    {
        $compiledClass = $this->getCompiledClassPath($path);

        $compiled = $this->getCompiledPath($path);

        if (!$this->files->exists($compiledClass) || !$this->files->exists($compiled)) {
            return true;
        }

        $lastModified = $this->files->lastModified($path);

        return $lastModified >= $this->files->lastModified($compiled) || $lastModified >= $this->files->lastModified($compiledClass);
    }


    protected function makeTemplate($path)
    {
        $contents    = $this->files->get($path);
        $tokenizer   = new Tokenizer($contents);
        $tokenStream = $tokenizer->tokenize();
        $parser      = new Parser($tokenStream);

        return $parser->parse();
    }

    protected function compileRequireOnce($path)
    {
        return "require_once '" . $this->getCompiledClassPath($path) . "';" . PHP_EOL;
    }

    public function compileInvocation($class)
    {
        return "echo (new $class(get_defined_vars()))->render();";
    }

    protected function compileExtends($extends) : Pair<?string, string>
    {
        if (strlen($extends) > 0) {
            $dependency = $this->finder->find($extends);
            $parent = $this->compile($dependency);
            $require = $this->compileRequireOnce($dependency);
        } else {
            $parent = null;
            $require = '';
        }

        return Pair {$parent, $require};
    }

    public function compile($path)
    {
        $template = $this->makeTemplate($path);

        $class = $this->getClassName($path);

        $prefix = "<?hh" . PHP_EOL . "/* source: $path */" . PHP_EOL;

        /* compile definition */
        $extends = (string) $template->getAttributes()[':extends'];
        list($parent, $require) = $this->compileExtends($extends);
        $targetPath = $this->getCompiledClassPath($path);
        $this->files->put($targetPath, $prefix . $require . $template->compile($class, $parent));

        /* compile invocation */
        $targetPath = $this->getCompiledPath($path);
        $require = $this->compileRequireOnce($path);
        $this->files->put($targetPath, $prefix . $require . $this->compileInvocation($class));

        return $class;
    }

}

