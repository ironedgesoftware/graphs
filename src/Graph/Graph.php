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

namespace IronEdge\Component\Graphs\Graph;
use IronEdge\Component\Graphs\Node\Node;
use IronEdge\Component\Graphs\Node\NodeTrait;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Graph
{
    use GraphTrait;


    /**
     * Creates an instance of a node.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @return NodeTrait
     */
    public function createNodeInstance(array $data, array $options = []): NodeTrait
    {
        return new Node();
    }


}