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
 * @copyright Copyright (c) 2011-2014 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace Includes\Decorator\Plugin\Templates\Plugin\Patcher;

/**
 * Main 
 *
 */
class Main extends \Includes\Decorator\Plugin\Templates\Plugin\APlugin
{
    /**
     * Interface for so called "patcher" classes
     */
    const INTERFACE_PATCHER = '\XLite\Base\IPatcher';

    /**
     * List of pather classes
     *
     * @var array
     */
    protected $patchers;

    /**
     * Execute certain hook handler
     *
     * @return void
     */
    public function executeHookHandler()
    {
        // Truncate old
        $this->clearAll();

        // Save pathes info in DB
        $this->collectPatches();
    }

    /**
     * Callback to collect patchers
     *
     * @param \Includes\Decorator\DataStructure\Graph\Classes $node Current node
     *
     * @return void
     */
    public function checkClassForPatcherInterface(\Includes\Decorator\DataStructure\Graph\Classes $node)
    {
        if ($node->isImplements(static::INTERFACE_PATCHER) && !$node->getReflection()->isAbstract) {
            $this->patchers[] = $node;
        }
    }

    /**
     * Remove existing lists from database
     *
     * @return void
     */
    protected function clearAll()
    {
        \XLite\Core\Database::getRepo('\XLite\Model\TemplatePatch')->clearAll();
    }

    /**
     * Save pathes info in DB
     *
     * @return void
     */
    protected function collectPatches()
    {
        $data = array();

        // List of all "patcher" classes
        foreach ($this->getPatchers() as $node) {
            $class = $node->getClass();

            // List of patches defined in class
            foreach (call_user_func(array($class, 'getPatches')) as $patch) {

                if (empty($patch[$class::PATCHER_CELL_TYPE])) {
                    $patch[$class::PATCHER_CELL_TYPE] = 'custom';
                }

                // Prepare model class properties
                $data[] = new \XLite\Model\TemplatePatch(
                    $this->getCommonData($patch, $class) 
                    + $this->{'get' . ucfirst($patch[$class::PATCHER_CELL_TYPE]) . 'Data'}($patch, $class)
                );
            }
        }

        \XLite\Core\Database::getRepo('\XLite\Model\TemplatePatch')->insertInBatch($data);
    }

    /**
     * Return list of the "patcher" classes
     *
     * @return array
     */
    protected function getPatchers()
    {
        if (!isset($this->patchers)) {
            $this->patchers = array();

            static::getClassesTree()->walkThrough(array($this, 'checkClassForPatcherInterface'));
        }

        return $this->patchers;
    }

    /**
     * Prepare common properties
     *
     * @param array  $data  Data describe the patch
     * @param string $class Patcher class
     *
     * @return array
     */
    protected function getCommonData(array $data, $class)
    {
        $parts = explode(':', $data[$class::PATCHER_CELL_TPL], 3);
        if (1 == count($parts)) {
            $parts = array('customer', '', $parts[0]);

        } elseif (2 == count($parts)) {
            $parts = array($parts[0], '', $parts[1]);
        }

        return array('patch_type' => $data[$class::PATCHER_CELL_TYPE])
            + array_combine(array('zone', 'lang', 'tpl'), $parts);
    }

    /**
     * Prepare properties for certain patch type
     *
     * @param array  $data  Data describe the patch
     * @param string $class Patcher class
     *
     * @return array
     */
    protected function getXpathData(array $data, $class)
    {
        return array(
            'xpath_query'       => $data[$class::XPATH_CELL_QUERY],
            'xpath_insert_type' => $data[$class::XPATH_CELL_INSERT_TYPE],
            'xpath_block'       => $data[$class::XPATH_CELL_BLOCK],
        );
    }

    /**
     * Prepare properties for certain patch type
     *
     * @param array  $data  Data describe the patch
     * @param string $class Patcher class
     *
     * @return array
     */
    protected function getRegexpData(array $data, $class)
    {
        return array(
            'regexp_pattern' => $data[$class::REGEXP_CELL_PATTERN],
            'regexp_replace' => $data[$class::REGEXP_CELL_REPLACE],
        );
    }

    /**
     * Prepare properties for certain patch type
     *
     * @param array $data  Data describe the patch
     * @param string $class Patcher class
     *
     * @return array
     */
    protected function getCustomData(array $data, $class)
    {
        $parts = explode('::', $data[$class::CUSTOM_CELL_CALLBACK], 2);
        if (1 == count($parts)) {
            $parts = array($class, $parts[0]);
        }

        return array(
            'custom_callback' => implode('::', $parts),
        );
    }
}
