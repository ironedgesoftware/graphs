<?php
/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Node;


interface NodeInterface
{
    /**
     * Returns the value of field _id.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Sets the value of field id.
     *
     * @param string $id - id.
     *
     * @return NodeInterface
     */
    public function setId(string $id): NodeInterface;

    /**
     * Returns the value of field _parent.
     *
     * @return NodeInterface
     */
    public function getParent(): NodeInterface;

    /**
     * Sets the value of field parent.
     *
     * @param NodeInterface $parent - parent.
     *
     * @return NodeInterface
     */
    public function setParent(NodeInterface $parent): NodeInterface;

    /**
     * Returns the value of field _children.
     *
     * @return array
     */
    public function getChildren(): array;

    /**
     * Sets the value of field children.
     *
     * @param array $children - children.
     *
     * @return NodeInterface
     */
    public function setChildren(array $children): NodeInterface;

    /**
     * Adds a child to this node.
     *
     * @param NodeInterface $child - Child.
     *
     * @return NodeInterface
     */
    public function addChild(NodeInterface $child): NodeInterface;
}