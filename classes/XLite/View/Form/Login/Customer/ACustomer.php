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

namespace XLite\View\Form\Login\Customer;

/**
 * Abstract log-in form in customer interface
 */
abstract class ACustomer extends \XLite\View\Form\Login\ALogin
{
    /**
     * getSecuritySetting
     *
     * @return boolean
     */
    protected function getSecuritySetting()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Required form parameters
     *
     * @return array
     */
    protected function getCommonFormParams()
    {
        $list = parent::getCommonFormParams();

        if (\XLite\Core\Request::getInstance()->popup && !\XLite\Core\Request::getInstance()->returnURL) {
            if (\XLite\Core\Request::getInstance()->fromURL) {
                $url = \XLite\Core\Request::getInstance()->fromURL;

            } else {
                $server = \XLite\Core\Request::getInstance()->getServerData();
                $url = empty($server['HTTP_REFERER']) ? null : $server['HTTP_REFERER'];
            }

            if ($url) {
                $list['popup'] = 1;
                $list['returnURL'] = $url;
            }
        } elseif ('checkout' === \XLite\Core\Request::getInstance()->target) {

            $list['returnURL'] = $this->buildURL('checkout');

        } else {
            $server = \XLite\Core\Request::getInstance()->getServerData();
            $url = empty($server['HTTP_REFERER']) ? null : $server['HTTP_REFERER'];

            $list['returnURL'] = $url;
        }

        return $list;
    }

    /**
     * getDefaultClassName
     *
     * @return string
     */
    protected function getDefaultClassName()
    {
        return trim(parent::getDefaultClassName() . ' use-inline-error');
    }

}
