<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * X-Cart
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the software license agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.x-cart.com/license-agreement.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@x-cart.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not modify this file if you wish to upgrade X-Cart to newer versions
 * in the future. If you wish to customize X-Cart for your needs please
 * refer to http://www.x-cart.com/ for more information.
 *
 * @category  X-Cart 5
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2013 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace Includes\Decorator\DataStructure\Graph;

/**
 * Modules 
 *
 */
class Modules extends \Includes\DataStructure\Graph
{
    /**
     * List of critical path legths for all child nodes
     *
     * @var array
     */
    protected $criticalPaths;

    // {{{ Getters and setters

    /**
     * Alias
     *
     * @return string
     */
    public function getActualName()
    {
        return $this->getKey();
    }

    /**
     * Return module dependencies list
     *
     * @return array
     */
    public function getDependencies()
    {
        return \Includes\Utils\ModulesManager::callModuleMethod($this->getActualName(), 'getDependencies');
    }

    /**
     * Return list of mutually exclusive modules
     *
     * @return array
     */
    public function getMutualModulesList()
    {
        return \Includes\Utils\ModulesManager::callModuleMethod($this->getActualName(), 'getMutualModulesList');
    }

    // }}}

    // {{{ Getters and setters

    /**
     * Method to get critical path length for a node
     *
     * @param string $module Module actual name
     *
     * @return integer
     */
    public function getCriticalPath($module)
    {
        if (!isset($this->criticalPaths)) {
            $this->criticalPaths = $this->calculateCriticalPathLengths();
        }

        return \Includes\Utils\ArrayManager::getIndex($this->criticalPaths, $module);
    }

    /**
     * Calculate critical path lengths
     *
     * @return void
     */
    protected function calculateCriticalPathLengths($length = 1)
    {
        $result = array();

        foreach ($this->getChildren() as $child) {

            // Critical path legth is equal to the current level
            $result[$child->getActualName()] = $length;

            // Recursive call for the next level nodes
            $result += $child->{__FUNCTION__}($length + 1);
        }

        return $result;
    }

    // }}}
}
