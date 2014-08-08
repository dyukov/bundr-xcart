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
 * Minicart widget
 *
 * @ListChild (list="layout.header.right", weight="100")
 */
class Minicart extends \XLite\View\SideBarBox
{
    /**
     * Widget parameter names
     */
    const PARAM_DISPLAY_MODE = 'displayMode';

    /**
     * Allowed display modes
     */
    const DISPLAY_MODE_HORIZONTAL = 'horizontal';

    /**
     * Number of cart items to display by default
     */
    const ITEMS_TO_DISPLAY = 3;


    /**
     * Widget directories
     *
     * @var array
     */
    protected $displayModes = array(
        self::DISPLAY_MODE_HORIZONTAL => 'Horizontal',
    );


    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'mini_cart/minicart.css';

        return $list;
    }

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return void
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'mini_cart/minicart.js';

        return $list;
    }

    /**
     * Get widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'mini_cart/' . strtolower($this->getParam(self::PARAM_DISPLAY_MODE));
    }

    /**
     * Get cart object without calculation
     *
     * @return \XLite\Model\Cart
     */
    protected function getCart()
    {
        return parent::getCart(false);
    }

    /**
     * Return up to 3 items from cart
     *
     * @return array
     */
    protected function getItemsList()
    {
        return array_slice(
            $this->getCart()->getItems()->toArray(),
            0,
            min(self::ITEMS_TO_DISPLAY, $this->getCart()->countItems())
        );
    }

    /**
     * Check whether in cart there are more than 3 items
     *
     * @return boolean
     */
    protected function isTruncated()
    {
        return self::ITEMS_TO_DISPLAY < $this->getCart()->countItems();
    }

    /**
     * Get cart total
     *
     * @return array
     */
    protected function getTotals()
    {
        return array('Total' => $this->getCart()->getTotal());
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
            self::PARAM_DISPLAY_MODE => new \XLite\Model\WidgetParam\Set(
                'Display mode', self::DISPLAY_MODE_HORIZONTAL, true, $this->displayModes
            ),
        );
    }

    /**
     * Get the detailed description of the reason why the cart is disabled
     * @todo Move it to another place?
     *
     * @return string
     */
    protected function getDisabledReason()
    {
        $result = '';

        $cart = $this->getCart();

        if ($cart->isMaxOrderAmountError()) {
            $result = $this->getMaxOrderAmountErrorReason();

        } elseif ($cart->isMinOrderAmountError()) {
            $result = $this->getMinOrderAmountErrorReason();

        } elseif ($cart->getItemsWithWrongAmounts()) {
            $result = $this->getItemsWithWrongAmountErrorReason();
        }

        return $result;
    }

    /**
     * Defines the error message if cart contains products with wrong quantity
     *
     * @return string
     */
    protected function getItemsWithWrongAmountErrorReason()
    {
        return static::t(
            '<p>Cart contains products with wrong quantity</p>'
        );
    }

    /**
     * Defines the error message if the maximum order amount exceeds
     *
     * @return string
     */
    protected function getMaxOrderAmountErrorReason()
    {
        return static::t(
            '<p>The order subtotal exceeds the maximum allowed value ({{max_order_amount}})</p>',
            array(
                'max_order_amount' => static::formatPrice(
                    \XLite\Core\Config::getInstance()->General->maximal_order_amount
                ),
            )
        );
    }

    /**
     * Defines the error message if the total is less than minimum order amount
     *
     * @return string
     */
    protected function getMinOrderAmountErrorReason()
    {
        return static::t(
            '<p>The order subtotal less than the minimum allowed value ({{min_order_amount}})</p>',
            array(
                'min_order_amount' => static::formatPrice(
                    \XLite\Core\Config::getInstance()->General->minimal_order_amount
                ),
            )
        );
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && !$this->isCheckoutLayout();
    }

    /**
     * Get items container attributes
     *
     * @return array
     */
    protected function getContainerAttributes()
    {
        $attributes = array(
            'class' => array(
                'lc-minicart',
                'lc-minicart-' . $this->getParam(static::PARAM_DISPLAY_MODE),
                'collapsed'
            ),
        );

        if ($this->getCart()->isEmpty()) {
            $attributes['class'][] = 'empty';
        }

        return $attributes;
    }

    /**
     * Get items container attributes
     *
     * @return array
     */
    protected function getItemsContainerAttributes()
    {
        $attributes = array(
            'class' => array('internal-popup', 'items-list'),
        );

        $attributes['class'][] = $this->getCart()->isEmpty() ? 'empty-cart' : 'full-cart';

        return $attributes;
    }

    // {{{ Cache

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return $this->getCart()->isEmpty();
    }

    // }}}

}
