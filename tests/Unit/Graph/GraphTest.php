<?php
declare(strict_types=1);

/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Test\Unit\Graph;

use IronEdge\Component\Graphs\Exception\ChildDoesNotExistException;
use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Graph\Graph;
use IronEdge\Component\Graphs\Node\Node;
use IronEdge\Component\Graphs\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class GraphTest extends AbstractTestCase
{
    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ValidationException
     * @dataProvider invalidIdDataProvider
     */
    public function test_initialize_ifIdIsInvalidThenThrowException(array $data)
    {
        $this->createGraphInstance($data);
    }

    /**
     * @dataProvider invalidNodesDataProvider
     */
    public function test_initialize_ifNodesAreInvalidThenThrowException(
        array $data,
        $expectedException = null,
        $expectedExceptionRegex = ''
    ) {
        $expectedException = $expectedException === null ?
            get_class(new ValidationException()) :
            $expectedException;

        $this->setExpectedExceptionRegExp($expectedException, $expectedExceptionRegex);

        $this->createGraphInstance($data);
    }

    public function test_initialize_initializeTheGraph()
    {
        $graph = $this->createGraphInstance([
            'id'            => 'myGraph',
            'nodes'         => [
                [
                    'id'            => 'node1',
                    'isRootNode'    => true
                ],
                [
                    'id'            => 'node2',
                    'parentId'      => 'node1'
                ]
            ]
        ]);

        $this->assertEquals(2, $graph->countNodes());
        $this->assertEquals('myGraph', $graph->getId());

        $nodes = $graph->getNodes();

        $this->assertEquals('node1', $nodes['node1']->getId());
        $this->assertEquals('node2', $nodes['node2']->getId());
        $this->assertCount(1, $nodes['node1']->getChildren());
        $this->assertEquals('node2', $nodes['node1']->getChild('node2')->getId());
    }

    public function test_setNodes_setsGraphNodes()
    {
        $graph = $this->createGraphInstance(
            [
                'id'                        => 'graph1'
            ],
            [
                'validateMinChildren'       => false
            ]
        );

        $node = new Node(['id' => 'node1']);
        $node2 = new Node(['id' => 'node2']);

        $node2->setParent($node);

        $graph->setNodes([$node, $node2]);

        $nodes = $graph->getNodes();

        $this->assertEquals('graph1', $graph->getId());
        $this->assertEquals('node1', $nodes['node1']->getId());
        $this->assertEquals('node2', $nodes['node2']->getId());
    }




    // Data Providers

    public function invalidIdDataProvider()
    {
        return [
            [
                []
            ],
            [
                ['id' => '']
            ],
            [
                ['id' => []]
            ]
        ];
    }

    public function invalidNodesDataProvider()
    {
        $nodeDoesNotExist = new NodeDoesNotExistException();

        return [
            [
                ['id' => 'myId', 'nodes' => ''],
                null,
                '/Field \"nodes\" must be an array\./'
            ],
            [
                ['id' => 'myId', 'nodes' => 'nodes'],
                null,
                '/Field \"nodes\" must be an array\./'
            ],
            [
                ['id' => 'myId', 'nodes' => ['invalidElement']],
                null,
                '/Field \"nodes\" must be an array of arrays/'
            ],
            [
                ['id' => 'myId', 'nodes' => [[]]],
                null,
                '/Field \"id\" must be a non\-empty string\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => '']]],
                null,
                '/Field \"id\" must be a non\-empty string\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'parentId' => 1234]]],
                null,
                '/Field \"parentId\" must be a non\-empty string\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'parentId' => ['1']]]],
                null,
                '/Field \"parentId\" must be a non\-empty string\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'parentId' => 'invalidParentId']]],
                get_class($nodeDoesNotExist)
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'childrenIds' => '']]],
                null,
                '/Field \"childrenIds\" must be an array\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'childrenIds' => [[]]]]],
                null,
                '/Each element of field \"childrenIds\" must be a non\-empty string\./'
            ],
            [
                ['id' => 'myId', 'nodes' => [['id' => 'a', 'childrenIds' => ['invalidParentId']]]],
                get_class($nodeDoesNotExist)
            ]
        ];
    }

    // Helper Methods

    /**
     * @param array $data
     * @param array $options
     *
     * @return Graph
     */
    protected function createGraphInstance(array $data = [], array $options = []): Graph
    {
        return new Graph($data, $options);
    }
}