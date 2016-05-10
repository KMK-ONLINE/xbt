<?hh // strict

namespace App\Publishing\Lib\Xbt;
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
                throw new SyntaxError('Params attribute for include tag must be a delimited expression');
            }

        }

        if ($attributes->offsetExists(':when')) {

            $when = $attributes->offsetGet(':when');

            if (!$when instanceof DelimitedExpressionNode) {
                throw new SyntaxError('When attribute for include tag must be a delimited expression');
            }
        }


        parent::__construct('xbt:include', $attributes, new NodeList(Vector<Node> {}));
    }

    public function render() : string
    {
        $attributes = $this->getAttributes();

        $template = $attributes->offsetGet(':template');

        $when = $attributes->offsetExists(':when') ? $attributes->offsetGet(':when') : 'true';

        $params = $attributes->offsetExists(':params') ? $attributes->offsetGet(':params') : '[]' ;

        return '<raw-string>{(' . $when . ') ? $__params[\'__env\']->make(\'' . $template . '\', ' . $params . ')->render() : \'\'}</raw-string>';
    }
}
