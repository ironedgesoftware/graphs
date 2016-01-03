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

namespace IronEdge\Component\Graphs\Graph;

use IronEdge\Component\CommonUtils\Options\OptionsTrait;
use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Node\NodeInterface;

/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
trait GraphTrait
{
    use OptionsTrait;

    /**
     * Field _id.
     *
     * @var string
     */
    private $_id;

    /**
     * Field _nodes.
     *
     * @var array
     */
    private $_nodes = [];



    /**
     * Returns the value of field _id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->_id;
    }

    /**
     * Sets the value of field id.
     *
     * @param string $id - id.
     *
     * @return GraphInterface
     */
    public function setId(string $id): GraphInterface
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * Returns the value of field _nodes.
     *
     * @return array
     */
    public function getNodes(): array
    {
        return $this->_nodes;
    }

    /**
     * Sets the value of field nodes.
     *
     * @param array $nodes - nodes.
     *
     * @return GraphInterface
     */
    public function setNodes(array $nodes): GraphInterface
    {
        $this->_nodes = [];

        foreach ($nodes as $node) {
            $this->addNode($node);
        }

        return $this;
    }

    /**
     * Adds a node to this graph.
     *
     * @param NodeInterface $node - Node.
     *
     * @return GraphInterface
     */
    public function addNode(NodeInterface $node): GraphInterface
    {
        $this->_nodes[$node->getId()] = $node;

        return $this;
    }

    /**
     * Returns the node with ID $id.
     *
     * @param string $id - Node ID.
     *
     * @throws NodeDoesNotExistException
     *
     * @return NodeInterface
     */
    public function getNode(string $id): NodeInterface
    {
        if (!$this->hasNode($id)) {
            throw NodeDoesNotExistException::create($id);
        }

        return $this->_nodes[$id];
    }

    /**
     * Returns true if node with ID $id is set on this graph.
     *
     * @param string $id - Node ID.
     *
     * @return bool
     */
    public function hasNode(string $id): bool
    {
        return isset($this->_nodes[$id]);
    }

    /**
     * Returns how many nodes does this graph have.
     *
     * @return int
     */
    public function countNodes(): int
    {
        return count($this->_nodes);
    }

    /**
     * Initializes the graph with an array of data.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return GraphInterface
     */
    public function initialize(array $data, array $options = []): GraphInterface
    {
        if (!isset($data['id']) || !is_string($data['id']) || $data['id'] === '') {
            throw ValidationException::create('Field "id" must be a non-empty string.');
        }

        $this->setId($data['id'])
            ->setOptions($options);

        if (isset($data['nodes'])) {
            if (!is_array($data['nodes'])) {
                throw ValidationException::create('Field "nodes" must be an array.');
            }

            foreach ($data['nodes'] as $nodeData) {
                if (!is_array($nodeData)) {
                    throw ValidationException::create('Field "nodes" must be an array of arrays.');
                }

                $this->addNode($this->createNode($nodeData, $options));
            }

            // Now, set parents and children of nodes

            foreach ($data['nodes'] as $nodeData) {
                $currentNode = $this->getNode($nodeData['id']);

                if (isset($nodeData['parentId'])) {
                    if (!is_string($nodeData['parentId']) || $nodeData['parentId'] === '') {
                        throw ValidationException::create('Field "parentId" must be a non-empty string.');
                    }

                    $currentNode->setParent($this->getNode($nodeData['parentId']));
                }

                if (isset($nodeData['childrenIds'])) {
                    if (!is_array($nodeData['childrenIds'])) {
                        throw ValidationException::create('Field "childrenIds" must be an array.');
                    }

                    foreach ($nodeData['childrenIds'] as $nodeId) {
                        if (!is_string($nodeId) || $nodeId === '') {
                            throw ValidationException::create(
                                'Each element of field "childrenIds" must be a non-empty string.'
                            );
                        }

                        $currentNode->addChild($this->getNode($nodeId));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Creates a node.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return NodeInterface
     */
    public function createNode(array $data, array $options = []): NodeInterface
    {
        $node = $this->createNodeInstance($data, $options);

        if (!isset($data['id']) || !is_string($data['id']) || $data['id'] === '') {
            throw ValidationException::create('Field "id" must be a non-empty string.');
        }

        $node->setId($data['id']);

        return $node;
    }

    /**
     * Creates an instance of a node.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @return NodeInterface
     */
    public abstract function createNodeInstance(array $data, array $options = []): NodeInterface;
}