<?hh
namespace App\Publishing\Lib\xbt;

use Mockery;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{

    public function test_tokenize_returns_tokenstream()
    {
        $source = '<h1>This is an H1 tag</h1>';
        $tokenizer = new Tokenizer($source);
        $this->assertTrue($tokenizer->tokenize() instanceof TokenStream);
    }

    public function test_variableize()
    {
        $tokenizer = new Tokenizer('');
        $actual = '$foo';
        $expected = '$__params[\'foo\']';
        $this->assertEquals($expected, $tokenizer->variableize($actual));
    }

    public function test_variableize_called_when_tokenize_contain_variable()
    {
        $source = '$x=$y[2]';
        $expected = '$__params[\'x\']=$__params[\'y\'][2]';

        $tokenizer = Mockery::mock(Tokenizer::class, [$source])->makePartial();
        $tokenizer->shouldReceive('variableize')->with('$x')->once()->passthru();
        $tokenizer->shouldReceive('variableize')->with('$y')->once()->passthru();

        $stream = $tokenizer->tokenize();
        $actual = implode('', array_map(($token) ==> $token->value, $stream->getTokens()));
        $this->assertEquals($expected, $actual);
    }

}
