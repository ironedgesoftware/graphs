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
class InvalidWriterTypeException extends BaseException
{
    public static function create(string $writerType)
    {
        return new self(
            'Writer "'.$writerType.'" is not supported.'
        );
    }
}