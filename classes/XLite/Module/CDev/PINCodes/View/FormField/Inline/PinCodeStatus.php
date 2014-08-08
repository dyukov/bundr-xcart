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

namespace XLite\Module\CDev\PINCodes\View\FormField\Inline;

/**
 * PinCodeStatus
 * 
 */
class PinCodeStatus extends \XLite\View\FormField\Inline\Base\Single
{

    /**
     * Define form field
     *
     * @return string
     */
    protected function defineFieldClass()
    {
        return 'XLite\View\FormField\Label';
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' inline-pin-code-status';
    }

    /**
     * Get view template 
     * 
     * @return string
     */
    protected function getViewTemplate()
    {
        return 'modules/CDev/PINCodes/product/pin_code_status.tpl';
    }

    /**
     * Is sold
     *
     * @param array $field Field
     *
     * @return boolean
     */
    protected function isSold(array $field)
    {
        return $field['field']['parameters']['value'];
    }

    /**
     * Is deleted
     *
     * @param array $field Field
     *
     * @return boolean
     */
    protected function isDeleted(array $field)
    {
        return !($field['field']['parameters']['value'] instanceof \XLite\Model\OrderItem);
    }

    /**
     * Get Order Id
     *
     * @param array $field Field
     *
     * @return integer
     */
    protected function getOrderNumber(array $field)
    {
        $item = $field['field']['parameters']['value'];
    
        return $item->getOrder()->getOrderNumber();   
    }

    /**
     * Get Order URL
     *
     * @param array $field Field
     *
     * @return string
     */
    protected function getOrderUrl(array $field)
    {
        $item = $field['field']['parameters']['value'];

        return $this->buildUrl('order', '', array('order_number' => $item->getOrder()->getOrderNumber()));
    }

    /**
     * Check - field is editable or not
     * 
     * @return boolean
     */
    protected function isEditable()
    {
        return false;
    }

}

