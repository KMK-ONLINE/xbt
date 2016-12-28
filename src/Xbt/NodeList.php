<?php // strict
namespace Xbt;

class NodeList implements Node
{
    protected $nodes;

    public function __construct($nodes = [])
    {
        $this->nodes = $nodes;
    }

    public function render() //: string
    {
        $out = '';

        foreach ($this->getNodes() as $node) {
            $out .= $node->render();
        }

        return $out;
    }

    public function getNodes()
    {
        return $this->nodes;
    }
}

