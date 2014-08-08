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

namespace XLite\View\SearchPanel\Order\Admin;

/**
 * Main admin orders list search panel
 */
class Main extends \XLite\View\SearchPanel\Order\Admin\AAdmin
{

    /**
     * Via this method the widget registers the CSS files which it uses.
     * During the viewers initialization the CSS files are collecting into the static storage.
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'search_panel/order/style.css';

        return $list;
    }

    /**
     * Get form class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\Order\Search';
    }

    /**
     * Define the items list CSS class with which the search panel must be linked
     *
     * @return string
     */
    protected function getLinkedItemsList()
    {
        return parent::getLinkedItemsList() . '.widget.items-list.orders';
    }

    /**
     * Define conditions
     *
     * @return array
     */
    protected function defineConditions()
    {
        return parent::defineConditions() + array(
            'substring' => array(
                static::CONDITION_CLASS => 'XLite\View\FormField\Input\Text',
                \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY         => true,
                \XLite\View\FormField\Input\Text::PARAM_PLACEHOLDER => static::t('Enter OrderID or email'),
            ),
            'paymentStatus' => array(
                static::CONDITION_CLASS => '\XLite\View\FormField\Select\OrderStatus\Payment',
                \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY          => true,
                \XLite\View\FormField\Select\OrderStatus\Payment::PARAM_ALL_OPTION  => true,
            ),
            'shippingStatus' => array(
                static::CONDITION_CLASS => '\XLite\View\FormField\Select\OrderStatus\Shipping',
                \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY          => true,
                \XLite\View\FormField\Select\OrderStatus\Shipping::PARAM_ALL_OPTION  => true,
            ),
            'dateRange' => array(
                static::CONDITION_CLASS => '\XLite\View\FormField\Input\Text\DateRange',
                \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY  => true,
            ),
        );
    }

    /**
     * Define hidden conditions
     *
     * @return array
     */
    protected function defineHiddenConditions()
    {
        return parent::defineHiddenConditions() + array(
            'accessLevel' => array(
                static::CONDITION_CLASS => 'XLite\View\FormField\Select\Order\CustomerAccessLevel',
                \XLite\View\FormField\AFormField::PARAM_LABEL => static::t('Customer access level'),
            ),
        );
    }
}
