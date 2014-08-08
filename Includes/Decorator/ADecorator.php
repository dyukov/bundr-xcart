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

namespace Includes\Decorator;

/**
 * ADecorator
 *
 */
abstract class ADecorator
{
    /**
     * Cache building steps
     */
    const STEP_FIRST  = 1;
    const STEP_SECOND = 2;
    const STEP_THIRD  = 3;
    const STEP_FOURTH = 4;
    const STEP_FIFTH  = 5;
    const STEP_SIX    = 6;
    const STEP_SEVEN  = 7;
    const STEP_EIGHT  = 8;
    const STEP_NINE   = 9;
    const STEP_TEN    = 10;
    const STEP_ELEVEN = 11;

    const LAST_STEP   = self::STEP_ELEVEN;

    /**
     * Current step
     *
     * @var string
     */
    protected static $step;

    /**
     * Classes tree
     *
     * @var \Includes\Decorator\DataStructure\Graph\Classes
     */
    protected static $classesTree;

    /**
     * Modules graph
     *
     * @var \Includes\Decorator\DataStructure\Graph\Modules
     */
    protected static $modulesGraph;

    /**
     * Return classes tree
     *
     * @param boolean $create Flag OPTIONAL
     *
     * @return \Includes\Decorator\DataStructure\Graph\Classes
     */
    public static function getClassesTree($create = true)
    {
        if (!isset(static::$classesTree) && $create) {
            if (\Includes\Utils\FileManager::isFileReadable(static::getClassesHashPath())) {
                $data = unserialize(\Includes\Utils\FileManager::read(static::getClassesHashPath()));
                static::$classesTree = array_pop($data);

            } else {
                static::$classesTree = \Includes\Decorator\Utils\Operator::createClassesTree();
            }
        }

        return static::$classesTree;
    }

    /**
     * Return modules graph
     *
     * @return \Includes\Decorator\DataStructure\Graph\Modules
     */
    public static function getModulesGraph()
    {
        if (!isset(static::$modulesGraph)) {
            static::$modulesGraph = \Includes\Decorator\Utils\Operator::createModulesGraph();
        }

        return static::$modulesGraph;
    }

    /**
     * Return classes repository path
     *
     * @return string
     */
    public static function getClassesDir()
    {
        return (self::STEP_FIRST == static::$step) ? LC_DIR_CLASSES : LC_DIR_CACHE_CLASSES;
    }

    /**
     * Return name of the file with the classes hash
     *
     * @return string
     */
    public static function getClassesHashPath()
    {
        return LC_DIR_COMPILE . 'Classes.php';
    }
}
