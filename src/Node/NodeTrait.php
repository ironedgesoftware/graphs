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

namespace IronEdge\Component\Graphs\Node;

/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
trait NodeTrait
{
    /**
     * Field _id.
     *
     * @var string
     */
    private $_id;

    /**
     * Field _parent.
     *
     * @var NodeTrait
     */
    private $_parent;

    /**
     * Field _children.
     *
     * @var array
     */
    private $_children = [];

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
     * Returns the value of field _parent.
     *
     * @return self
     */
    public function getParent(): self
    {
        return $this->_parent;
    }

    /**
     * Sets the value of field parent.
     *
     * @param NodeTrait $parent - parent.
     *
     * @return self
     */
    public function setParent(NodeTrait $parent): self
    {
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
     * @return self
     */
    public function setChildren(array $children): self
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
     * @param NodeTrait $child - Child.
     *
     * @return self
     */
    public function addChild(NodeTrait $child): self
    {
        $this->_children[$child->getId()] = $child;

        return $this;
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
}