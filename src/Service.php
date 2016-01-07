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
use IronEdge\Component\Graphs\Exception\ValidationException;
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
     * This factory is a callable used to instantiate nodes.
     *
     * @var callable
     */
    private $_nodeFactory;


    /**
     * Service constructor.
     *
     * @param array $options - Options.
     */
    public function __construct(array $options = [])
    {
        $options = array_replace_recursive(
            [
                'nodeFactory'           => function(array $data, array $options) {
                    return new Node($data, $options);
                }
            ],
            $options
        );

        $this->setNodeFactory($options['nodeFactory']);
    }

    /**
     * Creates a node tree.
     *
     * @param array $data    - Node data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return NodeInterface
     */
    public function create(array $data, array $options = [])
    {
        if (isset($data['children'])) {
            if (!is_array($data['children'])) {
                throw ValidationException::create('Field "children" must be an array.');
            }

            foreach ($data['children'] as $i => $nodeData) {
                if (!is_array($nodeData)) {
                    throw ValidationException::create(
                        'Field "children" must be an array of arrays.'
                    );
                }

                $data['children'][$i] = $this->create($nodeData, $options);
            }
        }

        return $this->createNodeInstance($data, $options);
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

    /**
     * Returns the node factory callable.
     *
     * @return callable
     */
    public function getNodeFactory(): callable
    {
        return $this->_nodeFactory;
    }

    /**
     * Sets the value of field nodeFactory.
     *
     * @param callable $nodeFactory - nodeFactory.
     *
     * @return Service
     */
    public function setNodeFactory(callable $nodeFactory): Service
    {
        $this->_nodeFactory = $nodeFactory;

        return $this;
    }

    /**
     * Creates a Node instance through the node factory.
     *
     * @param array $data    - Node Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     *
     * @return NodeInterface
     */
    protected function createNodeInstance(array $data, array $options = []): NodeInterface
    {
        $factory = $this->getNodeFactory();

        $node = $factory($data, $options);

        if (!is_object($node) || !($node instanceof NodeInterface)) {
            throw ValidationException::create(
                'Node Factory must return an instance of NodeInterface.'
            );
        }

        return $node;
    }
}