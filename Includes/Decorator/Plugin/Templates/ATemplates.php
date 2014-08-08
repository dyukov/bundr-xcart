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

namespace Includes\Decorator\Plugin\Templates;

/**
 * ATemplates
 *
 * @package XLite
 */
abstract class ATemplates extends \Includes\Decorator\Plugin\APlugin
{
    /**
     * Predefined tag names
     */
    const TAG_LIST_CHILD = 'listchild';
    const TAG_INHERITED_LIST_CHILD = 'inheritedlistchild';

    /**
     * List of .tpl files
     *
     * @var array
     */
    protected static $annotatedTemplates;

    /**
     * List of .tpl files with @InheritedListChild tag
     *
     * @var array
     */
    protected static $inheritedTemplates;

    /**
     * List of zones
     *
     * @var array
     */
    protected static $zones = array(
        'console' => \XLite\Model\ViewList::INTERFACE_CONSOLE,
        'admin'   => \XLite\Model\ViewList::INTERFACE_ADMIN,
        'mail'    => \XLite\Model\ViewList::INTERFACE_MAIL,
    );

    /**
     * Return templates list
     *
     * @return array
     */
    protected function getAnnotatedTemplates()
    {
        if (!isset(static::$annotatedTemplates)) {
            static::$annotatedTemplates = array();
            static::$inheritedTemplates = array();

            foreach ($this->getTemplateFileIterator()->getIterator() as $path => $data) {

                $data = \Includes\Decorator\Utils\Operator::getTags(
                    \Includes\Utils\FileManager::read($path, true),
                    array(static::TAG_LIST_CHILD, static::TAG_INHERITED_LIST_CHILD)
                );

                if (isset($data[static::TAG_LIST_CHILD])) {
                    $this->addTags($data[static::TAG_LIST_CHILD], $path);
                }

                if (isset($data[static::TAG_INHERITED_LIST_CHILD])) {
                    static::$inheritedTemplates[] = $path;
                }
            }
        }

        return static::$annotatedTemplates;
    }

    /**
     * Get iterator for template files
     *
     * @return \Includes\Utils\FileFilter
     */
    protected function getTemplateFileIterator()
    {
        return new \Includes\Utils\FileFilter(
            LC_DIR_SKINS,
            \Includes\Utils\ModulesManager::getPathPatternForTemplates()
        );
    }

    /**
     * Parse template and add tags to the list
     *
     * @param array  $data Tags data
     * @param string $path Template file path
     *
     * @return array
     */
    protected function addTags(array $data, $path)
    {
        $base = \Includes\Utils\FileManager::getRelativePath($path, LC_DIR_SKINS);

        foreach ($data as $tags) {
            $skin = \Includes\Utils\ArrayManager::getIndex(explode(LC_DS, $base), 0, true);
            $zone = array_search($skin, static::$zones) ?: \XLite\Model\ViewList::INTERFACE_CUSTOMER;
            $template = substr($base, strpos($base, LC_DS) + ('common' == $skin ? 1 : 4));

            static::$annotatedTemplates[] = array('tpl' => $template, 'zone' => $zone, 'path' => $path) + $tags;
        }
    }
}
