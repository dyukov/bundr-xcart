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

namespace Includes\Decorator\Plugin\Doctrine\Utils;

/**
 * FixturesManager 
 *
 */
abstract class FixturesManager extends \Includes\Decorator\Plugin\Doctrine\ADoctrine
{
    /**
     * Fixtures cache
     *
     * @var array
     */
    protected static $fixtures;

    /**
     * Get fixtures paths list
     *
     * @return array
     */
    public static function getFixtures()
    {
        if (!isset(static::$fixtures)) {
            static::$fixtures = array();
            $path = static::getFixturesFilePath();

            if (\Includes\Utils\FileManager::isFileReadable($path)) {
                foreach (parse_ini_file($path, false) as $file) {

                    if (static::checkFile($file)) {
                        static::$fixtures[] = $file;
                    }
                }
            }
        }

        return static::$fixtures;
    }

    /**
     * Remove fixtures
     *
     * @return void
     */
    public static function removeFixtures()
    {
        static::$fixtures = null;

        \Includes\Utils\FileManager::deleteFile(static::getFixturesFilePath());
    }

    /**
     * Add path to fixtures list
     *
     * @param string $file Fixture file path
     *
     * @return void
     */
    public static function addFixtureToList($file)
    {
        static::$fixtures[] = LC_DIR_ROOT . $file;

        static::saveFile();
    }

    /**
     * Remove path from fixtures list
     *
     * @param string $file Fixture file path
     *
     * @return void
     */
    public static function removeFixtureFromList($file)
    {
        if (is_array(static::$fixtures)) {

            foreach (static::$fixtures as $k => $v) {

                if ($v == $file) {
                    unset(static::$fixtures[$k]);
                }
            }

            static::saveFile();
        }
    }

    /**
     * Get file path with fixtures paths
     *
     * @return string
     */
    protected static function getFixturesFilePath()
    {
        return LC_DIR_VAR . '.decorator.fixtures.ini.php';
    }

    /**
     * Save fixtures to file
     *
     * @return void
     */
    protected static function saveFile()
    {
        $string  = '';

        foreach (array_values(array_unique(static::getFixtures())) as $index => $value) {
            $string .= ++$index . ' = "' . $value . '"' . PHP_EOL;
        }

        \Includes\Utils\FileManager::write(static::getFixturesFilePath(), '; <?php /*' . PHP_EOL . $string . '; */ ?>');
    }

    /**
     * Check if module is active
     *
     * @param string $file File name
     *
     * @return boolean
     */
    protected static function checkFile($file)
    {
        $module = \Includes\Utils\ModulesManager::getFileModule($file);

        return !isset($module) || \Includes\Utils\ModulesManager::isActiveModule($module);
    }
}
