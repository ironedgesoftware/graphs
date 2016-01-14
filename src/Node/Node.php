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
use IronEdge\Component\Graphs\Exception\NodeDoesNotExistException;
use IronEdge\Component\Graphs\Exception\ParentTypeNotSupportedException;
use IronEdge\Component\Graphs\Exception\ValidationException;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Node implements NodeInterface
{
    const TYPE                  = 'node';

    const EVENT_ADD_CHILD       = 'add_child';
    const EVENT_REMOVE_CHILD    = 'remove_child';
    const EVENT_ADD_PARENT      = 'add_parent';
    const EVENT_REMOVE_PARENT   = 'remove_parent';


    /**
     * Node ID.
     *
     * @var string
     */
    private $_id;

    /**
     * Node Name.
     *
     * @var string
     */
    private $_name;

    /**
     * Node Type.
     *
     * @var string
     */
    private $_type = self::TYPE;

    /**
     * Parents of this node.
     *
     * @var array
     */
    private $_parents = [];

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
     * Field _nodes.
     *
     * @var array
     */
    private $_nodes = [];


    /**
     * Node constructor.
     *
     * @param array $data - Node's data.
     * @param array $options - Options.
     */
    public function __construct(array $data = [], array $options = [])
    {
        $this->initialize($data, $options);
        $this->validate($options);
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
     * Getter method for field _type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->_type;
    }

    /**
     * Setter method for field type.
     *
     * @param string $type - type.
     *
     * @return Node
     */
    public function setType(string $type)
    {
        $this->_type = $type;

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
     * Finds parent nodes all the way up until we reach the root node.
     *
     * @param array $filters - Filters.
     * @param array $options - Options.
     *
     * @return array|NodeInterface
     */
    public function findParents(array $filters = [], array $options = [])
    {
        $options = array_replace(
            [
                'returnFirstResult' => false,
                'indexById'         => false
            ],
            $options
        );
        $parents = $this->getAllParents($options);

        if ($parents && $filters) {
            $result = [];

            /** @var NodeInterface $parent */
            foreach ($parents as $i => $parent) {
                foreach ($filters as $f => $v) {
                    $method = 'get' . ucfirst($f);

                    if (!method_exists($parent, $method) || $parent->$method() !== $v) {
                        continue 2;
                    }
                }

                if ($options['indexById']) {
                    $result[$i] = $parent;
                } else {
                    $result[] = $parent;
                }
            }
        } else {
            $result = $parents;
        }

        return $options['returnFirstResult'] && $result ?
            array_values($result)[0] :
            $result;
    }

    /**
     * Returns all parents from this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getAllParents(array $options = []): array
    {
        $options = array_replace(
            [
                'indexById'     => false
            ],
            $options
        );
        $parents = [];

        /** @var NodeInterface $parent */
        foreach ($this->getParents() as $id => $parent) {
            if ($options['indexById']) {
                $parents[$id] = $parent;
            } else {
                $parents[] = $parent;
            }

            $parents = array_merge(
                $parents,
                $parent->getAllParents($options)
            );
        }

        return $parents;
    }

    /**
     * Returns all parents from this node plus this node.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getAllParentsAndCurrentNode(array $options = []): array
    {
        return array_merge(
            $this->getAllParents($options),
            [$this]
        );
    }

    /**
     * Returns the value of field _parent.
     *
     * @param string|null $id - Parent ID.
     *
     * @return NodeInterface|null
     */
    public function getParent(string $id = null)
    {
        if (!$this->_parents || ($id !== null && !isset($this->_parents[$id]))) {
            return null;
        }

        return $id !== null ?
            $this->_parents[$id] :
            $this->_parents[array_rand($this->_parents)];
    }

    /**
     * Returns an array of parents.
     *
     * @return array
     */
    public function getParents(): array
    {
        return $this->_parents;
    }

    /**
     * Sets the value of field parent.
     *
     * @param NodeInterface $parent          - Parent.
     * @param bool          $setParentsChild - Set parent's child.
     *
     * @throws ParentTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function addParent(NodeInterface $parent, bool $setParentsChild = true): NodeInterface
    {
        if (!$this->supportsParent($parent)) {
            throw ParentTypeNotSupportedException::create($this, $parent);
        }

        $this->_parents[$parent->getId()] = $parent;

        if ($setParentsChild) {
            $parent->addChild($this, false);
        }

        $this->notifySubscribers(
            self::EVENT_ADD_PARENT,
            ['oldParents' => $this->_parents, 'newParent' => $parent, 'child' => $this]
        );

        return $this;
    }

    /**
     * Removes a parent from this node.
     *
     * @param string $parentId        - Parent.
     * @param bool   $setParentsChild - Set parent's child.
     *
     * @return NodeInterface
     */
    public function removeParent(string $parentId, bool $setParentsChild = true): NodeInterface
    {
        if (isset($this->_parents[$parentId])) {
            $parent = $this->getParent($parentId);

            $this->notifySubscribers(
                self::EVENT_REMOVE_PARENT,
                ['oldParents' => $this->_parents, 'parentToRemove' => $parent, 'child' => $this]
            );

            if ($setParentsChild) {
                $parent->removeChild($this->getId(), false);
            }

            unset($this->_parents[$parentId]);
        }

        return $this;
    }

    /**
     * Clears all parents of this node.
     *
     * @return NodeInterface
     */
    public function clearParents(): NodeInterface
    {
        /** @var NodeInterface $parent */
        foreach ($this->getParents() as $parent) {
            $this->removeParent($parent->getId());
        }

        return $this;
    }

    /**
     * Returns the value of field _children.
     *
     * @param array $options - Options.
     *
     * @return array
     */
    public function getChildren(array $options = []): array
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
        foreach ($this->getChildren() as $node) {
            $this->removeChild($node->getId());
        }

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
            $child->addParent($this, false);
        }

        /** @var NodeInterface $parent */
        foreach ($this->getAllParentsAndCurrentNode() as $parent) {
            $child->addSubscriber($parent);
        }

        $this->notifySubscribers(
            self::EVENT_ADD_CHILD,
            ['parent' => $this, 'child' => $child]
        );

        return $this;
    }

    /**
     * Removes a child.
     *
     * @param string $childId   - Child ID.
     * @param bool   $setParent - Set parent?
     *
     * @return NodeInterface
     */
    public function removeChild(string $childId, bool $setParent = true): NodeInterface
    {
        if ($this->hasChild($childId)) {
            $child = $this->getChild($childId);

            if ($setParent) {
                $child->removeParent($this->getId(), false);
            }

            unset($this->_children[$childId]);

            $this->notifySubscribers(
                self::EVENT_REMOVE_CHILD,
                ['parent' => $this, 'child' => $child]
            );

            /** @var NodeInterface $parent */
            foreach ($this->getAllParents() as $parent) {
                $child->removeSubscriber($parent->getId());
            }
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
     * Finds children.
     *
     * @param array $filters - Filters.
     * @param array $options - Options.
     *
     * @return array|NodeInterface
     */
    public function findChildren(array $filters = [], array $options = [])
    {
        $options = array_replace(
            [
                'returnFirstResult'     => false
            ],
            $options
        );

        if (empty($filters)) {
            $result = array_values($this->_nodes);
        } else if (count($filters) === 1
            && isset($filters['id'])
        ) {
            $result = $this->hasNode($filters['id']) ?
                [$this->getNode($filters['id'])] :
                [];
        } else {
            $result = [];

            /** @var NodeInterface $child */
            foreach ($this->getNodes() as $child) {
                foreach ($filters as $f => $v) {
                    $method = 'get'.ucfirst($f);

                    if (!method_exists($child, $method) || $child->$method() !== $v) {
                        continue 2;
                    }
                }

                $result[] = $child;
            }
        }

        return $options['returnFirstResult'] && $result ?
            $result[0] :
            $result;
    }

    /**
     * Returns count of children.
     *
     * @return int
     */
    public function countChildren(): int
    {
        return count($this->_children);
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
     * Adds a node to this graph.
     *
     * @param NodeInterface $node - Node.
     *
     * @return NodeInterface
     */
    public function addNode(NodeInterface $node): NodeInterface
    {
        $this->_nodes[$node->getId()] = $node;

        if ($this->getParent()) {
            $this->getParent()->addNode($node);
        }

        return $this;
    }

    /**
     * Removes a node.
     *
     * @param NodeInterface $node - Node.
     *
     * @return NodeInterface
     */
    public function removeNode(NodeInterface $node): NodeInterface
    {
        unset($this->_nodes[$node->getId()]);

        if ($this->getParent()) {
            $this->getParent()->removeNode($node);
        }

        return $this;
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

        if (!preg_match('/^[0-9a-z\_]+$/i', $data['id'])) {
            throw ValidationException::create(
                'Field "id" must have only characters 0-9, a-z, A-Z or "_". Received: '.$data['id']
            );
        }

        $this->setId($data['id']);

        $this->addSubscriber($this);

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

        if (isset($data['parent'])) {
            if (!is_object($data['parent'])
                || !($data['parent'] instanceof NodeInterface)
            ) {
                throw ValidationException::create(
                    'Field "parent" must be an instance of NodeInterface.'
                );
            }

            $this->addParent($data['parent']);
        }

        if (isset($data['children'])) {
            if (!is_array($data['children'])) {
                throw ValidationException::create('Field "children" must be an array.');
            }

            foreach ($data['children'] as $node) {
                if (!is_object($node) || !($node instanceof NodeInterface)) {
                    throw ValidationException::create(
                        'Field "children" must be an array of NodeInterface instances.'
                    );
                }

                $node->addParent($this);

                $this->addChild($node);
            }
        }

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
                'validateMinChildren'               => true,
                'validateMaxChildren'               => true,
                'validateParentMandatory'           => true,
                'validateParentMustNotBeSet'        => true
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
                'minChildren'                   => null,
                'maxChildren'                   => null,
                'parentMandatory'               => false,
                'parentMustNotBeSet'            => false
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
     * Removes a subscriber.
     *
     * @param string|SubscriberInterface $idOrSubscriber - Subscriber or ID.
     *
     * @return NodeInterface
     */
    public function removeSubscriber($idOrSubscriber): NodeInterface
    {
        if (!is_string($idOrSubscriber)
            && (!is_object($idOrSubscriber) || !($idOrSubscriber instanceof SubscriberInterface))
        ) {
            throw new \InvalidArgumentException(
                'Argument "$idOrSubscriber" must be a string or an instance of '.
                'IronEdge\Component\Graph\Event\SubscriberInterface.'
            );
        }

        $id = is_string($idOrSubscriber) ?
            $idOrSubscriber :
            $idOrSubscriber->getId();

        unset($this->_subscribers[$id]);

        return $this;
    }

    /**
     * This method is called when an event is fired.
     *
     * @param string $eventId   - Event ID.
     * @param array  $eventData - Event Data.
     *
     * @return void
     */
    public function handleEvent(string $eventId, array $eventData)
    {
        switch ($eventId) {
            case self::EVENT_ADD_CHILD:
                /** @var NodeInterface $node */
                $node = $eventData['child'];

                $this->addNode($node);

                break;
            case self::EVENT_REMOVE_CHILD:
                /** @var NodeInterface $node */
                $node = $eventData['child'];

                $this->removeNode($node);

                break;
            case self::EVENT_ADD_PARENT:
                /** @var NodeInterface $child */
                $child = $eventData['child'];
                /** @var NodeInterface $newParent */
                $newParent = $eventData['newParent'];
                $nodes = $child->getNodes() + [$child];

                /** @var NodeInterface $n */
                foreach ($nodes as $n) {
                    $n->addSubscriber($newParent);
                    $newParent->addNode($n);
                }

                break;
            case self::EVENT_REMOVE_PARENT:
                /** @var NodeInterface $child */
                $child = $eventData['child'];
                /** @var NodeInterface $oldParent */
                $oldParent = $eventData['parentToRemove'];
                $nodes = $child->getNodes() + [$child];

                /** @var NodeInterface $n */
                foreach ($nodes as $n) {
                    $n->removeSubscriber($oldParent);
                    $oldParent->removeNode($n);
                }

                break;
            default:
                break;
        }
    }

    /**
     * Fires an event. Subscribers gets notified about this event.
     *
     * @param string $eventId   - Event ID.
     * @param array  $eventData - Event Data.
     *
     * @return void
     */
    public function notifySubscribers(string $eventId, array $eventData)
    {
        /** @var SubscriberInterface $subscriber */
        foreach ($this->getSubscribers() as $subscriber) {
            $subscriber->handleEvent($eventId, $eventData);
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
            'type'              => $this->getType(),
            'metadata'          => $this->getMetadata()->getData(),
            'parentId'          => $this->getParent() ?
                $this->getParent()->getId() :
                null,
            'childrenIds'       => $childrenIds
        ];
    }
}