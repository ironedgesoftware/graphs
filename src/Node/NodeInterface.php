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
use IronEdge\Component\Graphs\Event\SubscriberInterface;
use IronEdge\Component\Graphs\Exception\ChildTypeNotSupportedException;

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
     * Resets the metadata.
     *
     * @return $this
     */
    public function resetMetadata();

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
     * @return NodeInterface|null
     */
    public function getParent();

    /**
     * Sets the value of field parent.
     *
     * @param NodeInterface $parent          - Parent.
     * @param bool          $setParentsChild - Set parent's child.
     *
     * @return NodeInterface
     */
    public function setParent(NodeInterface $parent = null, bool $setParentsChild = true): NodeInterface;

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
     * @param NodeInterface $child          - Child.
     * @param bool          $setChildParent - Set child's parent.
     *
     * @throws ChildTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function addChild(NodeInterface $child, bool $setParent = true): NodeInterface;

    /**
     * Removes a child.
     *
     * @param string $childId - Child ID.
     *
     * @return NodeInterface
     */
    public function removeChild(string $childId): NodeInterface;

    /**
     * Returns a child by ID.
     *
     * @param string $id - Node ID.
     *
     * @throws ChildTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function getChild(string $id): NodeInterface;

    /**
     * Returns true if this node has a child with ID $id, or false otherwise.
     *
     * @param string $id - Node ID.
     *
     * @return bool
     */
    public function hasChild(string $id): bool;

    /**
     * Returns count of children.
     *
     * @return int
     */
    public function countChildren(): int;

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

    /**
     * Returns the value of field _subscribers.
     *
     * @return array
     */
    public function getSubscribers(): array;

    /**
     * Sets the value of field subscribers.
     *
     * @param array $subscribers - subscribers.
     *
     * @return NodeInterface
     */
    public function setSubscribers(array $subscribers): NodeInterface;

    /**
     * Adds a subscriber.
     *
     * @param SubscriberInterface $subscriber - Subscriber.
     *
     * @return NodeInterface
     */
    public function addSubscriber(SubscriberInterface $subscriber): NodeInterface;

    /**
     * Fires an event. Subscribers gets notified about this event.
     *
     * @param string $id  - Event ID.
     * @param array $data - Event Data.
     *
     * @return void
     */
    public function notifySubscribers(string $id, array $data);

    /**
     * Returns default metadata.
     *
     * @return array
     */
    public function getDefaultMetadata();

    /**
     * Returns an array representation of this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function toArray(array $options = []);
}