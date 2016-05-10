<?hh

namespace App\Publishing\Lib\Xbt;

class CommentNodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException App\Publishing\Lib\Xbt\SyntaxError
     */
    public function test_commentNode_constructor_throws_SyntaxError_for_malformed_comment_strings()
    {
        $comment = new CommentNode('<-- this is just a comment');
    }

    public function test_render_renders_the_comment_node()
    {
        $comment = new CommentNode("<!-- this is just a }com\"ment{, O'Brien! -->");

        $expected =<<<'EXPECTED'
<raw-string>{'<!-- this is just a }com"ment{, O\'Brien! -->'}</raw-string>
EXPECTED;
        $this->assertEquals($expected, $comment->render());
    }

    public function test_render_renders_the_multiline_comment_node()
    {
        $comment = new CommentNode("<!--> ISI KOMEN < ! -->");

        $expected =<<<'EXPECTED'
<raw-string>{'<!--> ISI KOMEN < ! -->'}</raw-string>
EXPECTED;
        $this->assertEquals($expected, $comment->render());
    }
}

