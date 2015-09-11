<?hh // strict

namespace Lib\xbt;

class BlockNode extends TagNode
{
    public function __construct(TagAttributes $attributes, NodeList $children)
    {
        if (!$attributes->offsetExists(':name')) {
            throw new SyntaxError("Block must have a name attribute");
        }

        $name = $attributes->getAttributes()[':name'];

        if (!$name instanceof StringNode) {
            throw new SyntaxError('Name attribute of block must be a quoted string literal');
        }

        $blockName = (string) $name;

        if (!preg_match('/^[a-z0-9_]+$/i', $blockName)) {
            throw new SyntaxError("Invalid block name \"$blockName\". Block names must contain only alphanumeric and underscores");
        }

        parent::__construct(':xbt:block', $attributes, $children);
    }

    public function getNameAttribute() : string
    {
        return (string) $this->getAttributes()->offsetGet(':name');
    }

    public function getChildren() : NodeList
    {
        return $this->children;
    }

    public function render() : string
    {
        return '{$this->block_' . $this->getNameAttribute() . '()}';
    }

    public function renderBody() : string
    {
        $block =<<<BLOCK
    public function block_{$this->getNameAttribute()}()
    {
        extract(\$this->params);
        return <x:frag>{$this->renderChildren()}</x:frag>;
    }
BLOCK;
        return $block;
    }
}

