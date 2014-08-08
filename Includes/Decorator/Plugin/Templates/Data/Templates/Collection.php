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

namespace Includes\Decorator\Plugin\Templates\Data\Templates;

/**
 * Collection
 *
 * @package XLite
 */
class Collection
{
    /**
     * Templates list cache
     *
     * @var array
     */
    protected $list = array();


    /**
     * Get iterator for class files
     *
     * @return \Includes\Utils\FileFilter
     */
    protected function getFileIterator()
    {
        return new \Includes\Utils\FileFilter(
            LC_DIR_SKINS,
            \Includes\Utils\ModulesManager::getPathPatternForTemplates()
        );
    }

    /**
     * Check and prepare current element data
     *
     * @param \SplFileInfo $fileInfo file descriptor
     *
     * @return array
     */
    protected function prepareNodeData($fileinfo)
    {
        return \Includes\Decorator\Plugin\Templates\Utils\Parser::parse($fileinfo);
    }

    /**
     * Create new node
     *
     * @param array $node node data
     *
     * @return \Includes\Decorator\Plugin\Templates\Data\Templates\Node
     */
    protected function createNode(array $data = array())
    {
        return new \Includes\Decorator\Plugin\Templates\Data\Templates\Node($data);
    }

    /**
     * Walk over the skins and create templates list
     *
     * @param \Includes\Utils\FileFilter\FilterIterator $iterator FS iterator
     *
     * @return void
     */
    protected function create(\Includes\Utils\FileFilter\FilterIterator $iterator)
    {
        foreach ($iterator as $data) {

            // Check and prepare current element data
            if ($data = $this->prepareNodeData($data)) {

                // Add node to the list
                $this->list[] = $this->createNode($data);
            }
        }
    }


    /**
     * Find nodes using a callback function
     *
     * @param mixed $callback callback to execute
     *
     * @return array
     */
    public function findByCallback($callback)
    {
        return array_filter($this->getList(), $callback);
    }

    /**
     * Return templates list
     *
     * @return array
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        // Walk through the .tpl files and collect info
        $this->create($this->getFileIterator()->getIterator());
    }
}
