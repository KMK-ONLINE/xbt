<?hh // strict

namespace App\Publishing\Lib\Xbt;

class NodeList implements Node
{
    protected $nodes;

    public function __construct(Vector<Node> $nodes = Vector{})
    {
        $this->nodes = $nodes;
    }

    public function render() : string
    {
        $out = '';

        foreach ($this->getNodes() as $node) {
            $out .= $node->render();
        }

        return $out;
    }

    public function getNodes() : Vector<Node>
    {
        return $this->nodes;
    }
}

