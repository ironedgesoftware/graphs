<?php
/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Node;

use IronEdge\Component\CommonUtils\Data\Data;
use IronEdge\Component\Graphs\Event\SubscriberInterface;
use IronEdge\Component\Graphs\Exception\ChildDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ChildTypeNotSupportedException;
use IronEdge\Component\Graphs\Exception\ParentTypeNotSupportedException;
use IronEdge\Component\Graphs\Exception\ValidationException;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Node implements NodeInterface, SubscriberInterface
{
    /**
     * Field _id.
     *
     * @var string
     */
    private $_id;

    /**
     * Field _name.
     *
     * @var string
     */
    private $_name;

    /**
     * Field _parent.
     *
     * @var NodeInterface
     */
    private $_parent;

    /**
     * Field _children.
     *
     * @var array
     */
    private $_children = [];

    /**
     * Field _metadata.
     *
     * @var Data
     */
    private $_metadata;

    /**
     * Subscriber.
     *
     * @var array
     */
    private $_subscribers = [];


    /**
     * Node constructor.
     *
     * @param array $data    - Node's data.
     * @param array $options - Options.
     */
    public function __construct(array $data = [], array $options = [])
    {
        $this->initialize($data, $options);
    }

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
     * @return NodeInterface
     */
    public function setId(string $id): NodeInterface
    {
        $this->_id = $id;

        if ($this->_name === null) {
            $this->setName($id);
        }

        return $this;
    }

    /**
     * Returns the value of field _name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Sets the value of field name.
     *
     * @param string $name - name.
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Returns the value of field _metadata.
     *
     * @return Data
     */
    public function getMetadata(): Data
    {
        if ($this->_metadata === null) {
            $this->_metadata = new Data();

            $this->setMetadata([]);
        }

        return $this->_metadata;
    }

    /**
     * Sets the value of field metadata.
     *
     * @param array $metadata - metadata.
     *
     * @return NodeInterface
     */
    public function setMetadata(array $metadata)
    {
        $this->getMetadata()->setData(
            array_replace_recursive(
                $this->getDefaultMetadata(),
                $metadata
            )
        );

        return $this;
    }

    /**
     * Resets the metadata.
     *
     * @return $this
     */
    public function resetMetadata()
    {
        $this->setMetadata([]);

        return $this;
    }

    /**
     * Sets a metadata attribute.
     *
     * @param string $attr - Attribute.
     * @param mixed $value - Value.
     * @param array $options - Options.
     *
     * @return $this
     */
    public function setMetadataAttr(string $attr, $value, array $options = [])
    {
        $this->getMetadata()->set($attr, $value, $options);

        return $this;
    }

    /**
     * Returns a metadata attribute.
     *
     * @param string $attr - Attribute.
     * @param mixed $defaultValue - Default value.
     * @param array $options - Options.
     *
     * @return mixed
     */
    public function getMetadataAttr(string $attr, $defaultValue = null, array $options = [])
    {
        return $this->getMetadata()->get($attr, $defaultValue, $options);
    }

    /**
     * Returns true if metadata has attribute $attr, or false otherwise.
     *
     * @param string $attr - Attribute.
     *
     * @return bool
     */
    public function hasMetadataAttr(string $attr): bool
    {
        return $this->getMetadata()->has($attr);
    }

    /**
     * Returns the value of field _parent.
     *
     * @return NodeInterface|null
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Sets the value of field parent.
     *
     * @param NodeInterface $parent - Parent.
     * @param bool $setParentsChild - Set parent's child.
     *
     * @throws ParentTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function setParent(NodeInterface $parent = null, bool $setParentsChild = true): NodeInterface
    {
        if ($parent && !$this->supportsParent($parent)) {
            throw ParentTypeNotSupportedException::create($this, $parent);
        }

        $oldParent = $this->_parent;
        $this->_parent = $parent;

        if ($setParentsChild) {
            if ($parent) {
                $parent->addChild($this, false);
            } else if ($oldParent) {
                $oldParent->removeChild($this->getId());
            }
        }

        $this->notifySubscribers(
            'set_parent',
            ['oldParent' => $oldParent, 'newParent' => $parent, 'child' => $this]
        );

        return $this;
    }

    /**
     * Returns the value of field _children.
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->_children;
    }

    /**
     * Sets the value of field children.
     *
     * @param array $children - children.
     *
     * @return NodeInterface
     */
    public function setChildren(array $children): NodeInterface
    {
        $this->_children = [];

        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

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
    public function addChild(NodeInterface $child, bool $setParent = true): NodeInterface
    {
        if (!$this->supportsChild($child)) {
            throw ChildTypeNotSupportedException::create($this, $child);
        }

        $this->_children[$child->getId()] = $child;

        if ($setParent) {
            $child->setParent($this, false);
        }

        $this->notifySubscribers('add_child', ['parent' => $this, 'child' => $child]);

        return $this;
    }

    /**
     * Removes a child.
     *
     * @param string $childId - Child ID.
     *
     * @return NodeInterface
     */
    public function removeChild(string $childId): NodeInterface
    {
        if ($this->hasChild($childId)) {
            $child = $this->getChild($childId);

            $child->setParent(null, false);

            unset($this->_children[$childId]);

            $this->notifySubscribers('remove_child', ['parent' => $this, 'child' => $child]);
        }

        return $this;
    }

    /**
     * Returns a child by ID.
     *
     * @param string $id - Node ID.
     *
     * @throws ChildDoesNotExistException
     *
     * @return NodeInterface
     */
    public function getChild(string $id): NodeInterface
    {
        if (!$this->hasChild($id)) {
            throw ChildDoesNotExistException::create($this->getId(), $id);
        }

        return $this->_children[$id];
    }

    /**
     * Returns true if this node has a child with ID $id, or false otherwise.
     *
     * @param string $id - Node ID.
     *
     * @return bool
     */
    public function hasChild(string $id): bool
    {
        return isset($this->_children[$id]);
    }

    /**
     * Returns count of children.
     *
     * @return int
     */
    public function countChildren(): int
    {
        return count($this->getChildren());
    }

    /**
     * Initializes the node.
     *
     * @param array $data - Data.
     * @param array $options - Initialization options.
     *
     * @throws ValidationException
     *
     * @return NodeInterface
     */
    public function initialize(array $data, array $options = []): NodeInterface
    {
        if (!isset($data['id']) || !is_string($data['id']) || $data['id'] === '') {
            throw ValidationException::create('Field "id" must be a non-empty string.');
        }

        $this->setId($data['id']);

        if (isset($data['name'])) {
            if (!is_string($data['name']) || $data['name'] === '') {
                throw ValidationException::create('Field "name" must be a non-empty string.');
            }

            $this->setName($data['name']);
        }

        if (isset($data['metadata'])) {
            if (!is_array($data['metadata'])) {
                throw ValidationException::create('Field "metadata" must be an array.');
            }

            $this->setMetadata($data['metadata']);
        }

        $this->validate($options);

        return $this;
    }

    /**
     * This method is called after initializing the data.
     *
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return void
     */
    public function validate(array $options = [])
    {
        $options = array_replace(
            [
                'validateMinChildren'           => true,
                'validateMaxChildren'           => true,
                'validateParentMandatory'       => true,
                'validateParentMustNotBeSet'    => true
            ],
            $options
        );

        $countChildren = $this->countChildren();

        if ($options['validateMinChildren']
            && ($min = $this->getValidationConfig('minChildren')) !== null
            && $min > $countChildren
        ) {
            throw ValidationException::create(
                'Children must be, at least, ' . $min . '. Currently, node "' . $this->getId() . '" has ' .
                $countChildren . ' children.'
            );
        }

        if ($options['validateMaxChildren']
            && ($max = $this->getValidationConfig('maxChildren')) !== null
            && $max < $countChildren
        ) {
            throw ValidationException::create(
                'Children cannot exceed a maximum of ' . $max . '. Currently, node "' . $this->getId() . '" has ' .
                $countChildren . ' children.'
            );
        }

        if ($options['validateParentMandatory']
            && $this->getValidationConfig('parentMandatory')
            && $this->getParent() === null
        ) {
            throw ValidationException::create(
                'Node "' . $this->getId() . '" must have a Parent!'
            );
        }

        if ($options['validateParentMustNotBeSet']
            && $this->getValidationConfig('parentMustNotBeSet')
            && $this->getParent() !== null
        ) {
            throw ValidationException::create(
                'Node "' . $this->getId() . '" must NOT have a Parent!'
            );
        }
    }

    /**
     * Returns true if this node supports the following child.
     *
     * @param NodeInterface $child - Child node.
     *
     * @return bool
     */
    public function supportsChild(NodeInterface $child): bool
    {
        return true;
    }

    /**
     * Returns true if this node supports the following parent.
     *
     * @param NodeInterface $parent - Parent node.
     *
     * @return bool
     */
    public function supportsParent(NodeInterface $parent): bool
    {
        return true;
    }

    /**
     * Returns a validation config value, or $default it it does not exist.
     *
     * @param string $name - Validation name.
     * @param mixed $default - Default value.
     *
     * @return mixed
     */
    public function getValidationConfig(string $name, $default = null)
    {
        return $this->getMetadataAttr('validations.' . $name, $default);
    }

    /**
     * Sets a validation config.
     *
     * @param string $name - Validation name.
     * @param mixed $value - Validation value.
     *
     * @return NodeInterface
     */
    public function setValidationConfig(string $name, $value): NodeInterface
    {
        $this->setMetadataAttr('validations.'.$name, $value);

        return $this;
    }

    /**
     * Returns default metadata.
     *
     * @return array
     */
    public function getDefaultMetadata()
    {
        return [
            'validations'           => [
                'minChildren'           => null,
                'maxChildren'           => null,
                'parentMandatory'       => false,
                'parentMustNotBeSet'    => false
            ]
        ];
    }

    /**
     * Returns the value of field _subscribers.
     *
     * @return array
     */
    public function getSubscribers(): array
    {
        return $this->_subscribers;
    }

    /**
     * Sets the value of field subscribers.
     *
     * @param array $subscribers - subscribers.
     *
     * @return NodeInterface
     */
    public function setSubscribers(array $subscribers): NodeInterface
    {
        $this->_subscribers = [];

        foreach ($subscribers as $subscriber) {
            $this->addSubscriber($subscriber);
        }

        return $this;
    }

    /**
     * Adds a subscriber.
     *
     * @param SubscriberInterface $subscriber - Subscriber.
     *
     * @return NodeInterface
     */
    public function addSubscriber(SubscriberInterface $subscriber): NodeInterface
    {
        $this->_subscribers[$subscriber->getId()] = $subscriber;

        return $this;
    }


    /**
     * This method is called when an event is fired.
     *
     * @param string $id  - Event ID.
     * @param array $data - Event Data.
     *
     * @return void
     */
    public function handleEvent(string $id, array $data)
    {

    }

    /**
     * Fires an event. Subscribers gets notified about this event.
     *
     * @param string $id  - Event ID.
     * @param array $data - Event Data.
     *
     * @return void
     */
    public function notifySubscribers(string $id, array $data)
    {
        /** @var SubscriberInterface $subscriber */
        foreach ($this->getSubscribers() as $subscriber) {
            $subscriber->handleEvent($id, $data);
        }
    }

    /**
     * Returns an array representation of this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function toArray(array $options = [])
    {
        $childrenIds = [];

        /** @var NodeInterface $child */
        foreach ($this->getChildren() as $child) {
            $childrenIds[] = $child->getId();
        }

        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'metadata'          => $this->getMetadata()->getData(),
            'parentId'          => $this->getParent() ?
                $this->getParent()->getId() :
                null,
            'childrenIds'       => $childrenIds
        ];
    }


}