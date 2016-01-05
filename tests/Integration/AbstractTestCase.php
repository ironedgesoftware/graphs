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

namespace IronEdge\Component\Graphs\Test\Integration;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    public function getTmpDir()
    {
        $path = realpath(__DIR__.'/../tmp');

        if (!$path) {
            throw new \RuntimeException('Couldn\'t determine path to "tmp" directory inside "tests" path.');
        }

        return $path;
    }
}