<?php // strict
namespace App\Publishing\Lib\Xbt;

class TextNode implements Node
{
    protected $text;

    public function __construct(/*string */$text)
    {
        $this->text = $text;
    }

    public function render() //: string
    {
        return $this->text;
    }

    public function isWhitespace() //: bool
    {
        return strlen(trim($this->text)) === 0;
    }
}

