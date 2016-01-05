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
class ChildDoesNotExistException extends BaseException
{
    public static function create($id, $childId)
    {
        return new self('Node with ID "'.$id.'" has no child with ID "'.$childId.'".');
    }
}