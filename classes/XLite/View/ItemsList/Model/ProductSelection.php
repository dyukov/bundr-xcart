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

namespace XLite\View\ItemsList\Model;

/**
 * Product selections items list
 */
class ProductSelection extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Sorting parameter values
     */
    const SORT_BY_NAME  = 'translations.name';
    const SORT_BY_SKU   = 'p.sku';
    const SORT_BY_PRICE = 'p.price';
    const SORT_BY_AMOUNT = 'i.amount';

    /**
     * Widget parameter names
     */
    const PARAM_CATEGORY_ID = 'categoryId';
    const PARAM_SUBSTRING   = 'substring';
    const PARAM_SEARCH_IN_SUBCATS = 'searchInSubcats';

    /**
     * Sort modes
     *
     * @var   array
     */
    protected $sortByModes = array(
        self::SORT_BY_NAME  => 'Name',
        self::SORT_BY_PRICE => 'Price',
        self::SORT_BY_SKU   => 'SKU',
    );

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'product_selections/style.css';

        return $list;
    }

    /**
     * Get switcher field
     *
     * @return array
     */
    protected function getSwitcherField()
    {
        return array(
            'class'  => 'XLite\View\FormField\Inline\Input\Checkbox\Switcher\EnabledReadOnly',
            'name'   => 'enabled',
            'params' => array(),
        );
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
                static::COLUMN_SORT    => static::SORT_BY_SKU,
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_ORDERBY  => 100,
            ),
            'name' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Name'),
                static::COLUMN_SORT    => static::SORT_BY_NAME,
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_ORDERBY  => 200,
            ),
            'price' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Price'),
                static::COLUMN_TEMPLATE => 'product_selections/parts/info.price.tpl',
                static::COLUMN_SORT     => static::SORT_BY_PRICE,
                static::COLUMN_ORDERBY  => 300,
            ),
            'qty' => array(
                static::COLUMN_NAME    => \XLite\Core\Translation::lbl('Stock'),
                static::COLUMN_SORT    => static::SORT_BY_AMOUNT,
                static::COLUMN_ORDERBY  => 400,
            ),
        );
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

    /**
     * Template for selector action definition
     *
     * @return string
     */
    protected function getSelectorActionTemplate()
    {
        return 'product_selections/parts/selector.tpl';
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array('product_selector');
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_NAME;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Product';
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' product_selections';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return 'XLite\View\Pager\Admin\Model\ProductSelection\Table';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\ItemsList\ProductSelection';
    }

    /**
     * Initialize widget (set attributes)
     *
     * @param array $params Widget params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        if ($this->getCategoryId()) {
            if ($this->getCategoryId() == $this->getRootCategoryId()) {
                $this->widgetParams[static::PARAM_CATEGORY_ID]->setValue(0);
            } else {
                $this->widgetParams[static::PARAM_CATEGORY_ID]->setValue($this->getCategoryId());
            }
        }
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
            static::PARAM_CATEGORY_ID => new \XLite\Model\WidgetParam\Int(
                'CategoryID ', $this->getCategoryId()
            ),
            static::PARAM_SUBSTRING => new \XLite\Model\WidgetParam\String(
                'Substring', ''
            ),
        );
    }

    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            static::PARAM_SUBSTRING => static::PARAM_SUBSTRING,
            static::PARAM_CATEGORY_ID => static::PARAM_CATEGORY_ID,
            static::PARAM_SEARCH_IN_SUBCATS => static::PARAM_SEARCH_IN_SUBCATS,
        );
    }

    /**
     * Return params list to use for search
     * TODO refactor
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        $result->{\XLite\Model\Repo\Product::P_ORDER_BY} = $this->getOrderBy();
        $result->{static::PARAM_SEARCH_IN_SUBCATS} = true;

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if (static::PARAM_CATEGORY_ID === $requestParam && '0' === $paramValue) {
                $paramValue = 0;
            }

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        return $result;
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

    // }}}

}