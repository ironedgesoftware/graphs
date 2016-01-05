<?php
/*
 * This file is part of the graphs package.
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
class Response
{
    /**
     * Field _path.
     *
     * @var string
     */
    private $_path;



    /**
     * Returns the value of field _path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->_path;
    }

    /**
     * Sets the value of field path.
     *
     * @param string $path - path.
     *
     * @return $this
     */
    public function setPath(string $path): Response
    {
        $this->_path = $path;

        return $this;
    }



}