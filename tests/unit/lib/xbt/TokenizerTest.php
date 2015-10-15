<?hh
namespace App\Lib\xbt;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->source = '<h1>This is an H1 tag</h1>';
    }

    public function test_tokenize_returns_tokenstream()
    {
        $tokenizer = new Tokenizer($this->source);
        $this->assertTrue($tokenizer->tokenize() instanceof TokenStream);
    }
}

