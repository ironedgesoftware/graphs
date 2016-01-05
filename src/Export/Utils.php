<?php
/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Export;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Utils
{
    /**
     * Returns true if "dot" binary is installed, or false otherwise.
     *
     * @return bool
     */
    public function isDotInstalled()
    {
        exec('which dot', $output, $status);

        return $status ?
            false :
            true;
    }
}