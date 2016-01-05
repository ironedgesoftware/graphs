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
class GraphCantHaveTwoRootNodesException extends BaseException
{
    public static function create(string $graphId, string $rootId, string $secondRootId)
    {
        return new self(
            'Graph ID "'.$graphId.'" already has a root with ID "'.$rootId.'".'.
            'Can\'t set another root with ID "'.$secondRootId.'".'
        );
    }
}