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

namespace XLite\View\Pager\Admin\Module;

/**
 * Abstract pager class for the Modules list widget
 */
abstract class AModule extends \XLite\View\Pager\Admin\AAdmin
{
    const PARAM_CLEAR_PAGER = 'clearPager';

    /**
     * Register CSS files to include
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'items_list/module/pager/css/style.css';

        return $list;
    }

    /**
     * Register CSS files to include
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'items_list/module/pager/js/pager.js';

        return $list;
    }

    /**
     * Return CSS classes to use in parent widget of pager
     *
     * @return string
     */
    public function getCSSClasses()
    {
        return parent::getCSSClasses() . ' addons-pager';
    }

    protected function getClearPagerDefault()
    {
        return (bool)\XLite\Core\Request::getInstance()->{static::PARAM_CLEAR_PAGER};
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_CLEAR_PAGER => new \XLite\Model\WidgetParam\Bool(
                'Clear pager', $this->getClearPagerDefault(), false
            ),
        );
    }

    /**
     * Return ID of the current page
     *
     * @return integer
     */
    protected function getPageId()
    {
        if ($this->getParam(static::PARAM_CLEAR_PAGER)) {
            $this->currentPageId = 0;
        }
        return parent::getPageId();
    }

    /**
     * Return current tag
     *
     * @return string
     */
    protected function getTag()
    {
        return \XLite\Core\Request::getInstance()->tag;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'items_list/module/pager/body.tpl';
    }

    /**
     * Remove the standard pager placing
     *
     * @return boolean
     */
    protected function isVisibleBottom()
    {
        return false;
    }

    /**
     * Define the pager title
     *
     * @return string
     */
    protected function getPagerTitle()
    {
        return $this->getItemsTotal() . ' ' . static::t('modules');
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        foreach (array_keys($this->requestParams, self::PARAM_ITEMS_PER_PAGE) as $key) {
            unset($this->requestParams[$key]);
        }
    }

    /**
     * Return number of pages to display
     *
     * @return integer
     */
    protected function getPagesPerFrame()
    {
        return 5;
    }
}
