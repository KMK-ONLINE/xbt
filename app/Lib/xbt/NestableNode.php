<?hh // strict

namespace App\Lib\xbt;

interface NestableNode extends Node
{
    public function getChildren() : NodeList;
    public function renderChildren() : string;
}

