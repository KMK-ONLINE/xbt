<?hh
namespace App\Publishing\Lib\Xbt;

use Mockery;

class NodeListTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function test_getNodes_should_return_an_vector_of_nodes_in_the_list()
    {
        $nodes = Vector<Node> {
            Mockery::mock(Node::class)->makePartial(),
            Mockery::mock(Node::class)->makePartial(),
        };

        $nodeList = new NodeList($nodes);
        $nodes = $nodeList->getNodes();

        $this->assertEquals(2, count($nodes));
        $this->assertEquals($nodes[0], $nodes[0]);
        $this->assertEquals($nodes[1], $nodes[1]);
    }

    public function test_render_should_render_all_nodes_in_the_list()
    {
        $node1 = Mockery::mock(Node::class)->makePartial();
        $node1->shouldReceive('render')->once();

        $node2 = Mockery::mock(Node::class)->makePartial();
        $node2->shouldReceive('render')->once();

        $nodes = Vector<Node> {
            $node1, $node2,
        };

        $nodeList = new NodeList($nodes);

        $nodeList->render();
    }
}

