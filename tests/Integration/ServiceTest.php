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
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Export\Utils;
use IronEdge\Component\Graphs\Node\Node;
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

    /**
     * @dataProvider invalidCreateDataProvider
     */
    public function test_create_throwExceptionIfDataIsInvalid(array $data, $exceptionMessageRegex)
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            $exceptionMessageRegex
        );

        $this->createService()->create($data);
    }

    public function test_createNodeInstance_ifNoNodeInstanceIsReturnedThenThrowException()
    {
        $this->setExpectedExceptionRegExp(
            get_class(new ValidationException()),
            '/Node Factory must return an instance of NodeInterface./'
        );

        $service = $this->createService();
        $service->setNodeFactory(function() { return new \DateTime(); });

        $service->create(['id' => 'test']);
    }

    public function test_export_graphviz()
    {
        $this->skipIfGraphvizIsNotInstalled();

        $service = $this->createService();
        $graph = $service->create($this->getExampleGraphData());

        // Add an additional parent
        $node = new Node(['id' => 'additionalParent']);

        $graph->getNode('node2')->addParent($node);

        $graphvizImagePathEnv = getenv('IRONEDGE_TEST_GRAPHVIZ_PATH');
        $graphvizImagePath = $graphvizImagePathEnv ?
            $graphvizImagePathEnv : $this->getTmpDir().'/graphviz.png';

        $response = $service->export($graph, ['writer' => 'graphviz', 'path' => $graphvizImagePath]);

        $this->assertTrue(is_file($response->getPath()));
    }

    /**
     * @expectedException \IronEdge\Component\Graphs\Exception\InvalidWriterTypeException
     */
    public function test_export_throwExceptionIfWriterDoesNotExist()
    {
        $service = $this->createService();
        $graph = $service->create($this->getExampleGraphData());

        $service->export($graph, ['writer' => 'iDontExist']);
    }

    public function test_generateRandomFilePath_shouldReturnAPathToANonExistentFile()
    {
        $service = $this->createService();

        $this->assertFalse(is_file($service->generateRandomFilePath()));
    }



    // Data Providers

    public function invalidCreateDataProvider()
    {
        return [
            [
                [
                    'id'        => 'test',
                    'children'  => 'invalidValue'
                ],
                '/Field \"children\" must be an array\./'
            ],
            [
                [
                    'id'        => 'test',
                    'children'  => [
                        'invalidValue'
                    ]
                ],
                '/Field \"children\" must be an array of arrays\./'
            ]
        ];
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
            'children'      => [
                [
                    'id'        => 'node1',
                    'children'  => [
                        [
                            'id'        => 'node2',
                            'children'  => [
                                [
                                    'id'            => 'graph1',
                                    'nodeType'      => 'graph',
                                    'children'      => [
                                        [
                                            'id'            => 'node3',
                                            'children'      => [
                                                [
                                                    'id'            => 'node4',
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
                            ]
                        ]
                    ],
                    'metadata'      => [
                        'graphviz'      => [
                            'relationsAttributes'       => [
                                'node2'                     => [
                                    'label'                     => '"Testing Label"'
                                ]
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