<?hh // strict

namespace App\Lib\xbt;

class DelimitedExpressionNode extends ExpressionNode
{
    protected string $expression;

    public function __construct(string $expression)
    {
        $first = $expression[0];
        $last = $expression[strlen($expression)-1];

        if (!($first === '{' && $last === '}')) {
            throw new \InvalidArgumentException("$expression is not a valid " . static::class);
        }

        $this->expression = $expression;
    }

    public function render() : string
    {
        return $this->expression;
    }

    public function __toString() : string
    {
        return substr($this->expression, 1, -1);
    }
}

