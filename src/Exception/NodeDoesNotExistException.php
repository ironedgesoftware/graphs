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


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class NodeDoesNotExistException extends BaseException
{
    public static function create($id)
    {
        return new self('Node with ID "'.$id.'" does not exist on this graph.');
    }
}