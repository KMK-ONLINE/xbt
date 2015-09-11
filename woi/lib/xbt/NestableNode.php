<?hh // strict

namespace Lib\xbt;

interface NestableNode extends Node
{
    public function getChildren() : NodeList;
    public function renderChildren() : string;
}

