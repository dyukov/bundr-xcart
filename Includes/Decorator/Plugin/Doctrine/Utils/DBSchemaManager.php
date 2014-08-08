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
 * DbSchemaManager 
 */
abstract class DBSchemaManager extends \Includes\Decorator\Plugin\Doctrine\ADoctrine
{
    /**
     * Fixtures cache
     *
     * @var array
     */
    protected static $schema;

    /**
     * Get schema
     *
     * @return array
     */
    public static function getDBSchema()
    {
        if (!isset(static::$schema)) {
            $path = static::getDBSchemaFilePath();

            if (\Includes\Utils\FileManager::isFileReadable($path)) {
                $content = \Includes\Utils\FileManager::read($path);

                if ($content) {
                    static::$schema = explode(';', $content);
                }
            }
        }

        return static::$schema;
    }

    /**
     * Prepare DB schema
     *
     * @return void
     */
    public static function prepareDBSchema()
    {
        static::$schema = \XLite\Core\Database::getInstance()->getDBSchema(
            \XLite\Core\Database::getInstance()->getDBSchemaMode()
        );

        static::saveFile();
    }

    /**
     * Update DB schema data
     *
     * @return void
     */
    public static function updateDBSchema($data)
    {
        static::$schema = $data;

        static::saveFile();
    }

    /**
     * Remove DB schema
     *
     * @return void
     */
    public static function removeDBSchema()
    {
        static::$schema = null;

        \Includes\Utils\FileManager::deleteFile(static::getDBSchemaFilePath());
    }

    /**
     * Get file path with DB schema
     *
     * @return string
     */
    protected static function getDBSchemaFilePath()
    {
        return LC_DIR_VAR . '.decorator.dbSchema.php';
    }

    /**
     * Save schema to file
     *
     * @return void
     */
    protected static function saveFile()
    {
        $string = implode(';', static::$schema);

        \Includes\Utils\FileManager::write(static::getDBSchemaFilePath(), $string);
    }
}
