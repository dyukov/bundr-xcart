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

namespace XLite\View\ItemsList\Product\Customer\Category;

/**
 * Category products list widget (abstract)
 *
 */
abstract class ACategory extends \XLite\View\ItemsList\Product\Customer\ACustomer
{
    /**
     * Widget parameter names
     */
    const PARAM_CATEGORY_ID = 'category_id';

    /**
     * Allowed sort criterions
     */
    const SORT_BY_MODE_DEFAULT = 'cp.orderby';

    /**
     * Widget target
     */
    const WIDGET_TARGET = 'category';

    /**
     * Category
     *
     * @var \XLite\Model\Category
     */
    protected $category;

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'main';

        return $result;
    }

    /**
     * Defines the CSS class for sorting order arrow
     *
     * @param string $sortBy
     *
     * @return string
     */
    protected function getSortArrowClassCSS($sortBy)
    {
        return static::SORT_BY_MODE_DEFAULT === $this->getSortBy() ? '' : parent::getSortArrowClassCSS($sortBy);
    }

    /**
     * getSortOrder
     *
     * @return string
     */
    protected function getSortOrder()
    {
        return static::SORT_BY_MODE_DEFAULT === $this->getSortBy() ? static::SORT_ORDER_ASC : parent::getSortOrder();
    }

    /**
     * Return target to retrive this widget from AJAX
     *
     * @return string
     */
    protected static function getWidgetTarget()
    {
        return static::WIDGET_TARGET;
    }

    /**
     * Define and set widget attributes; initialize widget
     *
     * @param array $params Widget params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        parent::__construct($params);

        if ('default' == \XLite\Core\Config::getInstance()->General->default_products_sort_order) {
           $this->sortByModes = array(
                static::SORT_BY_MODE_DEFAULT => 'Default-sort-option',
            ) + $this->sortByModes;
        }
    }

    /**
     * Get products 'sort by' fields
     *
     * @return array
     */
    protected function getSortByFields()
    {
        return array(
            'default' => static::SORT_BY_MODE_DEFAULT,
        ) + parent::getSortByFields();
    }

    /**
     * Returns CSS classes for the container element
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return parent::getListCSSClasses() . ' category-products';
    }


    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return '\XLite\View\Pager\Customer\Product\Category';
    }

    /**
     * Get requested category object
     *
     * @return \XLite\Model\Category
     */
    protected function getCategory()
    {
        if (!isset($this->category)) {
            $this->category = \XLite\Core\Database::getRepo('XLite\Model\Category')->find($this->getCategoryId());
        }

        return $this->category;
    }

    /**
     * Get requested category ID
     *
     * @return integer
     */
    protected function getCategoryId()
    {
        return \XLite\Core\Request::getInstance()->{static::PARAM_CATEGORY_ID}
            ?: $this->getRootCategoryId();
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
            static::PARAM_CATEGORY_ID => new \XLite\Model\WidgetParam\ObjectId\Category('Category ID', $this->getRootCategoryId()),
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

        $this->requestParams[] = static::PARAM_CATEGORY_ID;
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        list($sortField, $sortMode) = $this->getDefaultSortOrderFromOption();

        return $sortField ?: parent::getSortByModeDefault();
    }

    /**
     * getSortOrderDefault
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        list($sortField, $sortMode) = $this->getDefaultSortOrderFromOption();

        return $sortMode ?: parent::getSortOrderModeDefault();
    }

    /**
     * Get default sort order values from option
     * Returned an array(<sortField>, <asc|desc|null>)
     *
     * @return array
     */
    protected function getDefaultSortOrderFromOption()
    {
        // Parse option value
        preg_match(
            '/^(\w+)(Asc|Desc)?$/SsU',
            \XLite\Core\Config::getInstance()->General->default_products_sort_order,
            $match
        );

        // Get list of available sort fields
        $sortFields = $this->getSortByFields();

        $option =(!empty($match[1]) && !empty($sortFields[$match[1]]))
            ? $sortFields[$match[1]]
            : null;

        $sortMode = $option && !empty($match[2])
            ? strtolower($match[2])
            : null;

        return array($option, $sortMode);
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|void
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $category = $this->getCategory();

        return $category ? $category->getProducts($cnd, $countOnly) : null;
    }

    /**
     * Get widget parameters
     *
     * @return array
     */
    protected function getWidgetParameters()
    {
        $list = parent::getWidgetParameters();
        $list['category_id'] = \XLite\Core\Request::getInstance()->category_id;

        return $list;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getCategory() && $this->getCategory()->isVisible();
    }

    // {{{ Cache

    /**
     * Get cache parameters
     *
     * @return array
     */
    protected function getCacheParameters()
    {
        $list = parent::getCacheParameters();

        $list[] = $this->getCategoryId();

        return $list;
    }

    // }}}

}
