<?php
/*
 * This file is part of the graphs package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Export\Writer;

use IronEdge\Component\CommonUtils\System\SystemService;
use IronEdge\Component\Config\Writer\WriterInterface;
use IronEdge\Component\Graphs\Exception\ExportException;
use IronEdge\Component\Graphs\Exception\ValidationException;
use IronEdge\Component\Graphs\Export\Utils;
use IronEdge\Component\Graphs\Node\NodeInterface;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class GraphvizWriter implements WriterInterface
{
    /**
     * Field _utils.
     *
     * @var Utils
     */
    private $_utils;

    /**
     * Field _systemService.
     *
     * @var SystemService
     */
    private $_systemService;


    /**
     * GraphvizWriter constructor.
     *
     * @param Utils|null         $utils         - Graphviz Utils.
     * @param SystemService|null $systemService - System Service.
     */
    public function __construct(Utils $utils = null, SystemService $systemService = null)
    {
        $this->setUtils($utils);
        $this->setSystemService($systemService);
    }

    /**
     * Returns the value of field _utils.
     *
     * @return Utils
     */
    public function getUtils(): Utils
    {
        if ($this->_utils === null) {
            $this->_utils = new Utils($this->getSystemService());
        }

        return $this->_utils;
    }

    /**
     * Sets the value of field utils.
     *
     * @param Utils $utils - utils.
     *
     * @return self
     */
    public function setUtils($utils = null): self
    {
        $this->_utils = $utils;

        return $this;
    }

    /**
     * Returns the instance of the system service.
     *
     * @return SystemService
     */
    public function getSystemService()
    {
        if ($this->_systemService === null) {
            $this->_systemService = new SystemService();
        }

        return $this->_systemService;
    }

    /**
     * Sets the system service instance.
     *
     * @param SystemService $systemService - System service.
     *
     * @return self
     */
    public function setSystemService($systemService = null): self
    {
        $this->_systemService = $systemService;

        return $this;
    }

    /**
     * Writes the data into a graphviz image.
     *
     * @param array $data    - Data.
     * @param array $options - Options.
     *
     * @throws ValidationException
     * @throws ExportException
     *
     * @return void
     */
    public function write(array $data, array $options)
    {
        if (!$this->getUtils()->isDotInstalled()) {
            throw ExportException::create(
                'Can\'t export to a Graphviz image because "dot" binary is not available. Verify that the ' .
                '"graphviz" package is installed on your system.'
            );
        }

        if (!isset($data['node']) || !($data['node'] instanceof NodeInterface)) {
            throw ValidationException::create(
                'Data attribute "node" must be an instance of IronEdge\Component\Graphs\Node\NodeInterface.'
            );
        }

        if (!isset($options['path']) || !is_string($options['path']) || $options['path'] === '') {
            throw ValidationException::create(
                'To be able to export this graph to a graphviz image, you must set option "path" with a string ' .
                'with the path to the target file.'
            );
        }

        /** @var NodeInterface $graph */
        $graph = $data['node'];
        $file = $options['path'];

        $graphvizCode = 'digraph ' . $graph->getId() . ' {' . PHP_EOL;
        $graphAttributes = $graph->getMetadataAttr('graphviz.nodeAttributes', []);

        if ($graphAttributes) {
            foreach ($graphAttributes as $k => $v) {
                $graphvizCode .= '  ' . $k . '=' . $v . ';' . PHP_EOL;
            }

            $graphvizCode .= PHP_EOL;
        }

        $graphvizCode .= $this->generateNodeCode($graph);

        $graphvizCode .= '}';

        $this->generateGraphvizOutputFile($graphvizCode, $file, ['targetType' => 'png']);
    }

    public function generateNodeCode(NodeInterface $node, array &$processedNodes = [])
    {
        if (isset($processedNodes[$node->getId()])) {
            return '';
        }

        $graphvizCode = '';

        $graphvizCode .= '  '.$this->generateNodeAttributesCode($node).PHP_EOL.PHP_EOL;
        $graphvizCode .= '  '.$this->generateNodeRelationsCode($node).PHP_EOL.PHP_EOL;

        $parents = $node->getParents();
        $children = $node->getChildren();

        $processedNodes[$node->getId()] = true;

        /** @var NodeInterface $n */
        foreach ($parents as $n) {
            if (!isset($processedNodes[$n->getId()])) {
                $graphvizCode .= $this->generateNodeCode($n, $processedNodes);
            }
        }

        /** @var NodeInterface $n */
        foreach ($children as $n) {
            if (!isset($processedNodes[$n->getId()])) {
                $graphvizCode .= $this->generateNodeCode($n, $processedNodes);
            }
        }

        return $graphvizCode;
    }

    public function generateNodeAttributesCode(NodeInterface $node)
    {
        $code = $node->getId();
        $attrs = $node->getMetadataAttr('graphviz.nodeAttributes', []);

        if ($attrs) {
            $code .= ' [';

            foreach ($attrs as $attr => $value) {
                $code.= $attr.'='.$value.',';
            }

            $code = substr($code, 0, -1).']';
        }

        $code .= ';';

        return $code;
    }

    public function generateNodeRelationsCode(NodeInterface $node)
    {
        $children = $node->getChildren();

        if (!$children) {
            return '';
        }

        $code = '';

        /** @var NodeInterface $child */
        foreach ($children as $child) {
            $code .= '  '.$node->getId().' -> '.$child->getId();
            $relationsAttributes = $node->getMetadataAttr('graphviz.relationsAttributes.'.$child->getId(), []);

            if ($relationsAttributes) {
                $code .= ' [';

                foreach ($relationsAttributes as $k => $v) {
                    $code .= $k.'='.$v.',';
                }

                $code = substr($code, 0, -1).']';
            }

            $code .= ';'.PHP_EOL;
        }

        return $code;
    }

    /**
     * Exports a graphviz code to a file.
     *
     * @param string $graphvizCode    - Graphviz code.
     * @param string $targetFile      - Target file.
     * @param array  $graphvizOptions - Graphviz options.
     *
     * @throws ExportException
     *
     * @return GraphvizWriter
     */
    protected function generateGraphvizOutputFile(
        string $graphvizCode,
        string $targetFile,
        array $graphvizOptions = []
    ): GraphvizWriter
    {
        $graphvizOptions = array_replace_recursive(
            [
                'targetType'            => 'png'
            ],
            $graphvizOptions
        );
        $tmpFile = $targetFile.'.tmp';

        $this->removeFile($targetFile)
            ->removeFile($tmpFile);

        $this->writeFile($graphvizCode, $tmpFile);

        exec('dot -T'.$graphvizOptions['targetType'].' '.$tmpFile.' -o '.$targetFile, $output, $status);

        if ($status) {
            throw ExportException::create(
                'Couldn\'t generate Graphviz image. Exit Code: '.$status.' - Output: '.print_r($output, true)
            );
        }

        $this->removeFile($tmpFile);

        return $this;
    }

    /**
     * Removes a file.
     *
     * @param string $file - File.
     *
     * @return GraphvizWriter
     */
    protected function removeFile(string $file): GraphvizWriter
    {
        $this->getSystemService()->rm($file);

        return $this;
    }

    /**
     * Writes contents to a file.
     *
     * @param string $contents - File contents.
     * @param string $file     - File.
     *
     * @throws ExportException
     *
     * @return GraphvizWriter
     */
    protected function writeFile(string $contents, string $file): GraphvizWriter
    {
        if (!file_put_contents($file, $contents)) {
            throw ExportException::create(
                'Couldn\'t write to file "'.$file.'".'
            );
        }

        return $this;
    }
}