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

use IronEdge\Component\CommonUtils\Exception\CommandException;
use IronEdge\Component\CommonUtils\System\SystemService;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
class Utils
{
    /**
     * Field _systemService.
     *
     * @var SystemService
     */
    private $_systemService;


    /**
     * Utils constructor.
     *
     * @param SystemService $systemService - System service.
     */
    public function __construct(SystemService $systemService = null)
    {
        $this->setSystemService($systemService ? $systemService : new SystemService());
    }

    /**
     * Returns the value of field _systemService.
     *
     * @return SystemService
     */
    public function getSystemService(): SystemService
    {
        return $this->_systemService;
    }

    /**
     * Sets the value of field systemService.
     *
     * @param SystemService $systemService - systemService.
     *
     * @return Utils
     */
    public function setSystemService(SystemService $systemService): Utils
    {
        $this->_systemService = $systemService;

        return $this;
    }

    /**
     * Returns true if "dot" binary is installed, or false otherwise.
     *
     * @throws CommandException
     *
     * @return bool
     */
    public function isDotInstalled()
    {
        try {
            $this->getSystemService()->executeCommand('which', ['dot']);

            return true;
        } catch (CommandException $e) {
            return false;
        }
    }
}