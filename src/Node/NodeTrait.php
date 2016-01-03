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

use IronEdge\Component\CommonUtils\Options\OptionsTrait;

/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
trait NodeTrait
{
    use OptionsTrait;

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
     * @return NodeInterface
     */
    public function setId(string $id): NodeInterface
    {
        $this->_id = $id;

        return $this;
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
     * @return NodeInterface
     */
    public function setParent(NodeInterface $parent): NodeInterface
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
     * @return NodeInterface
     */
    public function addChild(NodeInterface $child): NodeInterface
    {
        $this->_children[$child->getId()] = $child;

        return $this;
    }
}