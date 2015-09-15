<?hh // strict

namespace Lib\xbt;
use HH\Vector;

class IncludeNode extends TagNode {

    public function __construct(TagAttributes $attributes)
    {
        if (!$attributes->offsetExists(':template')) {
            throw new SyntaxError('Include tag must have template attribute');
        }

        if (!$attributes->offsetGet(':template') instanceof StringNode) {
            throw new SyntaxError('Template attribute for include tag must be a quote delimited string');
        }

        if ($attributes->offsetExists(':params')) {

            $params = $attributes->offsetGet(':params');

            if (!$params instanceof DelimitedExpressionNode) {
                throw new SyntaxError('Params attribute for include tag must be a delimited array expression');
            }

        }

        parent::__construct('xbt:include', $attributes, new NodeList(Vector<Node> {}));
    }

    public function render() : string
    {
        $attributes = $this->getAttributes();

        $template = $attributes->offsetGet(':template');

        $params = $attributes->offsetExists(':params') ? $attributes->offsetGet(':params') : '[]' ;

        return '<raw-string>{$__env->make(\'' . $template . '\', ' . $params . ')->render()}</raw-string>';
    }
}
