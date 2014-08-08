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

namespace XLite\View\ItemsList;

/**
 * Base class for all lists
 */
abstract class AItemsList extends \XLite\View\Container
{
    /**
     * Widget param names
     */
    const PARAM_SORT_BY      = 'sortBy';
    const PARAM_SORT_ORDER   = 'sortOrder';

    /**
     * SQL orderby directions
     */
    const SORT_ORDER_ASC  = 'asc';
    const SORT_ORDER_DESC = 'desc';

    /**
     * Default layout template
     *
     * @var string
     */
    protected $defaultTemplate = 'common/dialog.tpl';

    /**
     * commonParams
     *
     * @var array
     */
    protected $commonParams;

    /**
     * pager
     *
     * @var \XLite\View\Pager\APager
     */
    protected $pager;

    /**
     * itemsCount
     *
     * @var integer
     */
    protected $itemsCount;

    /**
     * sortByModes
     *
     * @var array
     */
    protected $sortByModes = array();

    /**
     * sortOrderModes
     *
     * @var array
     */
    protected $sortOrderModes = array(
        self::SORT_ORDER_ASC  => 'Ascending',
        self::SORT_ORDER_DESC => 'Descending',
    );

    /**
     * Sorting widget IDs list
     *
     * @var array
     */
    protected static $sortWidgetIds = array();

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    abstract protected function getPageBodyDir();

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    abstract protected function getPagerClass();

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    abstract protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false);

    /**
     * Get session cell name for the certain list items widget
     *
     * @return string
     */
    static public function getSessionCellName()
    {
        return str_replace('\\', '', get_called_class());
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

        // Do not change call order
        $this->widgetParams += $this->getPager()->getWidgetParams();
        $this->requestParams = array_merge($this->requestParams, $this->getPager()->getRequestParams());
    }

    /**
     * getActionURL
     *
     * @param array $params Params to modify OPTIONAL
     *
     * @return string
     */
    public function getActionURL(array $params = array())
    {
        return $this->getURL($params + $this->getURLParams());
    }

    /**
     * Get a list of JavaScript files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        // Static call of the non-static function
        $list[] = self::getDir() . '/items_list.js';

        $list[] = 'button/js/remove.js';

        $list[] = 'form_field/js/text.js';
        $list[] = 'form_field/input/text/float.js';
        $list[] = 'form_field/input/text/integer.js';
        $list[] = 'form_field/input/checkbox/switcher.js';

        $list[] = 'form_field/inline/controller.js';
        $list[] = 'form_field/inline/input/text.js';
        $list[] = 'form_field/inline/input/text/integer.js';
        $list[] = 'form_field/inline/input/text/price.js';

        return $list;
    }

    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        // Static call of the non-static function
        $list[] = self::getDir() . '/items_list.css';
        $list = self::preparePagerCSSFiles($list);

        $list[] = 'form_field/inline/style.css';
        $list[] = 'form_field/input/price.css';
        $list[] = 'form_field/input/symbol.css';
        $list[] = 'form_field/input/checkbox/switcher.css';

        return $list;
    }

    /**
     * Returns a list of CSS classes (separated with a space character) to be attached to the items list
     *
     * @return string
     */
    public function getListCSSClasses()
    {
        return 'items-list';
    }

    /**
     * Return inner head for list widgets
     *
     * @return string
     */
    protected function getListHead()
    {
        return parent::getHead();
    }

    /**
     * Return CSS classes for list header
     *
     * @return string
     */
    protected function getListHeadClass()
    {
        return '';
    }

    /**
     * Return number of items in products list
     *
     * @return array
     */
    protected function getItemsCount()
    {
        if (!isset($this->itemsCount)) {
            $this->itemsCount = $this->getData($this->getSearchCondition(), true);
        }

        return $this->itemsCount;
    }

    /**
     * Return name of the base widgets list
     *
     * @return string
     */
    protected function getListName()
    {
        return 'itemsList';
    }

    /**
     * Get widget templates directory
     * NOTE: do not use "$this" pointer here (see "getBody()" and "get[CSS/JS]Files()")
     *
     * @return string
     */
    protected function getDir()
    {
        return 'items_list';
    }

    /**
     * prepare CSS file list for use with pager
     *
     * @param array $list CSS file list
     *
     * @return array
     */
    protected function preparePagerCSSFiles($list)
    {
        return array_merge($list, self::getPager()->getCSSFiles());
    }

    /**
     * Return file name for the center part template
     *
     * @return string
     */
    protected function getBody()
    {
        // Static call of the non-static function
        return self::getDir() . LC_DS . $this->getBodyTemplate();
    }

    /**
     * Return default template
     * See setWidgetParams()
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * getPageBodyTemplate
     *
     * @return string
     */
    protected function getPageBodyTemplate()
    {
        return $this->getDir() . LC_DS . $this->getPageBodyDir() . LC_DS . $this->getPageBodyFile();
    }

    /**
     * getPageBodyFile
     *
     * @return string
     */
    protected function getPageBodyFile()
    {
        return 'body.tpl';
    }

    /**
     * getEmptyListTemplate
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return $this->getEmptyListDir() . LC_DS . $this->getEmptyListFile();
    }

    /**
     * Return "empty list" catalog
     *
     * @return string
     */
    protected function getEmptyListDir()
    {
        return self::getDir();
    }

    /**
     * getEmptyListFile
     *
     * @return string
     */
    protected function getEmptyListFile()
    {
        return 'empty.tpl';
    }

    /**
     * isEmptyListTemplateVisible
     *
     * @return boolean
     */
    protected function isEmptyListTemplateVisible()
    {
        return false === $this->hasResults();
    }

    /**
     * Get pager parameters list
     *
     * @return array
     */
    protected function getPagerParams()
    {
        return array(
            \XLite\View\Pager\APager::PARAM_ITEMS_COUNT => $this->getItemsCount(),
            \XLite\View\Pager\APager::PARAM_LIST        => $this,
        );
    }

    /**
     * Get pager
     *
     * @return \XLite\View\Pager\APager
     */
    protected function getPager()
    {
        if (!isset($this->pager)) {
            $this->pager = $this->getWidget($this->getPagerParams(), $this->getPagerClass());
        }

        return $this->pager;
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        return new \XLite\Core\CommonCell();
    }

    /**
     * Get page data
     *
     * @return array
     */
    protected function getPageData()
    {
        return $this->getData($this->getLimitCondition());
    }

    /**
     * Get limit condition
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getLimitCondition()
    {
        return $this->getPager()->getLimitCondition(null, null, $this->getSearchCondition());
    }

    /**
     * getSortOrderDefault
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        return static::SORT_ORDER_ASC;
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return null;
    }

    /**
     * Return 'Order by' array.
     * array(<Field to order>, <Sort direction>)
     *
     * @return array
     */
    protected function getOrderBy()
    {
        return array($this->getSortBy(), $this->getSortOrder());
    }

    /**
     * getSortBy
     *
     * @return string
     */
    protected function getSortBy()
    {
        $paramSortBy = $this->getParam(static::PARAM_SORT_BY);

        if (
            empty($paramSortBy)
            || !in_array($paramSortBy, array_keys($this->sortByModes))
        ) {
            $paramSortBy = $this->getSortByModeDefault();
        }

        return $paramSortBy;
    }

    /**
     * getSortOrder
     *
     * @return string
     */
    protected function getSortOrder()
    {
        $paramSortOrder = $this->getParam(static::PARAM_SORT_ORDER);

        if (
            empty($paramSortOrder)
            || !in_array(
                $paramSortOrder,
                array(static::SORT_ORDER_DESC, static::SORT_ORDER_ASC)
            )
        ) {
            $paramSortOrder = $this->getSortOrderModeDefault();
        }

        return $paramSortOrder;
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        if (!empty($this->sortByModes)) {
            $this->widgetParams += array(
                static::PARAM_SORT_BY => new \XLite\Model\WidgetParam\Set(
                    'Sort by', $this->getSortByModeDefault(), false, $this->sortByModes
                ),
                static::PARAM_SORT_ORDER => new \XLite\Model\WidgetParam\Set(
                    'Sort order', $this->getSortOrderModeDefault(), false, $this->sortOrderModes
                ),
            );
        }
    }

    /**
     * getJSHandlerClassName
     *
     * @return string
     */
    protected function getJSHandlerClassName()
    {
        return 'ItemsList';
    }

    /**
     * Get URL common parameters
     *
     * @return array
     */
    protected function getCommonParams()
    {
        if (!isset($this->commonParams)) {
            $this->commonParams = array(
                static::PARAM_SESSION_CELL => $this->getSessionCell()
            );
        }

        return $this->commonParams;
    }

    /**
     * Get AJAX-specific URL parameters
     *
     * @return array
     */
    protected function getAJAXSpecificParams()
    {
        return array(
            static::PARAM_AJAX_WIDGET => get_class($this),
            static::PARAM_AJAX_TARGET => \XLite\Core\Request::getInstance()->target,
        );
    }

    /**
     * getURLParams
     *
     * @return array
     */
    protected function getURLParams()
    {
        return array('target' => \XLite\Core\Request::getInstance()->target) + $this->getCommonParams();
    }

    /**
     * getURLAJAXParams
     *
     * @return array
     */
    protected function getURLAJAXParams()
    {
        return $this->getCommonParams() + $this->getAJAXSpecificParams();
    }

    /**
     * Return specific items list parameters that will be sent to JS code
     *
     * @return array
     */
    protected function getItemsListParams()
    {
        return array(
            'urlparams'     => $this->getURLParams(),
            'urlajaxparams' => $this->getURLAJAXParams(),
            'cell'          => $this->getSessionCell(),
        );
    }

    /**
     * Get sorting widget unique ID
     *
     * @param boolean $getLast Get last ID or next OPTIONAL
     *
     * @return string
     */
    protected function getSortWidgetId($getLast = false)
    {
        $class = get_called_class();

        if (!isset(static::$sortWidgetIds[$class])) {
            static::$sortWidgetIds[$class] = 0;
        }

        if (!$getLast) {
            static::$sortWidgetIds[$class]++;
        }

        return str_replace('\\', '-', $class) . '-sortby-' . static::$sortWidgetIds[$class];
    }

    /**
     * isSortByModeSelected
     *
     * @param string $sortByMode Value to check
     *
     * @return boolean
     */
    protected function isSortByModeSelected($sortByMode)
    {
        return $this->getSortBy() == $sortByMode;
    }

    /**
     * isSortOrderAsc
     *
     * @param string $sortOrder Sorting order
     *
     * @return boolean
     */
    protected function isSortOrderAsc($sortOrder = null)
    {
        return static::SORT_ORDER_ASC == ($sortOrder ?: $this->getSortOrder());
    }

    /**
     * getSortOrderToChange
     *
     * @param string $sortOrder Sorting order
     *
     * @return string
     */
    protected function getSortOrderToChange($sortOrder = null)
    {
        return $this->isSortOrderAsc($sortOrder) ? static::SORT_ORDER_DESC : static::SORT_ORDER_ASC;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && ($this->isDisplayWithEmptyList() || $this->hasResults());
    }

    /**
     * Auxiliary method to check visibility
     *
     * @return boolean
     */
    protected function isDisplayWithEmptyList()
    {
        return false;
    }

    /**
     * Public wrapper for hasResults method
     *
     * @return boolean
     */
    public function hasResultsPublic()
    {
        return $this->hasResults();
    }

    /**
     * Check if there are any results to display in list
     *
     * @return boolean
     */
    protected function hasResults()
    {
        return 0 < $this->getItemsCount();
    }

    /**
     * isHeaderVisible
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return false;
    }

    /**
     * Check if head title is visible
     *
     * @return boolean
     */
    protected function isHeadVisible()
    {
        return false;
    }

    /**
     * Check if pager is visible
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return $this->getPager()->isVisible();
    }

    /**
     * isFooterVisible
     *
     * @return boolean
     */
    protected function isFooterVisible()
    {
        return false;
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams[] = static::PARAM_SORT_BY;
        $this->requestParams[] = static::PARAM_SORT_ORDER;
    }

    /**
     * Get 'More' link URL
     *
     * @return string
     */
    public function getMoreLink()
    {
        return null;
    }

    /**
     * Get 'More' link title
     *
     * @return string
     */
    public function getMoreLinkTitle()
    {
        return null;
    }

}
