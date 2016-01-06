<?php
/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Test\Integration;
use IronEdge\Component\Graphs\Export\Utils;
use IronEdge\Component\Graphs\Service;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class ServiceTest extends AbstractTestCase
{
    /**
     * Field _utils.
     *
     * @var Utils
     */
    private $_utils;


    public function setUp()
    {
        $this->cleanUp();
    }

    public function tearDown()
    {
        $this->cleanUp();
    }


    public function test_export_graphviz()
    {
        $this->skipIfGraphvizIsNotInstalled();

        $service = $this->createService();
        $graph = $service->createGraph($this->getExampleGraphData());

        $response = $service->export($graph, ['writer' => 'graphviz', 'path' => '/var/www/html/graphviz.png']);

        $this->assertTrue(is_file($response->getPath()));
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\InvalidWriterTypeException
     */
    public function test_export_throwExceptionIfWriterDoesNotExist()
    {
        $service = $this->createService();
        $graph = $service->createGraph($this->getExampleGraphData());

        $service->export($graph, ['writer' => 'iDontExist']);
    }

    public function test_generateRandomFilePath_shouldReturnAPathToANonExistentFile()
    {
        $service = $this->createService();

        $this->assertFalse(is_file($service->generateRandomFilePath()));
    }


    // Helper methods

    public function cleanUp()
    {
        $glob = glob($this->getTmpDir().'/*.png*');

        foreach ($glob as $file) {
            @unlink($file);
        }
    }

    public function getExampleGraphData()
    {
        return [
            'id'            => 'myGraph',
            'nodes'         => [
                [
                    'id'        => 'node1',
                    'isRootNode'    => true,
                    'metadata'      => [
                        'graphviz'      => [
                            'relationsAttributes'       => [
                                'node2'                     => [
                                    'label'                     => '"Yeah Baby!"'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'id'        => 'node2',
                    'parentId'  => 'node1'
                ],
                [
                    'id'            => 'node3',
                    'parentId'      => 'node2',
                    'nodeType'      => 'graph',
                    'nodes'             => [
                        [
                            'id'            => 'node4',
                            'childrenIds'   => [
                                'node5'
                            ],
                            'isRootNode'    => true
                        ],
                        [
                            'id'            => 'node5',
                            'metadata'  => [
                                'graphviz'      => [
                                    'nodeAttributes'    => [
                                        'shape'             => 'circle',
                                        'style'             => 'filled',
                                        'color'             => '".7 .3 1.0"'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'metadata'  => [
                        'graphviz'      => [
                            'nodeAttributes'    => [
                                'shape'             => 'circle',
                                'style'             => 'filled',
                                'color'             => '".7 .3 1.0"'
                            ]
                        ]
                    ]
                ]
            ],
            'metadata'          => [
                'graphviz'          => [
                    'nodeAttributes'    => [
                        'size'              => '"4,4"'
                    ]
                ]
            ]
        ];
    }

    public function createService()
    {
        return new Service();
    }

    public function skipIfGraphvizIsNotInstalled()
    {
        if (!$this->getUtils()->isDotInstalled()) {
            $this->markTestSkipped('To be able to run these tests, you must install package "graphviz".');
        }
    }

    public function getUtils()
    {
        if ($this->_utils === null) {
            $this->_utils = new Utils();
        }

        return $this->_utils;
    }
}