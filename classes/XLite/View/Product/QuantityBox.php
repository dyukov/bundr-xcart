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

namespace XLite\View\Product;

/**
 * QuantityBox
 */
class QuantityBox extends \XLite\View\Product\AProduct
{
    /**
     * Widget param names
     */

    const PARAM_PRODUCT      = 'product';
    const PARAM_ORDER_ITEM   = 'orderItem';
    const PARAM_FIELD_NAME   = 'fieldName';
    const PARAM_FIELD_VALUE  = 'fieldValue';
    const PARAM_FIELD_TITLE  = 'fieldTitle';
    const PARAM_STYLE        = 'style';
    const PARAM_IS_CART_PAGE = 'isCartPage';
    const PARAM_FORCE_VALUE  = 'forceValue';
    const PARAM_MAX_VALUE    = 'maxValue';

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/quantity_box.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/controller.js';

        return $list;
    }


    /**
     * Return directory contains the template
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/quantity_box';
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
            self::PARAM_FIELD_NAME   => new \XLite\Model\WidgetParam\String('Name', 'amount'),
            self::PARAM_FIELD_TITLE  => new \XLite\Model\WidgetParam\String('Title', 'Quantity'),
            self::PARAM_PRODUCT      => new \XLite\Model\WidgetParam\Object('Product', null, false, '\XLite\Model\Product'),
            self::PARAM_ORDER_ITEM   => new \XLite\Model\WidgetParam\Object('Order item', null, false, '\XLite\Model\OrderItem'),
            self::PARAM_FIELD_VALUE  => new \XLite\Model\WidgetParam\Int('Value', null),
            self::PARAM_STYLE        => new \XLite\Model\WidgetParam\String('CSS class', ''),
            self::PARAM_IS_CART_PAGE => new \XLite\Model\WidgetParam\Bool('Is cart page', false),
            self::PARAM_FORCE_VALUE  => new \XLite\Model\WidgetParam\Bool('Force field value', false),
            self::PARAM_MAX_VALUE    => new \XLite\Model\WidgetParam\Int('Max value', null),
        );
    }

    /**
     * Alias
     *
     * @return \XLite\Model\Product
     */
    protected function getProduct()
    {
        return $this->getOrderItem()
            ? $this->getOrderItem()->getProduct()
            : $this->getParam(self::PARAM_PRODUCT);
    }

    /**
     * Alias
     *
     * @return \XLite\Model\OrderItem
     */
    protected function getOrderItem()
    {
        return $this->getParam(self::PARAM_ORDER_ITEM);
    }

    /**
     * Alias
    *
     * @return string
     */
    protected function getBoxName()
    {
        return $this->getParam(self::PARAM_FIELD_NAME);
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getBoxId()
    {
        return $this->getBoxName() . $this->getProduct()->getProductId();
    }

    /**
     * Alias
     *
     * @return integer
     */
    protected function getBoxValue()
    {
        $value = $this->getParam(self::PARAM_FIELD_VALUE) ?: $this->getProduct()->getMinPurchaseLimit();

        return $this->isCartPage() ? $value : max($value, $this->getMinQuantity());
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getBoxTitle()
    {
        return $this->getParam(self::PARAM_FIELD_TITLE);
    }

    /**
     * Alias
     *
     * @return boolean
     */
    protected function isCartPage()
    {
        return $this->getParam(self::PARAM_IS_CART_PAGE);
    }

    /**
     * CSS class
     *
     * @return string
     */
    protected function getClass()
    {
        $max = $this->getProduct()->getInventory()->getEnabled() ? ',max[' . $this->getMaxQuantity() . ']' : '';

        return trim(
            'quantity'
            . ' wheel-ctrl'
            . ($this->isCartPage() ? ' watcher' : '')
            . ' ' . $this->getParam(self::PARAM_STYLE)
            . ' validate[required,custom[integer],min[' . $this->getMinQuantity() . ']' . $max . ']'
        );
    }

    /**
     * Return name of the \XLite\Model\Inventory model to get max available quantity
     *
     * @return string
     */
    protected function getMaxQuantityMethod()
    {
        return $this->isCartPage() ? 'getAmount' : 'getAvailableAmount';
    }

    /**
     * Return maximum allowed quantity
     *
     * @return integer
     */
    protected function getMaxQuantity()
    {
        $maxValue = $this->getParam(self::PARAM_MAX_VALUE);

        return isset($maxValue)
            ? $maxValue
            : $this->getProduct()->getInventory()->{$this->getMaxQuantityMethod()}();
    }

    /**
     * Return minimum quantity
     *
     * @return integer
     */
    protected function getMinQuantity()
    {
        return 1;
    }
}
