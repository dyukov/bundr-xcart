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

namespace XLite\View\Button;

/**
 * 'Print invoice' button widget
 *
 * @ListChild (list="page.tabs.after", zone="admin", weight="100")
 */
class PrintInvoice extends \XLite\View\Button\AButton
{
    /**
     * Several inner constants
     */
    const PRINT_INVOICE_JS       = 'button/js/print_invoice.js';
    const PRINT_INVOICE_CSS      = 'button/css/print_invoice.css';
    const PRINT_INVOICE_TEMPLATE = 'button/print_invoice.tpl';

    /**
     * Widget parameters to use
     */
    const PARAM_ORDER_ID  = 'orderId';
    const PARAM_HAS_IMAGE = 'hasImage';


    /**
     * Return list of allowed targets
     * 
     * @return array
     */
    public static function getAllowedTargets()
    {
        $targets = parent::getAllowedTargets();
        $targets[] = 'order';

        return $targets;
    }


    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = self::PRINT_INVOICE_JS;

        return $list;
    }

    /**
     * Return CSS files list
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = self::PRINT_INVOICE_CSS;

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return self::PRINT_INVOICE_TEMPLATE;
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
            self::PARAM_ORDER_ID  => new \XLite\Model\WidgetParam\Int('OrderID', null),
        );
    }

    /**
     * Get default CSS class name
     * 
     * @return string
     */
    protected function getDefaultStyle()
    {
        return 'button print-invoice';
    }

    /**
     * Get default label 
     * 
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Print invoice';
    }

    /**
     * Get order ID 
     * 
     * @return integer
     */
    protected function getOrderId()
    {
        $orderId = $this->getParam(self::PARAM_ORDER_ID);

        if (empty($orderId)) {
            $orderId = \XLite\Core\Request::getInstance()->order_id;
        }

        return $orderId;
    }

    /**
     * Return URL params to use with onclick event
     *
     * @return array
     */
    protected function getURLParams()
    {
        return array(
            'url_params' => array (
                'target'       => 'order',
                'order_number' => $this->getOrder()->getOrderNumber(),
                'mode'         => 'invoice',
            ),
        );
    }
}
