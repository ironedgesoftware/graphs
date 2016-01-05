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
class GraphMustHaveOnlyOneChildException extends BaseException
{
    public static function create(string $graphId, string $currentChildId, string $secondChildId)
    {
        return new self(
            'Graph ID "'.$graphId.'" must have only one child ID. Current child ID is "'.$currentChildId.'". '.
            'Cannot add another child with ID "'.$secondChildId.'".'
        );
    }
}