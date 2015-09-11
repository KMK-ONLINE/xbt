<?hh // strict

namespace Lib\xbt;

class Template extends TagNode
{
    protected Map<string, BlockNode> $blocks;
    protected bool $doctype;

    public function __construct(TagAttributes $attributes, NodeList $children, Map<string, BlockNode> $blocks)
    {
        if ($attributes->offsetExists(':extends') && !$attributes->offsetGet(':extends') instanceof StringNode) {
            throw new SyntaxError("Extends attribute must be a StringNode");
        }

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

    public function getBlocks() : Map<string, BlockNode>
    {
        return $this->blocks;
    }

    public function compile(string $class, ?string $extends) : string
    {
        $wrapper = 'x:frag';
        $classDeclaration = "class $class";

        if (!is_null($extends)) {
            $classDeclaration .= " extends $extends";
            $renderDefinition =<<<DEFINITION
    // defer render method to parent class

DEFINITION;

        } else {

            if ($this->doctype) {
                $wrapper = 'x:doctype';
            }

            $renderDefinition =<<<RENDER
    public function __construct(\$params = [])
    {
        \$this->params = \$params;
    }

    public function render()
    {
        extract(\$this->params);
        return <{$wrapper}>{$this->renderChildren()}</{$wrapper}>;
    }

RENDER;
        }

        $classDefinition =<<<TEMPLATE
{$classDeclaration}
{
{$renderDefinition}
{$this->compileBlocks()}
}
TEMPLATE;
        return $classDefinition;
    }

    public function compileBlocks() : string
    {
        $blocks = [];
        foreach ($this->getBlocks() as $block) {
            $blocks[] = $block->renderBody();
        }
        return implode("\n\n", $blocks);
    }
}

