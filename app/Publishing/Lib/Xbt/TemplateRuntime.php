<?hh
namespace App\Publishing\Lib\Xbt;
class TemplateRuntime
{
    protected $parent;
    protected $render;
    protected $blocks;

    public function __construct(?TemplateRuntime $parent, callable $render, $blocks = [])
    {
        $this->parent = $parent;

        $this->render = isset($this->parent) ? $this->parent->render : $render;

        $this->render = $this->render->bindTo($this, __CLASS__);

        $this->blocks = $blocks + (isset($this->parent) ? $this->parent->blocks : []);

        foreach ($this->blocks as $i => $block) {
            $this->blocks[$i] = $block->bindTo($this, __CLASS__);
        }
    }

    public function resolveBlock($name)
    {
        return isset($this->blocks[$name]) ? $this->blocks[$name] : null;
    }

    public function resolveParentBlock($name)
    {
        return isset($this->parent) ? $this->parent->resolveBlock($name) : null;
    }

    public function resolveRender()
    {
        return $this->render;
    }

    public function render($params = [])
    {
        return call_user_func($this->resolveRender(), $params);
    }
}

