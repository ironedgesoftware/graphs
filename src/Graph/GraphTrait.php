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

use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Node\NodeTrait;

/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
trait GraphTrait
{
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
     * Field _options.
     *
     * @var array
     */
    private $_options = [];



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
     * @return self
     */
    public function setId(string $id): self
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
     * @return self
     */
    public function setNodes(array $nodes): self
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
     * @param NodeTrait $node - Node.
     *
     * @return self
     */
    public function addNode(NodeTrait $node): self
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
     * @return NodeTrait
     */
    public function getNode(string $id): NodeTrait
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
     * Returns the value of field _options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->_options;
    }

    /**
     * Sets the value of field options.
     *
     * @param array $options - options.
     *
     * @return self
     */
    public function setOptions($options): self
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Initializes the graph with an array of data.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return self
     */
    public function initialize(array $data, array $options = []): self
    {
        if (!isset($data['id']) || !is_string($data['id']) || $data['id']) {
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
                                'Each element of field "childrenIds" must be a non-empty string'
                            );
                        }

                        $currentNode->addChild($this->getNode($nodeId));
                    }
                }
            }
        }
    }

    /**
     * Creates a node.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return NodeTrait
     */
    public function createNode(array $data, array $options = []): NodeTrait
    {
        $node = $this->createNodeInstance($data, $options);

        if (!isset($data['id']) || !is_string($data['id']) || $data['id']) {
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
     * @return NodeTrait
     */
    public abstract function createNodeInstance(array $data, array $options = []): NodeTrait;
}