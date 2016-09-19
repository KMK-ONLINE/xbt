<?php // strict
namespace App\Publishing\Lib\Xbt;

class TagNode implements NestableNode
{
    protected $name;
    protected $attributes;
    protected $children;

    public function __construct(/*string */$name, TagAttributes $attributes, NodeList $children)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public function render() //: string
    {
        $name = strpos($this->name, ':') === 0 ? substr($this->name, 1) : $this->name;

        $attributes = $this->attributes->render();

        if ($attributes) {
            $attributes = ' ' . $attributes;
        }

        $children = $this->renderChildren();

        if ($children) {
            return '<' . $name . $attributes . '>' . $children . '</' . $name . '>';
        } else {
            return '<' . $name . $attributes . ' />';
        }
    }

    public function getAttributes() : TagAttributes
    {
        return $this->attributes;
    }

    public function getChildren() : NodeList
    {
        return $this->children;
    }

    public function renderChildren() //: string
    {
        return $this->getChildren()->render();
    }
}

