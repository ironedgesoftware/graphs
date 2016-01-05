<?php
/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Exception;

use IronEdge\Component\Graphs\Node\NodeInterface;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class ParentTypeNotSupportedException extends BaseException
{
    public static function create(NodeInterface $child, NodeInterface $parent)
    {
        return new self(
            'Node of type "'.get_class($child).'" (ID "'.$child->getId().'") '.
            'does not support a parent of type "'.get_class($parent).'" (ID: '.$parent->getId().').'
        );
    }
}