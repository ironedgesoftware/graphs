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
use IronEdge\Component\Graphs\Node\NodeInterface;

/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
interface GraphInterface extends NodeInterface
{
    /**
     * Returns the value of field _nodes.
     *
     * @return array
     */
    public function getNodes(): array;

    /**
     * Sets the value of field nodes.
     *
     * @param array $nodes - nodes.
     *
     * @return GraphInterface
     */
    public function setNodes(array $nodes): GraphInterface;

    /**
     * Adds a node to this graph.
     *
     * @param NodeInterface $node - Node.
     *
     * @return GraphInterface
     */
    public function addNode(NodeInterface $node): GraphInterface;

    /**
     * Returns the node with ID $id.
     *
     * @param string $id - Node ID.
     *
     * @throws NodeDoesNotExistException
     *
     * @return NodeInterface
     */
    public function getNode(string $id): NodeInterface;

    /**
     * Returns true if node with ID $id is set on this graph.
     *
     * @param string $id - Node ID.
     *
     * @return bool
     */
    public function hasNode(string $id): bool;

    /**
     * Returns how many nodes does this graph have.
     *
     * @return int
     */
    public function countNodes(): int;

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
    public function createNode(array $data, array $options = []): NodeInterface;

    /**
     * Initializes the node.
     *
     * @param array $data    - Data.
     * @param array $options - Initialization options.
     *
     * @throws ValidationException
     *
     * @return NodeInterface
     */
    public function initialize(array $data, array $options = []): NodeInterface;
}