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

namespace XLite\View;

/**
 * HTTPS settings page widget
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class HttpsSettings extends \XLite\View\Dialog
{
    /**
     * Suffix of URL to check https availability
     */
    const CHECK_URI_SUFFIX = 'skins/common/js/php.js';


    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'https_settings';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'https_settings';
    }

    /**
     * Return file name for body template
     *
     * @return string
     */
    protected function getBodyTemplate()
    {
        return $this->isCurlAvailable()
            ? parent::getBodyTemplate()
            : 'no_curl.tpl';
    }

    /**
     * Add widget specific CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Get URL of the page where SSL certificate can be purchased
     *
     * @return string
     */
    protected function getPurchaseURL()
    {
        return \XLite::getXCartURL('http://www.x-cart.com/ssl');
    }

    /**
     * Check if curl is available and we can check availablilty of https
     *
     * @return boolean
     */
    protected function isCurlAvailable()
    {
        return function_exists('curl_init');
    }

    /**
     * Check if HTTPS feature is available and can be enabled
     *
     * @return boolean
     */
    protected function isAvailableHTTPS()
    {
        return \XLite\Core\URLManager::isSecureURLAccessible($this->getTestURL());
    }

    /**
     * Check if SSL certificate is valid
     *
     * @return boolean
     */
    protected function isValidSSL()
    {
        return \XLite\Core\URLManager::isSecureURLAccessible($this->getTestURL(), true);
    }

    /**
     * Get URL to test https connection
     *
     * @return string
     */
    protected function getTestURL()
    {
        return \XLite\Core\URLManager::getShopURL(static::CHECK_URI_SUFFIX, true);
    }

    /**
     * Get URL to test https connection
     *
     * @return string
     */
    protected function getDomain()
    {
        $url = parse_url($this->getTestURL());

        return $url['host'];
    }

    /**
     * Check if HTTPS options are enabled
     *
     * @return boolean
     */
    protected function isEnabledHTTPS()
    {
        return \XLite\Core\Config::getInstance()->Security->admin_security
            && \XLite\Core\Config::getInstance()->Security->customer_security
            && (!$this->isCurlAvailable() || $this->isAvailableHTTPS());
    }

    /**
     * Buttons 'Enable HTTPS' and 'Disable HTTPS' are enabled
     *
     * @return boolean
     */
    protected function areButtonsEnabled()
    {
        return true;
    }
}
