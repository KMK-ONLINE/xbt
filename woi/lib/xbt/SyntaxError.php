<?hh

namespace Lib\xbt;

class SyntaxError extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}

