<?hh // strict


namespace App\Lib\xbt;

abstract class ExpressionNode implements Node
{
    abstract public function render() : string;
}
