<?php // strict
namespace Xbt;

interface NestableNode extends Node
{
    public function getChildren() : NodeList;
    public function renderChildren() /*: string*/;
}

