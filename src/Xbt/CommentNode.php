<?php // strict
namespace App\Publishing\Lib\Xbt;

class CommentNode implements Node
{
    protected $comment;

    public function __construct(/*string */$comment)
    {
        if (!preg_match('/<!--(.+?)-->/', $comment)) {
            throw new SyntaxError('Invalid XML comment: ' . $comment);
        }

        $this->comment = $comment;
    }

    public function render() //: string
    {
        return '<raw-string>{' . var_export($this->comment, true) . '}</raw-string>';
    }
}

