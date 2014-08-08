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

namespace XLite\View\ItemsList\Model\Product\Admin;

/**
 * Top selling products list (for dashboard page)
 */
class TopSellers extends \XLite\View\ItemsList\Model\Product\Admin\LowInventoryBlock
{
    /**
     * Widget parameter name
     */
    const PARAM_PERIOD = 'period';
    const PARAM_PRODUCTS_LIMIT = 'products_limit';

    /**
     * Allowed values for PARAM_PERIOD parameter
     */
    const P_PERIOD_DAY      = 'day';
    const P_PERIOD_WEEK     = 'week';
    const P_PERIOD_MONTH    = 'month';
    const P_PERIOD_LIFETIME = 'lifetime';


    /**
     * Get allowed periods
     *
     * @return array
     */
    public static function getAllowedPeriods()
    {
        return array(
            self::P_PERIOD_DAY      => 'Last 24 hours',
            self::P_PERIOD_WEEK     => 'Last 7 days',
            self::P_PERIOD_MONTH    => 'Last month',
            self::P_PERIOD_LIFETIME => 'Store lifetime',
        );
    }


    /**
     * Hide 'More...' link
     *
     * @return null
     */
    public function getMoreLink()
    {
        return null;
    }

    /**
     * Hide 'More...' link
     *
     * @return null
     */
    public function getMoreLinkTitle()
    {
        return null;
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
            static::PARAM_PERIOD         => new \XLite\Model\WidgetParam\String('Period', self::P_PERIOD_LIFETIME),
            static::PARAM_PRODUCTS_LIMIT => new \XLite\Model\WidgetParam\Int('Number of products', 5),
        );
    }

    /**
     * Define items list columns
     *
     * @return array
     */
    protected function defineColumns()
    {
        $allowedColumns = array(
            'sku',
            'name',
            'sold',
        );

        $columns = parent::defineColumns();

        $columns['sold'] = array(
            static::COLUMN_NAME  => \XLite\Core\Translation::lbl('Sold'),
            static::COLUMN_ORDERBY  => 10000,
        );

        // Remove redundant columns
        foreach ($columns as $k => $v) {
            if (!in_array($k, $allowedColumns)) {
                unset($columns[$k]);
            }
        }

        return $columns;
    }

    /*
     * getEmptyListTemplate
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return $this->getDir() . '/' . $this->getPageBodyDir() . '/product/empty_top_sellers_list.tpl';
    }

    /**
     * Get search conditions
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->date = array($this->getStartDate(), 0);

        $cnd->currency = \XLite::getInstance()->getCurrency()->getCurrencyId();

        $cnd->limit = $this->getParam(self::PARAM_PRODUCTS_LIMIT);

        return $cnd;
    }

    /**
     * Get period start date timestamp
     *
     * @return integer
     */
    protected function getStartDate()
    {
        $now = \XLite\Core\Converter::time();

        switch ($this->getParam(self::PARAM_PERIOD)) {
            case self::P_PERIOD_DAY:
                $startDate = mktime(0, 0, 0, date('m', $now), date('d', $now), date('Y', $now));
                break;

            case self::P_PERIOD_WEEK:
                $startDate = $now - (date('w', $now) * 86400);
                break;

            case self::P_PERIOD_MONTH:
                $startDate = mktime(0, 0, 0, date('m', $now), 1, date('Y', $now));
                break;

            case self::P_PERIOD_LIFETIME:
            default:
                $startDate = 0;
        }

        return $startDate;
    }

    /**
     * Get data for items list
     *
     * @param \XLite\Core\CommonCell $cnd       Search conditions
     * @param boolean                $countOnly Count only flag
     *
     * @return array
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $data = \XLite\Core\Database::getRepo('\XLite\Model\OrderItem')
            ->getTopSellers($this->getSearchCondition(), $countOnly);

        return $countOnly
            // $data is a quantity of collection
            ? $data
            // $data is a collection and we must extract product data from it
            : array_map(array($this, 'extractProductData'), $data);
    }

    /**
     * Extract product info from order item
     * It is used in the collection cycle for self::getData method
     *
     * @see \XLite\View\ItemsList\Model\Product\Admin\TopSellers::getData
     *
     * @param  array $item
     *
     * @return \XLite\Model\Product
     */
    public function extractProductData($item)
    {
        $product = $item[0]->getProduct();
        $product->setSold($item['cnt']);

        return $product;
    }

    /**
     * We add the "removed" text for the products which were removed from the catalog
     *
     * @param string               $value  Product name
     * @param array                $column Column data
     * @param \XLite\Model\Product $entity Product model
     *
     * @return string
     */
    protected function preprocessName($value, array $column, \XLite\Model\Product $entity)
    {
        return $entity->isPersistent() ? $value : ($value . ' <span class="removed">(removed)</span>');
    }

    /**
     * Check if the column must be a link.
     * It is used if the column field is displayed via
     *
     * @param array                $column
     * @param \XLite\Model\AEntity $entity
     *
     * @return boolean
     */
    protected function isLink(array $column, \XLite\Model\AEntity $entity)
    {
        return parent::isLink($column, $entity)
            // Deleted product entity must not be displayed as a link
            && $entity->isPersistent();
    }
}

