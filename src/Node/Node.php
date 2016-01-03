<?php
/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Node;

use IronEdge\Component\CommonUtils\Options\OptionsInterface;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Node implements NodeInterface, OptionsInterface
{
    use NodeTrait;

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