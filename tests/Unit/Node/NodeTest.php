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

namespace IronEdge\Component\Graphs\Test\Unit\Node;

use IronEdge\Component\Graphs\Event\SubscriberInterface;
use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Node\Node;
use IronEdge\Component\Graphs\Node\NodeInterface;
use IronEdge\Component\Graphs\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class NodeTest extends AbstractTestCase
{
    public function test_findParents_shouldFindParentsByFilters() {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2', 'name' => 'myNode2']);
        $node3 = $this->createNodeInstance(['id' => 'node3', 'name' => 'myNode2']);
        $node4 = $this->createNodeInstance(['id' => 'node4', 'name' => 'myNode4']);

        $node4->addParent($node3);
        $node3->addParent($node2);
        $node2->addParent($node);

        $result = $node4->findParents(['name' => 'myNode2'], ['indexById' => true]);

        $this->assertCount(2, $result);
        $this->assertEquals($node2, $result['node2']);
        $this->assertEquals($node3, $result['node3']);

        $result = $node4->findParents(['name' => 'myNode2'], ['indexById' => false]);

        $this->assertCount(2, $result);
        $this->assertEquals($node3, $result[0]);
        $this->assertEquals($node2, $result[1]);

        $result = $node4->findParents(['id' => 'node2', 'name' => 'myNode2'], ['indexById' => false]);

        $this->assertCount(1, $result);
        $this->assertEquals($node2, $result[0]);

        $result = $node4->findParents([], ['indexById' => true]);

        $this->assertCount(3, $result);
        $this->assertEquals($node, $result['node1']);
        $this->assertEquals($node2, $result['node2']);
        $this->assertEquals($node3, $result['node3']);

        $result = $node4->findParents([], ['indexById' => true, 'returnFirstResult' => true]);

        $this->assertEquals($node3, $result);
    }

    public function test_getAllParents_shouldFindAllParents()
    {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2', 'name' => 'myNode2']);
        $node3 = $this->createNodeInstance(['id' => 'node3', 'name' => 'myNode2']);
        $node4 = $this->createNodeInstance(['id' => 'node4', 'name' => 'myNode4']);

        $node4->addParent($node3);
        $node3->addParent($node2);
        $node2->addParent($node);

        $parents = [
            'node3' => $node3,
            'node2' => $node2,
            'node1' => $node
        ];
        $result = $node4->getAllParents();

        $this->assertEquals(array_values($parents), $result);

        $result = $node4->getAllParents(['indexById' => true]);

        $this->assertEquals($parents, $result);
    }

    public function test_removeSubscriber_worksWithIdOrInstance()
    {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2']);

        $this->assertCount(1, $node->getSubscribers());

        $node->addSubscriber($node2);

        $this->assertCount(2, $node->getSubscribers());

        $node->removeSubscriber($node2);

        $this->assertCount(1, $node->getSubscribers());

        $node->addSubscriber($node2);

        $this->assertCount(2, $node->getSubscribers());

        $node->removeSubscriber($node2->getId());

        $this->assertCount(1, $node->getSubscribers());
    }

    public function test_findChildren_ifNodesAreFoundThenReturnThem()
    {
        $node = $this->createExampleTree();
        $nodes = $node->findChildren(['id' => 'node2']);

        $this->assertCount(1, $nodes);
        $this->assertEquals($node->getNode('node2'), $nodes[0]);

        $nodes = $node->findChildren(['name' => 'someNode']);

        $this->assertCount(2, $nodes);
        $this->assertEquals($node->getNode('node3'), $nodes[0]);
        $this->assertEquals($node->getNode('node5'), $nodes[1]);

        $resultNode = $node->findChildren(
            ['name' => 'someNode'],
            ['returnFirstResult' => true]
        );

        $this->assertEquals($node->getNode('node3'), $resultNode);

        $nodes = $node->findChildren();

        $this->assertCount(4, $nodes);
        $this->assertEquals($node->getNode('node2'), $nodes[0]);
        $this->assertEquals($node->getNode('node3'), $nodes[1]);
        $this->assertEquals($node->getNode('node4'), $nodes[2]);
        $this->assertEquals($node->getNode('node5'), $nodes[3]);
    }

    public function test_findChildren_ifNoNodesAreFoundReturnEmptyArray()
    {
        $node = $this->createExampleTree();

        $this->assertCount(0, $node->findChildren(['iDontExist' => 'value']));
    }

    public function test_removeSubscriber_throwsExceptionIfArgumentIsInvalid()
    {
        $this->setExpectedExceptionRegExp('\InvalidArgumentException');

        $node = $this->createNodeInstance(['id' => 'n1']);

        $node->removeSubscriber([]);
    }

    public function test_subscribers_shouldBeRegisteredAccordingly()
    {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2']);
        $node3 = $this->createNodeInstance(['id' => 'node3']);
        $node4 = $this->createNodeInstance(['id' => 'node4']);
        $node5 = $this->createNodeInstance(['id' => 'node5']);

        $this->assertCount(0, $node->getNodes());
        $this->assertCount(0, $node2->getNodes());
        $this->assertCount(0, $node3->getNodes());
        $this->assertCount(0, $node4->getNodes());
        $this->assertCount(0, $node5->getNodes());

        $node->addChild($node2);

        $this->assertCount(1, $node->getNodes());
        $this->assertEquals($node2, $node->getNode('node2'));
        $this->assertCount(0, $node2->getNodes());
        $this->assertCount(0, $node3->getNodes());
        $this->assertCount(0, $node4->getNodes());
        $this->assertCount(0, $node5->getNodes());

        $node3->addParent($node2);

        $this->assertCount(2, $node->getNodes());
        $this->assertEquals($node2, $node->getNode('node2'));
        $this->assertEquals($node3, $node->getNode('node3'));
        $this->assertCount(1, $node2->getNodes());
        $this->assertEquals($node3, $node2->getNode('node3'));
        $this->assertCount(0, $node3->getNodes());
        $this->assertCount(0, $node4->getNodes());
        $this->assertCount(0, $node5->getNodes());

        $node2->clearParents();

        $this->assertCount(0, $node->getNodes());
        $this->assertCount(1, $node2->getNodes());
        $this->assertEquals($node3, $node2->getNode('node3'));
        $this->assertCount(0, $node3->getNodes());
        $this->assertCount(0, $node4->getNodes());
        $this->assertCount(0, $node5->getNodes());

        $node2->addParent($node);

        $this->assertCount(2, $node->getNodes());
        $this->assertEquals($node2, $node->getNode('node2'));
        $this->assertEquals($node3, $node->getNode('node3'));
        $this->assertCount(1, $node2->getNodes());
        $this->assertEquals($node3, $node2->getNode('node3'));
        $this->assertCount(0, $node3->getNodes());
        $this->assertCount(0, $node4->getNodes());
        $this->assertCount(0, $node5->getNodes());
    }

    public function test_setSubscribers_clearsOriginalSubscribersAndSetsNewOnes()
    {
        $node = $this->createNodeInstance(
            [
                'id'            => 'node1',
                'children'      => [
                    new Node(['id' => 'node2'])
                ]
            ]
        );

        $this->assertCount(1, $node->getSubscribers());
        $this->assertCount(2, $node->getNode('node2')->getSubscribers());

        $node->setSubscribers([]);

        $this->assertCount(0, $node->getSubscribers());
        $this->assertCount(2, $node->getNode('node2')->getSubscribers());

        $node->getNode('node2')->setSubscribers([]);

        $this->assertCount(0, $node->getSubscribers());
        $this->assertCount(0, $node->getNode('node2')->getSubscribers());

        $node->setSubscribers([
            new class implements SubscriberInterface {
                public function getId(): string { return 'a'; }
                public function handleEvent(string $id, array $data) {}
            },
            new class implements SubscriberInterface {
                public function getId(): string { return 'b'; }
                public function handleEvent(string $id, array $data) {}
            },
            new class implements SubscriberInterface {
                public function getId(): string { return 'c'; }
                public function handleEvent(string $id, array $data) {}
            }
        ]);

        $this->assertCount(3, $node->getSubscribers());
        $this->assertCount(0, $node->getNode('node2')->getSubscribers());
    }

    public function test_getNode_shouldThrowExceptionIfNodeDoesNotExists()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new NodeDoesNotExistException())
        );

        $node = $this->createNodeInstance(
            [
                'id'            => 'node1'
            ]
        );

        $node->getNode('node2');
    }

    public function test_getNode_shouldReturnTheNodeIfItExists()
    {
        $node = $this->createNodeInstance(
            [
                'id'            => 'node1',
                'children'      => [
                    new Node(['id' => 'node2'])
                ]
            ]
        );

        $node2 = $node->getNode('node2');

        $this->assertEquals('node2', $node2->getId());
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ValidationException
     * @dataProvider invalidIdDataProvider
     */
    public function test_initialize_ifIdIsInvalidThenThrowException(array $data)
    {
        $this->createNodeInstance($data);
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

        $this->createNodeInstance($data);
    }

    public function test_initialize_initializeTheGraph()
    {
        $graph = $this->createNodeInstance(
            [
                'id'            => 'myGraph',
                'children'      => [
                    new Node(
                        [
                            'id' => 'node1',
                            'children'  => [
                                new Node(['id' => 'node2'])
                            ]
                        ]
                    )
                ]
            ]
        );

        $this->assertEquals(1, $graph->countChildren());
        $this->assertEquals(2, $graph->countNodes());
        $this->assertEquals('myGraph', $graph->getId());

        $nodes = $graph->getNodes();

        $this->assertEquals('node1', $nodes['node1']->getId());
        $this->assertEquals('node2', $nodes['node2']->getId());
        $this->assertCount(1, $nodes['node1']->getChildren());
        $this->assertEquals('node2', $nodes['node1']->getChild('node2')->getId());
    }

    public function test_setParent_shouldRemoveParentsChild()
    {
        $data = [
            'id'        => 'node1'
        ];
        $node = $this->createNodeInstance($data);
        $data = [
            'id'        => 'node2'
        ];
        $node2 = $this->createNodeInstance($data);

        $node->addParent($node2);

        $this->assertTrue($node2->hasChild('node1'));

        $node->clearParents();

        $this->assertFalse($node2->hasChild('node1'));

        // Should not fail if called again

        $node->clearParents();

        $this->assertFalse($node2->hasChild('node1'));
    }

    public function test_validate_shouldThrowExceptionIfParentIsSet()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            '/Node "node1" must NOT have a Parent/'
        );

        $data = [
            'id'        => 'node1'
        ];
        $node = $this->createNodeInstance($data);
        $data = [
            'id'        => 'node2'
        ];
        $node2 = $this->createNodeInstance($data);

        $node->addParent($node2);

        $node->setValidationConfig('parentMustNotBeSet', true);
        $node->validate();
    }

    public function test_validate_shouldThrowExceptionIfParentIsNotSet()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            '/Node "node1" must have a Parent/'
        );

        $data = [
            'id'        => 'node1'
        ];
        $node = $this->createNodeInstance($data);

        $node->setValidationConfig('parentMandatory', true);
        $node->validate();
    }

    public function test_validate_shouldThrowExceptionIfMaxChildrenIsExceeded()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            '/Children cannot exceed a maximum of 1/'
        );

        $data = [
            'id'        => 'node1'
        ];
        $node = $this->createNodeInstance($data);
        $data = [
            'id'        => 'node2'
        ];
        $node2 = $this->createNodeInstance($data);
        $data = [
            'id'        => 'node3'
        ];
        $node3 = $this->createNodeInstance($data);

        $node->setValidationConfig('maxChildren', 1);

        $node->setChildren([$node2, $node3]);

        $node->validate();
    }

    public function test_validate_shouldThrowExceptionIfMinChildrenIsNotMeet()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            '/Children must be, at least, 1/'
        );

        $data = [
            'id'        => 'node1'
        ];
        $node = $this->createNodeInstance($data);

        $node->setValidationConfig('minChildren', 1);
        $node->validate();
    }

    public function test_initialize_setsDataSuccessfully()
    {
        $data = [
            'id'        => 'node1',
            'name'      => 'node 1',
            'metadata'  => [
                'attr1'     => [
                    'attr1Name'      => 'attr1Value'
                ]
            ]
        ];

        $node = $this->createNodeInstance($data);

        $data = array_replace_recursive(
            $data,
            [
                'metadata'          => $node->getDefaultMetadata()
            ]
        );

        $this->assertEquals($data['id'], $node->getId());
        $this->assertEquals($data['name'], $node->getName());
        $this->assertEquals($data['metadata'], $node->getMetadata()->getData());
        $this->assertTrue($node->hasMetadataAttr('attr1.attr1Name'));
        $this->assertFalse($node->hasMetadataAttr('attr2.attr2Name'));
        $this->assertEquals($data['metadata']['attr1']['attr1Name'], $node->getMetadataAttr('attr1.attr1Name'));

        $node->setMetadataAttr('attr1.attr1Name', 'test');

        $this->assertEquals('test', $node->getMetadataAttr('attr1.attr1Name'));
    }

    public function test_supportChild_shouldWorkByDefault()
    {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2']);

        $this->assertTrue($node->supportsChild($node2));
    }

    /**
     * @dataProvider initializeExceptionsDataProvider
     */
    public function test_initialize_exceptions(array $data, $expectedException, $expectedExceptionMsgRegex)
    {
        $this->setExpectedExceptionRegExp($expectedException, $expectedExceptionMsgRegex);

        $this->createNodeInstance($data);
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ChildTypeNotSupportedException
     */
    public function test_supportChild_shouldThrowExceptionIfItDoesntSupportAChild()
    {
        $node = $this->createCustomNodeInstance(['id' => 'node1']);
        $node2 = $this->createCustomNodeInstance(['id' => 'node2']);
        $node3 = $this->createCustomNodeInstance(['id' => 'node3']);

        $node->addParent($node3);
        $node->addChild($node2);

        $this->assertEquals($node3, $node->getParent());
        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->canSupportChildren = false;

        $node->addParent($node3);

        $this->assertEquals($node3, $node->getParent());

        $node->setChildren([$node2]);
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ParentTypeNotSupportedException
     */
    public function test_supportParent_shouldThrowExceptionIfItDoesntSupportAParent()
    {
        $node = $this->createCustomNodeInstance(['id' => 'node1']);
        $node2 = $this->createCustomNodeInstance(['id' => 'node2']);
        $node3 = $this->createCustomNodeInstance(['id' => 'node3']);

        $node->addChild($node2);
        $node->addParent($node3);

        $this->assertEquals($node3, $node->getParent());
        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->canSupportParent = false;

        $node->setChildren([$node2]);

        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->addParent($node3);
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ChildDoesNotExistException
     */
    public function test_getChild_ifChildIsNotFoundThenThrowException()
    {
        $node = $this->createNodeInstance(['id' => 'someNode']);

        $node->getChild('iDontExist');
    }

    public function test_getChild_ifChildIExistsThenReturnIt()
    {
        $node = $this->createNodeInstance(['id' => 'someNode']);
        $node2 = $this->createNodeInstance(['id' => 'otherNode']);

        $node2->addChild($node);

        $child = $node2->getChild('someNode');

        $this->assertEquals($node, $child);
    }

    public function test_removeChild_removesChildIfItExists()
    {
        $node = $this->createNodeInstance(['id' => 'someNode']);
        $node2 = $this->createNodeInstance(['id' => 'otherNode']);

        $node2->addChild($node);

        $this->assertTrue($node2->hasChild('someNode'));

        $node2->removeChild('someNode');

        $this->assertFalse($node2->hasChild('someNode'));

        $node2->removeChild('someNode');

        $this->assertFalse($node2->hasChild('someNode'));
    }

    public function test_resetMetadata_shouldResetTheMetadata()
    {
        $node = $this->createCustomNodeInstance(
            ['id' => 'someId', 'metadata' => ['otherAttr' => 'otherValue']]
        );

        $this->assertEquals('testValue', $node->getMetadataAttr('testAttr'));
        $this->assertTrue($node->hasMetadataAttr('otherAttr'));
        $this->assertEquals('otherValue', $node->getMetadataAttr('otherAttr'));

        $node->resetMetadata();

        $this->assertEquals('testValue', $node->getMetadataAttr('testAttr'));
        $this->assertFalse($node->hasMetadataAttr('otherAttr'));
    }

    public function test_toArray_shouldReturnAnArrayRepresentationOfTheNode()
    {
        $data = [
            'id'            => 'someId',
            'name'          => 'someName',
            'type'          => 'node',
            'metadata'      => [
                'additionalAttr'        => 'additionalValue'
            ]
        ];
        $node = $this->createCustomNodeInstance($data);
        $data2 = [
            'id'            => 'otherNode',
            'name'          => 'someOtherName',
            'type'          => 'node',
            'metadata'      => [
                'otherAdditionalAttr'        => 'otherAdditionalValue'
            ]
        ];
        $node2 = $this->createCustomNodeInstance($data2);

        $node->addChild($node2);

        $data['parentId'] = null;
        $data['childrenIds'] = ['otherNode'];
        $data['metadata']['testAttr'] = 'testValue';

        $this->assertEquals($data, $node->toArray());

        $data2['parentId'] = 'someId';
        $data2['childrenIds'] = [];
        $data2['metadata']['testAttr'] = 'testValue';

        $this->assertEquals($data2, $node2->toArray());
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
        return [
            [
                ['id' => 'myId', 'children' => ''],
                null,
                '/Field \"children\" must be an array\./'
            ],
            [
                ['id' => 'myId', 'children' => 'children'],
                null,
                '/Field \"children\" must be an array\./'
            ],
            [
                ['id' => 'myId', 'parent' => new Node(['id' => 'node2']), 'children' => ['invalidElement']],
                null,
                '/Field \"children\" must be an array of NodeInterface instances/'
            ]
        ];
    }

    public function initializeExceptionsDataProvider()
    {
        $validationException = new ValidationException();
        $validationExceptionClass = get_class($validationException);

        return [
            [
                [
                    'id'            => null
                ],
                $validationExceptionClass,
                '/Field \"id\" must be a non\-empty string\./'
            ],
            [
                [
                    'id'            => 'node111',
                    'name'          => 1234
                ],
                $validationExceptionClass,
                '/Field \"name\" must be a non\-empty string\./'
            ],
            [
                [
                    'id'            => 'node111',
                    'name'          => 'myNode',
                    'metadata'      => 'invalidValue'
                ],
                $validationExceptionClass,
                '/Field \"metadata\" must be an array\./'
            ],
            [
                [
                    'id'            => 'node111',
                    'name'          => 'myNode',
                    'parent'        => 'invalidValue'
                ],
                $validationExceptionClass,
                '/Field \"parent\" must be an instance of NodeInterface/'
            ],
            [
                [
                    'id'            => 'node 111'
                ],
                $validationExceptionClass,
                '/Field \"id\" must have only characters 0-9, a-z, A-Z or \"\_\"\./'
            ]
        ];
    }

    // Helper Methods

    /**
     * @return Node
     */
    protected function createNodeInstance(array $data = [], array $options = []): Node
    {
        return new Node($data, $options);
    }

    /**
     * @return Node
     */
    protected function createCustomNodeInstance(array $data): Node
    {
        return new CustomNode($data);
    }

    protected function createExampleTree()
    {
        $node = $this->createNodeInstance(['id' => 'node1']);
        $node2 = $this->createNodeInstance(['id' => 'node2', 'name' => 'anotherNode']);
        $node3 = $this->createNodeInstance(['id' => 'node3', 'name' => 'someNode']);
        $node4 = $this->createNodeInstance(['id' => 'node4']);
        $node5 = $this->createNodeInstance(['id' => 'node5', 'name' => 'someNode']);

        $node->setChildren([$node2, $node3]);
        $node3->setChildren([$node4, $node5]);

        return $node;
    }
}

class CustomNode extends Node
{
    public $canSupportChildren = true;
    public $canSupportParent = true;

    public function supportsChild(NodeInterface $node): bool
    {
        return $this->canSupportChildren;
    }

    public function supportsParent(NodeInterface $node): bool
    {
        return $this->canSupportParent;
    }

    public function getDefaultMetadata()
    {
        return [
            'testAttr'  => 'testValue'
        ];
    }
}