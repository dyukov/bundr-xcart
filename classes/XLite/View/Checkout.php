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
 * Checkout
 *
 * @ListChild (list="center")
 */
class Checkout extends \XLite\View\Dialog
{
    /**
     * Indexes in step data array
     */
    const STEP_TEMPLATE  = 'template';
    const STEP_SHOW_CART = 'showCart';


    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'checkout';

        return $result;
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'checkout/checkout.css';
        if (
            $checkoutCanceled = \XLite\Core\Session::getInstance()->checkoutCanceled
            && \XLite\Core\Request::getInstance()->checkoutCanceled
        ) {
            $list[] = 'back_from_payment/style.css';
        }

        if (!$this->isCheckoutAvailable()) {
            $list[] = 'checkout/signin.css';
        }

        return $list;
    }

    /**
     * Get a list of JS files required to display the widget properly
     * FIXME - decompose these files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'checkout/controller.js';
        $list[] = 'button/js/login.js';
        if (
            $checkoutCanceled = \XLite\Core\Session::getInstance()->checkoutCanceled
            && \XLite\Core\Request::getInstance()->checkoutCanceled
        ) {
            $list[] = 'back_from_payment/controller.js';
        }

        if (!$this->isCheckoutAvailable()) {
            $list[] = 'checkout/login.js';
        }

        return $list;
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        $list[static::RESOURCE_JS][] = 'js/core.popup.js';
        $list[static::RESOURCE_JS][] = 'js/core.popup_button.js';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'checkout';
    }

    /**
     * Get preloaded labels
     *
     * @return array
     */
    protected function getPreloadedLabels()
    {
        $list = array(
            'Enter a correct email',
            'Order can not be placed because not all required fields are completed. Please check the form and try again.',
            'Field is required!',
        );

        $data = array();
        foreach ($list as $name) {
            $data[$name] = static::t($name);
        }

        return $data;
    }

    /**
     * Defines the store name
     *
     * @return string
     */
    protected function getStoreName()
    {
        return \XLite\Core\Config::getInstance()->Company->company_name;
    }

    /**
     * Defines the anonymous title box
     *
     * @return string
     */
    protected function getSigninAnonymousTitle()
    {
        return static::t('Go to checkout as a New customer');
    }
}
