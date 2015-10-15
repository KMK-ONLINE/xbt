<?hh // strict


namespace App\Publishing\Lib\xbt;

abstract class ExpressionNode implements Node
{
    abstract public function render() : string;
}

