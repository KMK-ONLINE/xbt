<?php // strict
namespace App\Publishing\Lib\Xbt;

class Token
{
    public $type;
    public $value;
    public $lineno;

    const T_XHP_TOKEN = 1000;
    const T_XHP_TAG_SLASH = 1001;
    const T_XHP_BRACE_OPEN = 1002;
    const T_XHP_BRACE_CLOSE = 1003;
    const T_XHP_ATTRIBUTE_EQUAL =  1004;
    const T_XHP_EOF = 1005;

    public function __construct(/*int */$type, /*string */$value = null, /*int */$lineno = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->lineno = $lineno;
    }

    public function match(/*int */$type, $value = null) //: bool
    {
        if ($value !== null) {
            if (is_array($value)) {
                return $this->type === $type && in_array($this->value, $value);
            } else {
                return $this->type === $type && $this->value === $value;
            }
        } else {
            return $this->type === $type;
        }

    }

    public function toString() //: string
    {
        if ($this->type === T_XHP_LABEL) {
            return substr($this->value, 1);
        } else {
            return $this->value;
        }
    }
}

