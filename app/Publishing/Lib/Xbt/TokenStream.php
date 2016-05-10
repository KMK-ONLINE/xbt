<?hh // strict

namespace App\Publishing\Lib\Xbt;

class TokenStream
{
    protected array $tokens;

    protected Token $current;

    protected int $position;

    public int $row;
    public string $text = "";

    public function __construct(array<Token> $tokens)
    {
        $this->tokens = $tokens;
        if (count($this->tokens) === 0 ||
            $this->tokens[count($this->tokens)-1]->type !== Token::T_XHP_EOF
        ) {
            $this->tokens[] = new Token(Token::T_XHP_EOF);
        }
        $this->position = 0;
        $this->current = $this->tokens[$this->position];
        $this->row = $this->current->lineno;
        $this->text .= $this->current->value;
    }

    public function getTokens() : array
    {
        return $this->tokens;
    }

    public function getCurrent() : Token
    {
        return $this->current;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function isEOF() : bool
    {
        return $this->getCurrent()->type === Token::T_XHP_EOF;
    }

    /**
     * Return the current element and go to the next position
     * */
    public function next() : Token
    {
        $tokens = $this->getTokens();
        $current = $this->getCurrent();
        $position = $this->getPosition() + 1;

        if (isset($tokens[$position])) {
            $this->current = $tokens[$position];
            $this->position = $position;
            $this->text = substr($this->text . $this->current->value, 100);
            if ($this->match(T_WHITESPACE)) {
              $this->row = $current->lineno;
            }
        }

        return $current;
    }

    /**
     * Return a position ahead of the current postiion
     * If it's out of range, return the the latest position
     */
    public function peek($i = 1) : Token
    {
        if (!is_int($i) || $i < 1) {
            throw new \InvalidArgumentException('supplied argument must be an integer larger than 0');
        }

        $tokens = $this->getTokens();
        $next = $this->getPosition() + $i;

        if (isset($tokens[$next])) {
            return $tokens[$next];
        } else {
            return $tokens[count($tokens)-1];
        }
    }

    /**
     * Go forward until until the next element that is not of $type
     */
    public function skip(int $type): void
    {
        while (!$this->match(Token::T_XHP_EOF)) {
            if (!$this->consume($type)) {
                break;
            }
        }
    }

    /**
     * Check if the current element match a certain type
     */
    public function match(int $type, ?string $value = null): bool
    {
        return $this->getCurrent()->match($type, $value);
    }

    /**
     * Try to match the current element with a certain type:
     * Go forward and return next element if succeed, throws an exception otherwise
     */
    public function expect(int $type, ?string $value = null) : ?Token
    {
        $current = $this->getCurrent();

        if (!$this->match($type, $value)) {
            throw new SyntaxError('Unexpected (' . $current->type . ') "' . $current->value . '", expecting (' . $type . ') "' . $value . '"');
        }
        return $this->next();
    }

    /**
     * Try to match the current element with a certain type:
     * Go forward and return true if success, return false otherwise
     */
    public function consume(int $type, ?string $value = null): bool
    {
        if ($this->match($type, $value)) {
            $this->next();
            return true;
        } else {
            return false;
        }
    }

}
