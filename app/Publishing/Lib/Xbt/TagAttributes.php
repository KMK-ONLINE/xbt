<?php
namespace App\Publishing\Lib\Xbt;

class TagAttributes implements Node, \ArrayAccess
{
    protected $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function render() //: string
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

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function offsetExists($name)
    {
        $attributes = $this->getAttributes();

        return isset($attributes[$name]);
    }

    public function offsetGet($name)
    {
        $attributes = $this->getAttributes();

        return $this->offsetExists($name) ? $attributes[$name] : null;
    }

    public function offsetSet($name, $value)
    {
        $attributes = $this->getAttributes();

        $attributes[$name] = $value;
    }

    public function offsetUnset($name)
    {
        $attributes = $this->getAttributes();

        unset($attributes[$name]);
    }
}

