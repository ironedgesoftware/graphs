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
use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ValidationException;

interface NodeInterface extends SubscriberInterface
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
     * @param string $attr - Attribute.
     * @param mixed $value - Value.
     * @param array $options - Options.
     *
     * @return $this
     */
    public function setMetadataAttr(string $attr, $value, array $options = []);

    /**
     * Returns a metadata attribute.
     *
     * @param string $attr - Attribute.
     * @param mixed $defaultValue - Default value.
     * @param array $options - Options.
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
     * @param string|null $id - Parent ID.
     *
     * @return NodeInterface|null
     */
    public function getParent(string $id = null);

    /**
     * Returns an array of parents.
     *
     * @return array
     */
    public function getParents(): array;

    /**
     * Finds parents up from this node.
     *
     * @param array $filters - Filters.
     * @param array $options - Options.
     *
     * @return array|NodeInterface
     */
    public function findParents(array $filters = [], array $options = []);

    /**
     * Returns all parents from this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getAllParents(array $options = []): array;

    /**
     * Returns all parents from this node plus this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getAllParentsAndCurrentNode(array $options = []): array;

    /**
     * Adds a parent for this node.
     *
     * @param NodeInterface $parent          - Parent.
     * @param bool          $setParentsChild - Set parent's child.
     *
     * @return NodeInterface
     */
    public function addParent(NodeInterface $parent, bool $setParentsChild = true): NodeInterface;

    /**
     * Removes a parent from this node.
     *
     * @param string $parentId        - Parent.
     * @param bool   $setParentsChild - Set parent's child.
     *
     * @return NodeInterface
     */
    public function removeParent(string $parentId, bool $setParentsChild = true): NodeInterface;

    /**
     * Clears all parents of this node.
     *
     * @return NodeInterface
     */
    public function clearParents(): NodeInterface;

    /**
     * Returns the value of field _children.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getChildren(array $options = []): array;

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
     * @param bool $setChildParent - Set child's parent.
     *
     * @throws ChildTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function addChild(NodeInterface $child, bool $setParent = true): NodeInterface;

    /**
     * Removes a child.
     *
     * @param string $childId   - Child ID.
     * @param bool   $setParent - Set parent?
     *
     * @return NodeInterface
     */
    public function removeChild(string $childId, bool $setParent = true): NodeInterface;

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
     * Finds children.
     *
     * @param array $filters - Filters.
     * @param array $options - Options.
     *
     * @return array|NodeInterface
     */
    public function findChildren(array $filters = []);

    /**
     * Returns count of children.
     *
     * @return int
     */
    public function countChildren(): int;

    /**
     * Returns the value of field _nodes.
     *
     * @return array
     */
    public function getNodes(): array;

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
     * Adds a node to this graph.
     *
     * @param NodeInterface $node - Node.
     *
     * @return NodeInterface
     */
    public function addNode(NodeInterface $node): NodeInterface;

    /**
     * Removes a node.
     *
     * @param NodeInterface $node - Node.
     *
     * @return NodeInterface
     */
    public function removeNode(NodeInterface $node): NodeInterface;

    /**
     * Returns how many nodes does this graph have.
     *
     * @return int
     */
    public function countNodes(): int;

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
     * Sets a validation config.
     *
     * @param string $name  - Validation name.
     * @param mixed  $value - Validation value.
     *
     * @return NodeInterface
     */
    public function setValidationConfig(string $name, $value): NodeInterface;

    /**
     * Returns a validation config value, or $default it it does not exist.
     *
     * @param string $name    - Validation name.
     * @param mixed  $default - Default value.
     *
     * @return mixed
     */
    public function getValidationConfig(string $name, $default = null);

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
     * This method is called after initializing the data.
     *
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return void
     */
    public function validate(array $options = []);

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
     * Removes a subscriber.
     *
     * @param string|SubscriberInterface $idOrSubscriber - Subscriber or ID.
     *
     * @return NodeInterface
     */
    public function removeSubscriber($idOrSubscriber): NodeInterface;

    /**
     * Fires an event. Subscribers gets notified about this event.
     *
     * @param string $eventId   - Event ID.
     * @param array  $eventData - Event Data.
     *
     * @return void
     */
    public function notifySubscribers(string $eventId, array $eventData);

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