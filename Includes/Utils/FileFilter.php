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

namespace Includes\Utils;

/**
 * FileFilter
 *
 * @package    XLite
 */
class FileFilter extends \Includes\Utils\AUtils
{
    /**
     * Directory to iterate over
     *
     * @var string
     */
    protected $dir;

    /**
     * Pattern to filter files by path
     *
     * @var string
     */
    protected $pattern;

    /**
     * Mode
     *
     * @var int
     */
    protected $mode;

    /**
     * Cache
     *
     * @var \Includes\Utils\FileFilter\FilterIterator
     */
    protected $iterator;


    /**
     * Return the directory iterator
     *
     * @return \RecursiveIteratorIterator
     */
    protected function getUnfilteredIterator()
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            $this->mode
        );
    }


    /**
     * Return the directory iterator
     *
     * @return \Includes\Utils\FileFilter\FilterIterator
     */
    public function getIterator()
    {
        if (!isset($this->iterator)) {
            $this->iterator = new \Includes\Utils\FileFilter\FilterIterator(static::getUnfilteredIterator(), $this->pattern);
        }

        return $this->iterator;
    }

    /**
     * Constructor
     *
     * @param string $dir     Directory to iterate over
     * @param string $pattern Pattern to filter files
     * @param int    $mode    Filtering mode OPTIONAL
     *
     * @return void
     */
    public function __construct($dir, $pattern = null, $mode = \RecursiveIteratorIterator::LEAVES_ONLY)
    {
        $canonicalDir = \Includes\Utils\FileManager::getCanonicalDir($dir);

        if (empty($canonicalDir)) {
            \Includes\ErrorHandler::fireError('Path "' . $dir . '" is not exists or is not readable.');
        }

        $this->dir     = $canonicalDir;
        $this->pattern = $pattern;
        $this->mode    = $mode;
    }
}
