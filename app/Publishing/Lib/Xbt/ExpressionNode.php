<?php // strict
namespace App\Publishing\Lib\Xbt;

abstract class ExpressionNode implements Node
{
    abstract public function render() /*: string*/;
}

