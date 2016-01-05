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


use IronEdge\Component\CommonUtils\Data\Data;

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
     * Returns the value of field _name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets the value of field name.
     *
     * @param string $name - name.
     *
     * @return $this
     */
    public function setName(string $name);

    /**
     * Returns the value of field _metadata.
     *
     * @return Data
     */
    public function getMetadata(): Data;

    /**
     * Sets the value of field metadata.
     *
     * @param array $metadata - metadata.
     *
     * @return NodeInterface
     */
    public function setMetadata(array $metadata);

    /**
     * Sets a metadata attribute.
     *
     * @param string $attr    - Attribute.
     * @param mixed  $value   - Value.
     * @param array  $options - Options.
     *
     * @return $this
     */
    public function setMetadataAttr(string $attr, $value, array $options = []);

    /**
     * Returns a metadata attribute.
     *
     * @param string $attr         - Attribute.
     * @param mixed  $defaultValue - Default value.
     * @param array  $options      - Options.
     *
     * @return mixed
     */
    public function getMetadataAttr(string $attr, $defaultValue = null, array $options = []);

    /**
     * Returns true if metadata has attribute $attr, or false otherwise.
     *
     * @param string $attr - Attribute.
     *
     * @return bool
     */
    public function hasMetadataAttr(string $attr): bool;

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

    /**
     * Returns true if this node supports the following child.
     *
     * @param NodeInterface $child - Child node.
     *
     * @return bool
     */
    public function supportsChild(NodeInterface $child): bool;

    /**
     * Returns true if this node supports the following parent.
     *
     * @param NodeInterface $parent - Parent node.
     *
     * @return bool
     */
    public function supportsParent(NodeInterface $parent): bool;

    /**
     * Initializes the node.
     *
     * @param array $data    - Node's data.
     * @param array $options - Options.
     *
     * @return NodeInterface
     */
    public function initialize(array $data, array $options = []);
}