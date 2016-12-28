<?php // strict
namespace Xbt;

abstract class ExpressionNode implements Node
{
    abstract public function render() /*: string*/;
}

