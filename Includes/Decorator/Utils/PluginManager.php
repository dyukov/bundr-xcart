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

namespace Includes\Decorator\Utils;

/**
 * PluginManager 
 *
 */
abstract class PluginManager extends \Includes\Decorator\Utils\AUtils
{
    /**
     * Config file name
     */
    const FILE_INI = 'plugins.ini';

    /**
     * List of registered plugins
     *
     * @var array
     */
    protected static $plugins;

    /**
     * Check and execute hook handlers
     *
     * @param string $hook Hook name
     *
     * @return void
     */
    public static function invokeHook($hook)
    {
        // Get plugins "subscribed" for the hook
        foreach (static::getPlugins($hook) as $plugin => $instance) {

            if (!isset($instance)) {
                $class = '\Includes\Decorator\Plugin\\' . str_replace('_', '\\', $plugin) . '\Main';
                static::$plugins[$plugin] = $instance = new $class();
            }

            // Show message
            \Includes\Decorator\Utils\CacheManager::showStepMessage('Run the "' . $plugin . '" plugin...');

            // Execute plugin main method
            $instance->executeHookHandler();

            // Show memory usage
            \Includes\Decorator\Utils\CacheManager::showStepInfo();
        }
    }

    /**
     * Return list of registered plugins
     *
     * @param string $hook Hook name OPTIONAL
     *
     * @return array
     */
    protected static function getPlugins($hook = null)
    {
        if (!isset(static::$plugins)) {

            // Check config file
            if (\Includes\Utils\FileManager::isFileReadable(static::getConfigFile())) {

                // Iterate over all sections
                foreach (parse_ini_file(static::getConfigFile(), true) as $section => $plugins) {

                    // Set plugins order
                    asort($plugins, SORT_NUMERIC);

                    // Save plugins list
                    static::$plugins[$section] = array_fill_keys(array_keys($plugins), null);
                }

            } else {
                \Includes\ErrorHandler::fireError('Unable to read config file for the Decorator plugins');
            }
        }

        return \Includes\Utils\ArrayManager::getIndex(static::$plugins, $hook);
    }

    /**
     * Return configuration file
     *
     * @return string
     */
    protected static function getConfigFile()
    {
        return LC_DIR_INCLUDES . 'Decorator' . LC_DS . static::FILE_INI;
    }
}
