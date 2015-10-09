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

    public function getCompiledTemplateDefinitionPath($path)
    {
        return $this->classPath . '/' . md5($path) . '.php';
    }

    public function isExpired($path)
    {
        $compiledClass = $this->getCompiledTemplateDefinitionPath($path);

        $compiled = $this->getCompiledPath($path);

        if (!$this->files->exists($compiledClass) || !$this->files->exists($compiled)) {
            return true;
        }

        $lastModified = $this->files->lastModified($path);

        return $lastModified >= $this->files->lastModified($compiled) || $lastModified >= $this->files->lastModified($compiledClass);
    }

    public function compileExtends($view)
    {
        $path = $this->finder->find($view);
        if ($this->isExpired($path)) {
            $this->compile($path);
        }
        return include $this->getcompiledTemplateDefinitionPath($path);
    }

    protected function makeTemplate($path)
    {
        $contents    = $this->files->get($path);
        $tokenizer   = new Tokenizer($contents);
        $tokenStream = $tokenizer->tokenize();
        $parser      = new Parser($tokenStream);

        return $parser->parse();
    }

    public function compile($path)
    {
        $this->compileDefinition($path);

        $this->compileInvocation($path);
    }

    public function compileDefinition($path) {

        $template = $this->makeTemplate($path);

        $prefix = "<?hh" . PHP_EOL . "/* source: $path */" . PHP_EOL;

        $definitionTargetPath = $this->getCompiledTemplateDefinitionPath($path);

        $definition = $template->compile();

        $this->files->put($definitionTargetPath, $prefix . $definition);
    }

    public function compileInvocation($path) {

        $prefix = "<?hh" . PHP_EOL . "/* source: $path */" . PHP_EOL;

        $definitionTargetPath = $this->getCompiledTemplateDefinitionPath($path);

        $invocationTargetPath = $this->getCompiledPath($path);

        $invocation =<<<INVOCATION
\$__inv = (include '$definitionTargetPath');
echo \$__inv->render(get_defined_vars());
INVOCATION;
        $this->files->put($invocationTargetPath, $prefix . $invocation);
    }

}

