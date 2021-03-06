<?php // strict
namespace Xbt;

class Template extends TagNode
{
    protected $extends;
    protected $blocks;
    protected $doctype;

    public function __construct(TagAttributes $attributes, NodeList $children, $blocks = [])
    {
        if ($attributes->offsetExists(':extends') && !$attributes->offsetGet(':extends') instanceof StringNode) {
            throw new SyntaxError("Extends attribute must be a StringNode");
        }

        $this->extends = $attributes->offsetGet(':extends');

        if ($attributes->offsetExists(':doctype')) {
            if (!$attributes->offsetGet(':doctype') instanceof StringNode) {
                throw new SyntaxError("Doctype attribute must be a StringNode");
            }

            $doctype = $attributes->offsetGet(':doctype')->__toString();

            if (!in_array($doctype, ['true', 'false'])) {
                throw new SyntaxError("Doctype attribute must be true or false");
            }

            $this->doctype = $doctype === 'true';
        }

        parent::__construct('xbt:template', $attributes, $children);
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function compile() //: string
    {
        $wrapper = 'x:frag';
        if ($this->doctype) {
            $wrapper = 'x:doctype';
        }
        $parent = $this->extends ? "app('xbt.compiler')->compileExtends('{$this->extends}')" : 'null';
        return <<<RENDER
return new \\Xbt\TemplateRuntime(
    {$parent},
    function(\$__this, \$__params = []) {
        return <{$wrapper}>{$this->renderChildren()}</{$wrapper}>;
    },
    [
{$this->compileBlocks()}
    ]
);
RENDER;
    }

    public function compileBlocks() //: string
    {
        $blocks = [];
        foreach ($this->getBlocks() as $block) {
            $blocks[] = $block->renderBody();
        }
        return implode("\n", $blocks);
    }
}

