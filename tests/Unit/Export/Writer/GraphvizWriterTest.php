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

namespace IronEdge\Component\Graphs\Test\Unit\Export\Graph;

use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Export\Writer\GraphvizWriter;
use IronEdge\Component\Graphs\Graph\Graph;
use IronEdge\Component\Graphs\Test\Unit\AbstractTestCase;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class GraphvizWriterTest extends AbstractTestCase
{
    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\ExportException
     */
    public function test_write_ifDotIsNotAvailableThenThrowException()
    {
        $writer = $this->createInstance();

        $writer->getUtils()
            ->expects($this->once())
            ->method('isDotInstalled')
            ->will($this->returnValue(false));

        $writer->write([], []);
    }

    public function test_write_ifGraphIsNotReceivedThenThrowException()
    {
        $validationException = new ValidationException();

        $this->setExpectedExceptionRegExp(
            get_class($validationException),
            '/Data attribute \"graph\" must be an instance of/'
        );

        $writer = $this->createInstance();

        $writer->getUtils()
            ->expects($this->once())
            ->method('isDotInstalled')
            ->will($this->returnValue(true));

        $writer->write([], []);
    }

    public function test_write_ifPathIsNotReceivedThenThrowException()
    {
        $validationException = new ValidationException();

        $this->setExpectedExceptionRegExp(
            get_class($validationException),
            '/you must set option \"path\" with a string/'
        );

        $writer = $this->createInstance();

        $writer->getUtils()
            ->expects($this->once())
            ->method('isDotInstalled')
            ->will($this->returnValue(true));
        $graph = new Graph(['id' => 'test']);

        $writer->write(['graph' => $graph], []);
    }



    // Helper Methods

    /**
     * @param array $data
     * @param array $options
     *
     * @return Graph
     */
    protected function createInstance(): GraphvizWriter
    {
        $writer = new GraphvizWriter();

        $writer->setUtils($this->getUtilsMock());

        return $writer;
    }

    protected function getUtilsMock()
    {
        $mock = $this->getMock('\IronEdge\Component\Graphs\Export\Utils', ['isDotInstalled']);

        return $mock;
    }
}