<?php // strict
namespace Xbt;

class StringNode extends ExpressionNode
{
    protected $string;

    public function __construct(/*string */$string)
    {
        $first = $string[0];
        $last = $string[strlen($string)-1];

        if (!($first === '"' && $last === '"')) {
            throw new \InvalidArgumentException("$string is not a valid " . static::class);
        }

        $this->string = $string;
    }

    public function render() //: string
    {
        return $this->string;
    }

    public function __toString() //: string
    {
        return substr($this->string, 1, -1);
    }
}

