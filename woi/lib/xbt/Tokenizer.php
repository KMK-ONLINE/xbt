<?hh // strict

namespace Lib\xbt;

class Tokenizer
{
    protected string $source;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function tokenize() : TokenStream
    {
        $tokens = array_map(
            function($token) {
                if (is_string($token)) {
                    switch ($token) {
                    case '/':
                        $type = Token::T_XHP_TAG_SLASH;
                        break;
                    case '{':
                        $type = Token::T_XHP_BRACE_OPEN;
                        break;
                    case '}':
                        $type = Token::T_XHP_BRACE_CLOSE;
                        break;
                    case '=':
                        $type = Token::T_XHP_ATTRIBUTE_EQUAL;
                        break;
                    default:
                        $type = Token::T_XHP_TOKEN;
                        break;
                    }
                    return new Token($type, $token);
                } elseif (is_array($token)) {
                    return new Token($token[0], $token[1], $token[2]);
                } else {
                    throw new \InvalidArgumentException('provided token is a ' . gettype($token) . ', string or array from token_get_all needed');
                }

            },
            token_get_all("<?hh\n" . $this->source)
        );
        array_shift($tokens);
        $tokens[] = new Token(Token::T_XHP_EOF);
        return new TokenStream($tokens);
    }
}
