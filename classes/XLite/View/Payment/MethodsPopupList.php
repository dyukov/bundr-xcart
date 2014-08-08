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
 * List of payment methods for popup widget
 */
class MethodsPopupList extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();
        $list[] = 'payment_method_selection';

        return $list;
    }

    /**
     * Return file name for the center part template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'payment/methods_popup_list/body.tpl';
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            \XLite\View\Button\Payment\AddMethod::PARAM_PAYMENT_METHOD_TYPE => new \XLite\Model\WidgetParam\String('Payment methods type', ''),
        );
    }

    /**
     * Return payment type for the payment methods list
     *
     * @return string
     */
    protected function getPaymentType()
    {
        return $this->getParam(\XLite\View\Button\Payment\AddMethod::PARAM_PAYMENT_METHOD_TYPE);
    }

    /**
     * Return payment methods list structure to use in the widget
     *
     * @return array
     */
    protected function getPaymentMethods()
    {
        $result = array();

        $list = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findForAdditionByType($this->getPaymentType());
        foreach ($list as $entry) {
            $result[$entry->getModuleName()][] = $entry;
        }

        return $result;
    }
}
