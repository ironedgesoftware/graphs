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
use IronEdge\Component\Graphs\Exception\ChildTypeNotSupportedException;
use IronEdge\Component\Graphs\Exception\ParentTypeNotSupportedException;
use IronEdge\Component\Graphs\Exception\ValidationException;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Node implements NodeInterface
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
     * Node constructor.
     *
     * @param array $data - Node's data.
     */
    public function __construct(array $data = [])
    {
        $this->initialize($data);
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
            $this->_metadata = new Data($this->getDefaultMetadata());
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
        $this->getMetadata()->setData($metadata);

        return $this;
    }

    /**
     * Sets a metadata attribute.
     *
     * @param string $attr    - Attribute.
     * @param mixed  $value   - Value.
     * @param array  $options - Options.
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
     * @param string $attr         - Attribute.
     * @param mixed  $defaultValue - Default value.
     * @param array  $options      - Options.
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
     * @return NodeInterface
     */
    public function getParent(): NodeInterface
    {
        return $this->_parent;
    }

    /**
     * Sets the value of field parent.
     *
     * @param NodeInterface $parent - parent.
     *
     * @throws ParentTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function setParent(NodeInterface $parent): NodeInterface
    {
        if (!$this->supportsParent($parent)) {
            throw ParentTypeNotSupportedException::create($this, $parent);
        }

        $this->_parent = $parent;

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
     *
     * @throws ChildTypeNotSupportedException
     *
     * @return NodeInterface
     */
    public function addChild(NodeInterface $child): NodeInterface
    {
        if (!$this->supportsChild($child)) {
            throw ChildTypeNotSupportedException::create($this, $child);
        }

        $this->_children[$child->getId()] = $child;

        return $this;
    }

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

        return $this;
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
     * Returns default metadata.
     *
     * @return array
     */
    public function getDefaultMetadata()
    {
        return [];
    }
}