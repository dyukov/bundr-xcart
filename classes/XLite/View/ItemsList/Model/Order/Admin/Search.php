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

namespace XLite\View\ItemsList\Model\Order\Admin;

/**
 * Search order
 */
class Search extends \XLite\View\ItemsList\Model\Order\Admin\AAdmin
{
    /**
     * Widget param names
     */
    const PARAM_ORDER_ID        = 'orderNumber';
    const PARAM_LOGIN           = 'login';
    const PARAM_PAYMENT_STATUS  = 'paymentStatus';
    const PARAM_SHIPPING_STATUS = 'shippingStatus';
    const PARAM_DATE            = 'date';
    const PARAM_SUBSTRING       = 'substring';
    const PARAM_DATE_RANGE      = 'dateRange';
    const PARAM_PROFILE_ID      = 'profileId';
    const PARAM_ACCESS_LEVEL    = 'accessLevel';

    /**
     * Allowed sort criterions
     */
    const SORT_BY_MODE_ID               = 'o.orderNumber';
    const SORT_BY_MODE_DATE             = 'o.date';
    const SORT_BY_MODE_CUSTOMER         = 'p.login';
    const SORT_BY_MODE_PAYMENT_STATUS   = 'o.paymentStatus';
    const SORT_BY_MODE_SHIPPING_STATUS  = 'o.shippingStatus';
    const SORT_BY_MODE_TOTAL            = 'o.total';

    /**
     * Define and set widget attributes; initialize widget
     *
     * @param array $params Widget params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        $this->sortByModes += array(
            static::SORT_BY_MODE_ID              => 'Order ID',
            static::SORT_BY_MODE_DATE            => 'Date',
            static::SORT_BY_MODE_CUSTOMER        => 'Customer',
            static::SORT_BY_MODE_PAYMENT_STATUS  => 'Payment status',
            static::SORT_BY_MODE_SHIPPING_STATUS => 'Shipping status',
            static::SORT_BY_MODE_TOTAL           => 'Amount',
        );

        parent::__construct($params);
    }

    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/order/style.css';

        return $list;
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
        if (!empty($params[static::PARAM_DATE]) && is_array($params[static::PARAM_DATE])) {
            foreach ($params[static::PARAM_DATE] as $i => $date) {
                if (is_string($date) && false !== strtotime($date)) {
                    $params[static::PARAM_DATE][$i] = strtotime($date);
                }
            }
        }

        parent::setWidgetParams($params);
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'orderNumber' => array(
                static::COLUMN_NAME     => static::t('Order #'),
                static::COLUMN_LINK     => 'order',
                static::COLUMN_SORT     => static::SORT_BY_MODE_ID,
                static::COLUMN_ORDERBY  => 100,
            ),
            'date' => array(
                static::COLUMN_NAME     => static::t('Date'),
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/order/cell.date.tpl',
                static::COLUMN_SORT     => static::SORT_BY_MODE_DATE,
                static::COLUMN_ORDERBY  => 200,
            ),
            'profile' => array(
                static::COLUMN_NAME     => static::t('Customer'),
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/order/cell.profile.tpl',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_MAIN     => true,
                static::COLUMN_SORT     => static::SORT_BY_MODE_CUSTOMER,
                static::COLUMN_ORDERBY  => 300,
            ),
            'paymentStatus' => array(
                static::COLUMN_NAME     => static::t('Payment status'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Select\OrderStatus\Payment',
                static::COLUMN_SORT     => static::SORT_BY_MODE_PAYMENT_STATUS,
                static::COLUMN_ORDERBY  => 400,
            ),
            'shippingStatus' => array(
                static::COLUMN_NAME     => static::t('Shipping status'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Select\OrderStatus\Shipping',
                static::COLUMN_SORT     => static::SORT_BY_MODE_SHIPPING_STATUS,
                static::COLUMN_ORDERBY  => 500,
            ),
            'total' => array(
                static::COLUMN_NAME     => static::t('Amount'),
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/order/cell.total.tpl',
                static::COLUMN_SORT     => static::SORT_BY_MODE_TOTAL,
                static::COLUMN_ORDERBY  => 600,
            ),
        );
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array_merge(parent::getListNameSuffixes(), array('search'));
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\Order\Admin\Search';
    }

    /**
     * Preprocess profile
     *
     * @param \XLite\Model\Profile $profile Profile
     * @param array                $column  Column data
     * @param \XLite\Model\Order   $entity  Order
     *
     * @return string
     */
    protected function preprocessProfile(\XLite\Model\Profile $profile, array $column, \XLite\Model\Order $entity)
    {
        $address = $profile->getBillingAddress() ?: $profile->getShippingAddress();

        return $address ? $address->getName() : $profile->getLogin();
    }

    /**
     * Preprocess order id
     *
     * @param integer              $id      Order id
     * @param array                $column  Column data
     * @param \XLite\Model\Order   $entity  Order
     *
     * @return string
     */
    protected function preprocessOrderNumber($id, array $column, \XLite\Model\Order $entity)
    {
        return '#' . str_repeat('0', 5 - strlen($id)) . $id;
    }

    /**
     * Get right actions tempaltes
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = parent::getRightActions();

        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/order/action.link.tpl';

        return $list;
    }

    // {{{ Search

    /**
     * Return search parameters
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            \XLite\Model\Repo\Order::P_ORDER_NUMBER      => static::PARAM_ORDER_ID,
            \XLite\Model\Repo\Order::P_EMAIL             => static::PARAM_LOGIN,
            \XLite\Model\Repo\Order::P_PAYMENT_STATUS    => static::PARAM_PAYMENT_STATUS,
            \XLite\Model\Repo\Order::P_SHIPPING_STATUS   => static::PARAM_SHIPPING_STATUS,
            \XLite\Model\Repo\Order::P_DATE              => static::PARAM_DATE,
            \XLite\Model\Repo\Order::SEARCH_DATE_RANGE   => static::PARAM_DATE_RANGE,
            \XLite\Model\Repo\Order::SEARCH_SUBSTRING    => static::PARAM_SUBSTRING,
            \XLite\Model\Repo\Order::P_PROFILE_ID        => static::PARAM_PROFILE_ID,
            \XLite\Model\Repo\Order::SEARCH_ACCESS_LEVEL => static::PARAM_ACCESS_LEVEL,

        );
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
            static::PARAM_ORDER_ID        => new \XLite\Model\WidgetParam\String('Order ID', ''),
            static::PARAM_LOGIN           => new \XLite\Model\WidgetParam\String('Email', ''),
            static::PARAM_PAYMENT_STATUS  => new \XLite\Model\WidgetParam\Int('Payment status', 0),
            static::PARAM_SHIPPING_STATUS => new \XLite\Model\WidgetParam\Int('ShippingShipping status', 0),
            static::PARAM_DATE            => new \XLite\Model\WidgetParam\Collection('Date', array(null, null)),
            static::PARAM_DATE_RANGE      => new \XLite\Model\WidgetParam\String('Date range', ''),
            static::PARAM_SUBSTRING       => new \XLite\Model\WidgetParam\String('Substring', ''),
            static::PARAM_PROFILE_ID      => new \XLite\Model\WidgetParam\Int('Profile ID', 0),
            static::PARAM_ACCESS_LEVEL    => new \XLite\Model\WidgetParam\String('Customer access level', ''),
        );
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams = array_merge($this->requestParams, static::getSearchParams());
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        // We initialize structure to define order (field and sort direction) in search query.
        $result->{\XLite\Model\Repo\Order::P_ORDER_BY} = $this->getOrderBy();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $value = $this->getParam($requestParam);
            if (static::PARAM_DATE == $requestParam && is_array($value)) {
                foreach ($value as $i => $date) {
                    if (is_string($date) && false !== strtotime($date)) {
                        $value[$i] = strtotime($date);
                    }
                }
            }

            $result->$modelParam = $value;
        }

        return $result;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Order')->search($cnd, $countOnly);
    }

    /**
     * Return default sort by
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_MODE_DATE;
    }

    /**
     * Return default sort mode
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        return static::SORT_ORDER_DESC;
    }

    // }}}

    // {{{ Content helpers

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return 'Orders';
    }

    /**
     * Get items sum quantity
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return integer
     */
    protected function getItemsQuantity(\XLite\Model\Order $order)
    {
        return $order->countQuantity();
    }

    /**
     * Check - order's profile removed or not
     *
     * @param \XLite\Model\Order $order Order
     *
     * @return boolean
     */
    protected function isProfileRemoved(\XLite\Model\Order $order)
    {
        return !$order->getOrigProfile() || $order->getOrigProfile()->getOrder();
    }

    // }}}

    // {{{ Behaviors

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * Mark list as selectable
     *
     * @return void
     */
    protected function isSelectable()
    {
        return true;
    }

    // }}}

}
