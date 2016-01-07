<?php
/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs;

use IronEdge\Component\Config\Config;
use IronEdge\Component\Graphs\Exception\InvalidWriterTypeException;
use IronEdge\Component\Graphs\Export\Response;
use IronEdge\Component\Graphs\Export\Writer\GraphvizWriter;
use IronEdge\Component\Graphs\Node\Node;
use IronEdge\Component\Graphs\Node\NodeInterface;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Service
{
    /**
     * Creates a graph instance.
     *
     * @param array $data    - Graph data.
     * @param array $options - Options.
     *
     * @return Node
     */
    public function createGraph(array $data, array $options = [])
    {
        $graph = new Node($data, $options);

        return $graph;
    }

    /**
     * Exports a graph to a graphviz image.
     *
     * @param NodeInterface $node    - Graph.
     * @param array         $options - Options.
     *
     * @throws InvalidWriterTypeException
     *
     * @return Response
     */
    public function export(NodeInterface $node, array $options = [])
    {
        $options = array_replace_recursive(
            [
                'writer'            => 'graphviz'
            ],
            $options
        );

        if (is_string($options['writer'])) {
            switch ($options['writer']) {
                case 'graphviz':
                    $options['path'] = $options['path'] ?? $this->generateRandomFilePath();
                    $options['writer'] = new GraphvizWriter();

                    break;
                default:
                    throw InvalidWriterTypeException::create($options['writer']);
            }
        }

        $config = new Config(
            [
                'node'              => $node
            ],
            [
                'writer'            => $options['writer']
            ]
        );

        $config->save(
            [
                'writerOptions'        => $options
            ]
        );

        $response = new Response();

        $response->setPath($options['path']);

        return $response;
    }

    /**
     * Returns a random file path to use for a new file.
     *
     * @return string
     */
    public function generateRandomFilePath()
    {
        do {
            $file = sys_get_temp_dir().
                '/graphviz-'.sha1(uniqid('graphviz-image-', true).time().rand(0, 9999)).'.png';
        } while (is_file($file));

        return $file;
    }
}