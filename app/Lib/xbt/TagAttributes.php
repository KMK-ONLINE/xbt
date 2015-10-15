<?hh
namespace App\Lib\xbt;

class TagAttributes<Tk, Tv> implements Node, \ArrayAccess<string, ExpressionNode>
{
    protected $attributes;

    public function __construct(Map<string, ExpressionNode> $attributes = Map {})
    {
        $this->attributes = $attributes;
    }

    public function render() : string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            if (strpos($key, ':') === 0) {
                $attributes[] = substr($key, 1) . '=' . $value->render();
            } else {
                $attributes[] = $key . '=' . $value->render();
            }
        }
        return implode(' ', $attributes);
    }

    public function getAttributes() : Map<string, ExpressionNode>
    {
        return $this->attributes;
    }

    public function offsetExists($name) : bool
    {
        $attributes = $this->getAttributes();

        return $attributes->containsKey($name);
    }

    public function offsetGet($name) : ?ExpressionNode
    {
        $attributes = $this->getAttributes();

        return $attributes->get($name);
    }

    public function offsetSet($name, $value) : void
    {
        $attributes = $this->getAttributes();

        $attributes->set($name, $value);
    }

    public function offsetUnset($name) : void
    {
        $attributes = $this->getAttributes();

        $attributes->remove($name);
    }
}

