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

namespace XLite\View;

/**
 * Page header
 */
class Header extends \XLite\View\AResourcesContainer
{
    /**
     * Get head prefixes
     *
     * @return array
     */
    public static function defineHeadPrefixes()
    {
        return array();
    }

    /**
     * Get meta description
     *
     * @return string
     */
    protected function getMetaDescription()
    {
        return ($result = \XLite::getController()->getMetaDescription())
            ? trim(strip_tags($result))
            : $this->getDefaultMetaDescription();
    }

    /**
     * Get default meta description
     *
     * @return string
     */
    protected function getDefaultMetaDescription()
    {
        return 'The powerful shopping cart software for web stores and e-commerce '
            . 'enabled stores is based on PHP5 with SQL database with highly '
            . 'configurable implementation based on templates';
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getTitle()
    {
        return \XLite::getController()->getPageTitle() ?: $this->getDefaultTitle();
    }

    /**
     * Get default title
     *
     * @return string
     */
    protected function getDefaultTitle()
    {
        return 'X-Cart 5';
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'header';
    }

    /**
     * Get collected meta tags
     *
     * @return array
     */
    protected function getMetaResources()
    {
        return static::getRegisteredMetas();
    }

    /**
     * Get script
     *
     * @return string
     */
    protected function getScript()
    {
        return \XLite::getInstance()->getScript();
    }

    /**
     * Defines the base URL for the page
     * 
     * @return string
     */
    protected function getBaseShopURL()
    {
        return \XLite::getInstance()->getShopURL();
    }
    
    /**
     * Get head tag attributes
     *
     * @return array
     */
    protected function getHeadAttributes()
    {
        $list = array(
            'profile' => 'http://www.w3.org/1999/xhtml/vocab',
        );

        $prefixes = static::defineHeadPrefixes();
        if ($prefixes) {
            $data = array();
            foreach ($prefixes as $name => $uri) {
                $data[] = $name . ': ' . $uri;
            }
            $prefixes = implode(' ', $data);
        }

        if ($prefixes) {
            $list['prefix'] = $prefixes;
        }

        return $list;
    }

    /**
     * Get ZeroClipboard library's SWF URL
     * 
     * @return string
     */
    protected function getZeroClipboardSWFUrl()
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'ZeroClipboard.swf',
            \XLite\Core\Layout::WEB_PATH_OUTPUT_SHORT,
            \XLite::COMMON_INTERFACE
        );
    }
}
