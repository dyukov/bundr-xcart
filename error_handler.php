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

define('LC_ERR_TAG_MSG',   '@MSG@');
define('LC_ERR_TAG_ERROR', '@ERROR@');
define('LC_ERR_TAG_CODE',  '@CODE@');

define('LC_ERROR_PAGE_MESSAGE', 'ERROR: "' . LC_ERR_TAG_ERROR . '" (' . LC_ERR_TAG_CODE . ') - ' . LC_ERR_TAG_MSG . LC_EOL);

/**
 * Display error message
 *
 * @param string  $code    Error code
 * @param string  $message Error message
 * @param string  $page    Template of message to display
 *
 * @return void
 */
function showErrorPage($code, $message, $page = LC_ERROR_PAGE_MESSAGE, $prefix = 'ERROR_')
{
    header('Content-Type: text/html; charset=utf-8');

    echo str_replace(
        array(LC_ERR_TAG_MSG, LC_ERR_TAG_ERROR, LC_ERR_TAG_CODE),
        array($message, str_replace($prefix, '', $code), defined($code) ? constant($code) : 'N/A'),
        $page
    );

    exit (intval($code) ? $code : 1);
}

// Check PHP version before any other operations
if (!defined('LC_DO_NOT_CHECK_PHP_VERSION') && version_compare(PHP_VERSION, '5.3.0', '<')) {
    showErrorPage('ERROR_UNSUPPORTED_PHP_VERSION', 'Min allowed PHP version is 5.3.0');
}
