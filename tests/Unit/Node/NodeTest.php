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

use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Node\Node;
use IronEdge\Component\Graphs\Node\NodeInterface;
use IronEdge\Component\Graphs\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class NodeTest extends AbstractTestCase
{
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

        $node->setParent($node3);
        $node->addChild($node2);

        $this->assertEquals($node3, $node->getParent());
        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->canSupportChildren = false;

        $node->setParent($node3);

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
        $node->setParent($node3);

        $this->assertEquals($node3, $node->getParent());
        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->canSupportParent = false;

        $node->setChildren([$node2]);

        $this->assertEquals(['node2' => $node2], $node->getChildren());

        $node->setParent($node3);
    }



    // Data Providers

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
            ]
        ];
    }

    // Helper Methods

    /**
     * @return Node
     */
    protected function createNodeInstance(array $data = [], array $options = []): Node
    {
        return new Node($data);
    }

    /**
     * @return Node
     */
    protected function createCustomNodeInstance(array $data): Node
    {
        return new CustomNode($data);
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
}