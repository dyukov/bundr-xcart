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

namespace XLite\View\Payment;

/**
 * IFRAME-based payment page
 *
 * @ListChild (list="center")
 */
class Iframe extends \XLite\View\AView
{
    /**
     * Common widget parameter names
     */
    const PARAM_WIDTH  = 'width';
    const PARAM_HEIGHT = 'height';
    const PARAM_SRC    = 'src';


    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $targets = parent::getAllowedTargets();

        $targets[] = 'checkoutPayment';

        return $targets;
    }

    /**
     * Set widget params
     *
     * @param array $params Handler params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        if (is_array(\XLite\Core\Session::getInstance()->iframePaymentData)) {
            $params = array_merge($params, \XLite\Core\Session::getInstance()->iframePaymentData);
        }

        parent::setWidgetParams($params);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Session::getInstance()->iframePaymentData;
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
            self::PARAM_WIDTH  => new \XLite\Model\WidgetParam\Int('Width', 400),
            self::PARAM_HEIGHT => new \XLite\Model\WidgetParam\Int('Height', 400),
            self::PARAM_SRC    => new \XLite\Model\WidgetParam\String('Source', ''),
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'payment/iframe.tpl';
    }

}

