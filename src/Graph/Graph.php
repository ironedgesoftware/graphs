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
use IronEdge\Component\Graphs\Node\NodeInterface;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Graph implements GraphInterface
{
    use GraphTrait;


    /**
     * Creates an instance of a node.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @return NodeInterface
     */
    public function createNodeInstance(array $data, array $options = []): NodeInterface
    {
        return new Node();
    }

    /**
     * Default options.
     *
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return [];
    }


}