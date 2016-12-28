<?php // strict
namespace App\Publishing\Lib\Xbt;

interface NestableNode extends Node
{
    public function getChildren() : NodeList;
    public function renderChildren() /*: string*/;
}

