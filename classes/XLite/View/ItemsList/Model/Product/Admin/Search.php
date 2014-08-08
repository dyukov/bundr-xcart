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

namespace XLite\View\ItemsList\Model\Product\Admin;

/**
 * Search product
 */
class Search extends \XLite\View\ItemsList\Model\Product\Admin\AAdmin
{
    /**
     * Widget param names
     */
    const PARAM_SUBSTRING         = 'substring';
    const PARAM_CATEGORY_ID       = 'categoryId';
    const PARAM_SEARCH_IN_SUBCATS = 'searchInSubcats';
    const PARAM_BY_TITLE          = 'by_title';
    const PARAM_BY_DESCR          = 'by_descr';
    const PARAM_BY_SKU            = 'by_sku';
    const PARAM_INVENTORY         = 'inventory';
    const PARAM_ENABLED           = 'enabled';

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
            static::SORT_BY_MODE_PRICE  => 'Price',
            static::SORT_BY_MODE_NAME   => 'Name',
            static::SORT_BY_MODE_SKU    => 'SKU',
            static::SORT_BY_MODE_AMOUNT => 'Amount',
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
        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/product/style.css';

        return $list;
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'sku' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('SKU'),
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_SORT    => static::SORT_BY_MODE_SKU,
                static::COLUMN_ORDERBY => 100,
            ),
            'name' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Name'),
                static::COLUMN_LINK    => 'product',
                static::COLUMN_MAIN    => true,
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_SORT    => static::SORT_BY_MODE_NAME,
                static::COLUMN_ORDERBY => 200,
            ),
            'category' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Category'),
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_ORDERBY => 300,
            ),
            'price' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Price'),
                static::COLUMN_CLASS   => 'XLite\View\FormField\Inline\Input\Text\Price',
                static::COLUMN_PARAMS  => array('min' => 0),
                static::COLUMN_SORT    => static::SORT_BY_MODE_PRICE,
                static::COLUMN_ORDERBY => 400,
            ),
            'qty' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Stock'),
                static::COLUMN_CLASS   => 'XLite\View\FormField\Inline\Input\Text\Integer\ProductQuantity',
                static::COLUMN_SORT    => static::SORT_BY_MODE_AMOUNT,
                static::COLUMN_ORDERBY => 500,
            ),
        );
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return $this->buildURL('add_product');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'Add product';
    }

    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_TOP;
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
        return 'XLite\View\StickyPanel\Product\Admin\Search';
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
            \XLite\Model\Repo\Product::P_SUBSTRING         => static::PARAM_SUBSTRING,
            \XLite\Model\Repo\Product::P_CATEGORY_ID       => static::PARAM_CATEGORY_ID,
            \XLite\Model\Repo\Product::P_SEARCH_IN_SUBCATS => static::PARAM_SEARCH_IN_SUBCATS,
            \XLite\Model\Repo\Product::P_BY_TITLE          => static::PARAM_BY_TITLE,
            \XLite\Model\Repo\Product::P_BY_DESCR          => static::PARAM_BY_DESCR,
            \XLite\Model\Repo\Product::P_BY_SKU            => static::PARAM_BY_SKU,
            \XLite\Model\Repo\Product::P_INVENTORY         => static::PARAM_INVENTORY,
            \XLite\Model\Repo\Product::P_ENABLED           => static::PARAM_ENABLED,
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
            static::PARAM_SUBSTRING         => new \XLite\Model\WidgetParam\String('Substring', ''),
            static::PARAM_CATEGORY_ID       => new \XLite\Model\WidgetParam\Int('Category ID', 0),
            static::PARAM_SEARCH_IN_SUBCATS => new \XLite\Model\WidgetParam\Checkbox('Search in subcategories', 0),
            static::PARAM_BY_TITLE          => new \XLite\Model\WidgetParam\Checkbox('Search in title', 0),
            static::PARAM_BY_DESCR          => new \XLite\Model\WidgetParam\Checkbox('Search in description', 0),
            static::PARAM_BY_SKU            => new \XLite\Model\WidgetParam\Checkbox('Search in sku', 0),
            static::PARAM_INVENTORY         => new \XLite\Model\WidgetParam\String('Inventory', 'all'),
            static::PARAM_ENABLED           => new \XLite\Model\WidgetParam\String('Enabled', ''),
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
        $result->{\XLite\Model\Repo\Product::P_ORDER_BY} = $this->getOrderBy();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $result->$modelParam = $this->getParam($requestParam);
        }

        // Prepare filter by 'enabled' field
        $fieldEnabled = \XLite\Model\Repo\Product::P_ENABLED;
        if (!empty($result->{$fieldEnabled})) {
            $result->{$fieldEnabled} = ('enabled' == $result->{$fieldEnabled} ? true : false);

        } else {
            unset($result->{$fieldEnabled});
        }

        // Correct filter param 'Search in subcategories'
        if (empty($result->{static::PARAM_CATEGORY_ID})) {
            unset($result->{static::PARAM_CATEGORY_ID});
            unset($result->{static::PARAM_SEARCH_IN_SUBCATS});

        } else {
            $result->{static::PARAM_SEARCH_IN_SUBCATS} = true;
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
        return \XLite\Core\Database::getRepo('\XLite\Model\Product')->search($cnd, $countOnly);
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_MODE_NAME;
    }

    // }}}

    // {{{ Content helpers

    /**
     * Get column cell class
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model OPTIONAL
     *
     * @return string
     */
    protected function getColumnClass(array $column, \XLite\Model\AEntity $entity = null)
    {
        $class = parent::getColumnClass($column, $entity);

        if ('qty' == $column[static::COLUMN_CODE] && !$entity->getInventory()->getEnabled()) {
            $class .= ' infinity';
        }

        return $class;
    }

    /**
     * Check - has specified column attention or not
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model OPTIONAL
     *
     * @return boolean
     */
    protected function hasColumnAttention(array $column, \XLite\Model\AEntity $entity = null)
    {
        return parent::hasColumnAttention($column, $entity)
            || ('qty' == $column[static::COLUMN_CODE] && $entity && $entity->getInventory()->isLowLimitReached());
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
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }

    /**
     * Mark list as selectable
     *
     * @return boolean
     */
    protected function isSelectable()
    {
        return true;
    }

    // }}}

    /**
     * Preprocess category
     *
     * @param integer              $date   Date
     * @param array                $column Column data
     * @param \XLite\Model\Product $entity Product
     *
     * @return string
     */
    protected function preprocessCategory($date, array $column, \XLite\Model\Product $entity)
    {
        return $date
            ? func_htmlspecialchars($date->getName())
            : '';
    }
}
