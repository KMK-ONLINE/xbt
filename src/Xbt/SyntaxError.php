<?php
namespace Xbt;

class SyntaxError extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

