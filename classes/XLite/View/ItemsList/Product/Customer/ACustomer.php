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

namespace XLite\View\ItemsList\Product\Customer;

/**
 * ACustomer
 */
abstract class ACustomer extends \XLite\View\ItemsList\Product\AProduct
{
    /**
     * Widget param names
     */
    const PARAM_WIDGET_TYPE  = 'widgetType';
    const PARAM_DISPLAY_MODE = 'displayMode';
    const PARAM_GRID_COLUMNS = 'gridColumns';

    const PARAM_SHOW_DISPLAY_MODE_SELECTOR = 'showDisplayModeSelector';
    const PARAM_SHOW_SORT_BY_SELECTOR      = 'showSortBySelector';

    const PARAM_ICON_MAX_WIDTH = 'iconWidth';
    const PARAM_ICON_MAX_HEIGHT = 'iconHeight';

    /**
     * Allowed widget types
     */
    const WIDGET_TYPE_SIDEBAR = 'sidebar';
    const WIDGET_TYPE_CENTER  = 'center';

    /**
     * Allowed display modes
     */
    const DISPLAY_MODE_LIST    = 'list';
    const DISPLAY_MODE_GRID    = 'grid';
    const DISPLAY_MODE_TABLE   = 'table';
    const DISPLAY_MODE_ROTATOR = 'rotator';

    const DISPLAY_MODE_STHUMB = 'small_thumbnails';
    const DISPLAY_MODE_BTHUMB = 'big_thumbnails';
    const DISPLAY_MODE_TEXTS  = 'text_links';


    /**
     * A special option meaning that a CSS layout is to be used
     */
    const DISPLAY_GRID_CSS_LAYOUT = 'css-defined';

    /**
     * Columns number range
     */
    const GRID_COLUMNS_MIN = 1;
    const GRID_COLUMNS_MAX = 5;

    /**
     * Template to use for sidebars
     */
    const TEMPLATE_SIDEBAR = 'common/sidebar_box.tpl';

    /**
     * Cache flag if the product options must be chosen first
     *
     * @var boolean
     */
    protected static $forceChooseOptions = false;

    /**
     * Widget types
     *
     * @var array
     */
    protected $widgetTypes = array(
        self::WIDGET_TYPE_SIDEBAR  => 'Sidebar',
        self::WIDGET_TYPE_CENTER   => 'Center',
    );


    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = static::getWidgetTarget();

        return $result;
    }

    /**
     * Get display modes for sidebar widget type
     *
     * @return array
     */
    public static function getSidebarDisplayModes()
    {
        return array(
            static::DISPLAY_MODE_STHUMB  => 'Cells',
            static::DISPLAY_MODE_BTHUMB  => 'List',
            static::DISPLAY_MODE_TEXTS   => 'Text links',
        );
    }

    /**
     * Get display modes for center widget type
     *
     * @return array
     */
    public static function getCenterDisplayModes()
    {
        return array(
            static::DISPLAY_MODE_GRID  => 'Grid',
            static::DISPLAY_MODE_LIST  => 'List',
            static::DISPLAY_MODE_TABLE => 'Table',
        );
    }

    /**
     * Get icon sizes
     *
     * @return array
     */
    public static function getIconSizes()
    {
        return array(
            static::WIDGET_TYPE_SIDEBAR . '.' . static::DISPLAY_MODE_STHUMB => array(160, 160),
            static::WIDGET_TYPE_SIDEBAR . '.' . static::DISPLAY_MODE_BTHUMB => array(160, 160),
            static::WIDGET_TYPE_CENTER . '.' . static::DISPLAY_MODE_GRID => array(160, 160),
            static::WIDGET_TYPE_CENTER . '.' . static::DISPLAY_MODE_LIST => array(160, 160),
            'other' => array(110, 110),
        );
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
        static::$forceChooseOptions = \XLite\Core\Config::getInstance()->General->force_choose_product_options;
        $this->sortByModes = array(
            static::SORT_BY_MODE_PRICE  => 'Price-sort-option',
            static::SORT_BY_MODE_NAME   => 'Name-sort-option',
        );
    }

    /**
     * Get products 'sort by' fields
     *
     * @return array
     */
    protected function getSortByFields()
    {
        return array(
            'price'  => static::SORT_BY_MODE_PRICE,
            'name'   => static::SORT_BY_MODE_NAME,
            'sku'    => static::SORT_BY_MODE_SKU,
            'amount' => static::SORT_BY_MODE_AMOUNT,
        );
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
        $result = '';
        if ($this->isSortByModeSelected($sortBy)) {
            $result = static::SORT_ORDER_DESC === $this->getSortOrder()
                ? 'sort-arrow-desc'
                : 'sort-arrow-asc';
        }

        return $result;
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
        return $this->isSortByModeSelected($sortOrder ?: $this->getSortOrder())
            ? parent::getSortOrderToChange()
            : static::SORT_ORDER_ASC;
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

        // Modify display modes and default display mode
        $allOptions = array_merge(static::getSidebarDisplayModes(), static::getCenterDisplayModes());

        $this->widgetParams[static::PARAM_DISPLAY_MODE]->setOptions($allOptions);

        $options = $this->getDisplayModes();

        if (!isset($options[$this->getParam(static::PARAM_DISPLAY_MODE)])) {
            $this->widgetParams[static::PARAM_DISPLAY_MODE]->setValue(
                $this->isSidebar()
                    ? static::DISPLAY_MODE_STHUMB
                    : static::DISPLAY_MODE_GRID
            );
        }

        if (
            !isset($params[static::PARAM_ICON_MAX_WIDTH])
            && !isset($params[static::PARAM_ICON_MAX_HEIGHT])
            && 0 == $this->getParam(static::PARAM_ICON_MAX_WIDTH)
            && 0 == $this->getParam(static::PARAM_ICON_MAX_HEIGHT)
        ) {
            $sizes = static::getIconSizes();
            $key = $this->getParam(static::PARAM_WIDGET_TYPE) . '.' . $this->getParam(static::PARAM_DISPLAY_MODE);
            $size = isset($sizes[$key]) ? $sizes[$key] : $sizes['other'];

            $this->widgetParams[static::PARAM_ICON_MAX_WIDTH]->setValue($size[0]);
            $this->widgetParams[static::PARAM_ICON_MAX_HEIGHT]->setValue($size[1]);
        }

        // FIXME - not a good idea, but I don't see a better way
        if ($this->isWrapper() && $this->checkSideBarParams($params)) {
            $this->defaultTemplate = static::TEMPLATE_SIDEBAR;
            $this->widgetParams[static::PARAM_TEMPLATE]->setValue($this->getDefaultTemplate());
        }
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        if ($this->isQuickLookEnabled()) {
            $list[] = $this->getDir() . '/quick_look.css';
        }

        //TODO: Remove after CSS loading feature is added.
        $list[] = 'labels/style.css';

        return array_merge($list, $this->getPopupCSS());
    }

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        return array_merge(parent::getJSFiles(), $this->getPopupJS());
    }

    /**
     * Return class attribute for the product cell
     *
     * @param \XLite\Model\Product $product The product to look for
     *
     * @return string
     */
    public function getProductCellClass($product)
    {
        return 'product productid-'
            . $product->getProductId()
            . ($this->isProductAdded($product) ? ' product-added' : '')
            . ($product->isOutOfStock() ? ' out-of-stock' : '')
            . ($product->isAvailable() ? '' : ' not-available')
            . (static::$forceChooseOptions && $product->hasEditableAttributes() ? ' need-choose-options' : '');
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return null;
    }

    /**
     * Return name of the base widgets list
     *
     * @return string
     */
    protected function getListName()
    {
        return parent::getListName() . '.customer';
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
            static::PARAM_WIDGET_TYPE => new \XLite\Model\WidgetParam\Set(
                'Widget type', static::WIDGET_TYPE_CENTER, true, $this->widgetTypes
            ),
            static::PARAM_DISPLAY_MODE => new \XLite\Model\WidgetParam\Set(
                'Display mode', static::DISPLAY_MODE_GRID, true, array()
            ),
            static::PARAM_SHOW_DISPLAY_MODE_SELECTOR => new \XLite\Model\WidgetParam\Checkbox(
                'Show "Display mode" selector', true, true
            ),
            static::PARAM_SHOW_SORT_BY_SELECTOR => new \XLite\Model\WidgetParam\Checkbox(
                'Show "Sort by" selector', true, true
            ),
            static::PARAM_GRID_COLUMNS => new \XLite\Model\WidgetParam\Set(
                'Number of columns (for Grid mode only)', 3, true, $this->getGridColumnsRange()
            ),
            static::PARAM_ICON_MAX_WIDTH => new \XLite\Model\WidgetParam\Int(
                'Maximal icon width', 0, true
            ),
            static::PARAM_ICON_MAX_HEIGHT => new \XLite\Model\WidgetParam\Int(
                'Maximal icon height', 0, true
            ),
        );
    }

    /**
     * Get display modes
     *
     * @return void
     */
    protected function getDisplayModes()
    {
        return $this->isSidebar()
            ? static::getSidebarDisplayModes()
            : static::getCenterDisplayModes();
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

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams[] = static::PARAM_DISPLAY_MODE;
    }

    /**
     * checkSideBarParams
     *
     * @param array $params Params to check
     *
     * @return boolean
     */
    protected function checkSideBarParams(array $params)
    {
        return isset($params[static::PARAM_WIDGET_TYPE]) && $this->isSidebar();
    }

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    protected function getPageBodyDir()
    {
        return $this->getParam(static::PARAM_WIDGET_TYPE) . '/' . parent::getPageBodyDir();
    }

    /**
     * Check - current widget type is sidebar
     *
     * @return boolean
     */
    protected function isSidebar()
    {
        return static::WIDGET_TYPE_SIDEBAR == $this->getParam(static::PARAM_WIDGET_TYPE);
    }

    /**
     * Check if pager control row is visible or not
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return parent::isPagerVisible()
            && !$this->isSidebar()
            && $this->getParam(\XLite\View\Pager\APager::PARAM_SHOW_ITEMS_PER_PAGE_SELECTOR);
    }

    /**
     * isDisplayModeSelectorVisible
     *
     * @return boolean
     */
    protected function isDisplayModeSelectorVisible()
    {
        return $this->getParam(static::PARAM_SHOW_DISPLAY_MODE_SELECTOR) && !$this->isSidebar();
    }

    /**
     * isSortBySelectorVisible
     *
     * @return boolean
     */
    protected function isSortBySelectorVisible()
    {
        return $this->getParam(static::PARAM_SHOW_SORT_BY_SELECTOR) && !$this->isSidebar();
    }

    /**
     * isHeaderVisible
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return $this->isDisplayModeSelectorVisible() || $this->isSortBySelectorVisible();
    }

    /**
     * getDisplayMode
     *
     * @return string
     */
    protected function getDisplayMode()
    {
        return $this->getParam(static::PARAM_DISPLAY_MODE);
    }

    /**
     * isDisplayModeSelected
     *
     * @param string $displayMode Value to check
     *
     * @return boolean
     */
    protected function isDisplayModeSelected($displayMode)
    {
        return $this->getParam(static::PARAM_DISPLAY_MODE) == $displayMode;
    }

    /**
     * Get display mode link class name
     * TODO - simplify
     *
     * @param string $displayMode Display mode
     *
     * @return string
     */
    protected function getDisplayModeLinkClassName($displayMode)
    {
        $classes = array(
            'list-type-' . $displayMode
        );

        if ('grid' == $displayMode) {
            $classes[] = 'first';
        }

        if ('table' == $displayMode) {
            $classes[] = 'last';
        }

        if ($this->isDisplayModeSelected($displayMode)) {
            $classes[] = 'selected';
        }

        return implode(' ', $classes);
    }

    /**
     * Get display mode link class name
     * TODO - simplify
     *
     * @param string $displayMode Display mode
     *
     * @return string
     */
    protected function getDisplayModeCSS($displayMode)
    {
        $classes = array(
            $displayMode
        );

        switch ($displayMode) {
            case static::DISPLAY_MODE_GRID:
                $fa = 'fa-th';
                break;

            case static::DISPLAY_MODE_LIST:
                $fa = 'fa-list-ul';
                break;

            case static::DISPLAY_MODE_TABLE:
                $fa = 'fa-list-alt';

            default:
                break;
        }

        $classes[] = $fa;

        return implode(' ', $classes);
    }

    /**
     * Return products split into rows
     *
     * @return array
     */
    protected function getProductRows()
    {
        $data = $this->getPageData();
        $rows = array();

        if (!empty($data)) {
            $rows = array_chunk($data, $this->getParam(static::PARAM_GRID_COLUMNS));
            $last = count($rows) - 1;
            $rows[$last] = array_pad($rows[$last], $this->getParam(static::PARAM_GRID_COLUMNS), false);
        }

        return $rows;
    }

    /**
     * Get grid columns range
     *
     * @return array
     */
    protected function getGridColumnsRange()
    {
        $range = array_merge(
            array(static::DISPLAY_GRID_CSS_LAYOUT => static::DISPLAY_GRID_CSS_LAYOUT),
            range(static::GRID_COLUMNS_MIN, static::GRID_COLUMNS_MAX)
        );

        return array_combine($range, $range);
    }

    /**
     * Check whether a CSS layout should be used for "Grid" mode
     *
     * @return void
     */
    protected function isCSSLayout()
    {
        return ($this->getParam(static::PARAM_DISPLAY_MODE) == static::DISPLAY_MODE_GRID);
    }

    /**
     * getPageBodyFile
     *
     * @return string
     */
    protected function getPageBodyFile()
    {
        if (
            $this->getParam(static::PARAM_WIDGET_TYPE) == static::WIDGET_TYPE_CENTER
            && $this->getParam(static::PARAM_DISPLAY_MODE) == static::DISPLAY_MODE_GRID
        ) {
            return $this->isCSSLayout() ? 'body-css-layout.tpl' : 'body-table-layout.tpl';
        } else {
            return parent::getPageBodyFile();
        }
    }

    /**
     * getSidebarMaxItems
     *
     * @return integer
     */
    protected function getSidebarMaxItems()
    {
        return $this->getParam(\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE);
    }

    /**
     * Get products list for sidebar widget
     *
     * @return void
     */
    protected function getSideBarData()
    {
        return $this->getData($this->getPager()->getLimitCondition(0, $this->getSidebarMaxItems()));
    }

    /**
     * Get additional list item class
     *
     * @param integer $i     Item index
     * @param integer $count List length
     *
     * @return string
     */
    protected function getAdditionalItemClass($i, $count)
    {
        $classes = array();

        if (1 == $i) {
            $classes[] = 'first';
        }

        if ($count == $i) {
            $classes[] = 'last';
        }

        if (0 == $i % 2) {
            $classes[] = 'odd';
        }

        return implode(' ', $classes);
    }

    /**
     * Get grid item width (percent)
     *
     * @return integer
     */
    protected function getGridItemWidth()
    {
        return floor(100 / $this->getParam(static::PARAM_GRID_COLUMNS)) - 6;
    }

    /**
     * Return the maximal icon width
     *
     * @return integer
     */
    protected function getIconWidth()
    {
        return $this->getParam(static::PARAM_ICON_MAX_WIDTH);
    }

    /**
     * Return the maximal icon height
     *
     * @return integer
     */
    protected function getIconHeight()
    {
        return $this->getParam(static::PARAM_ICON_MAX_HEIGHT);
    }

    /**
     * Return the icon 'alt' value
     *
     * @param \XLite\Model\Product $product Current product
     *
     * @return string
     */
    protected function getIconAlt($product)
    {
        return $product->getImage() && $product->getImage()->getAlt()
            ? $product->getImage()->getAlt()
            : $product->getName();
    }

    /**
     * Get table columns count
     *
     * @return integer
     */
    protected function getTableColumnsCount()
    {
        return 3 + ($this->isShowAdd2Cart() ? 1 : 0);
    }

    /**
     * Check status of 'More...' link for sidebar list
     *
     * @return boolean
     */
    protected function isShowMoreLink()
    {
        return false;
    }

    /**
     * Get 'More...' link URL for sidebar list
     *
     * @return string
     */
    protected function getMoreLinkURL()
    {
        return null;
    }

    /**
     * Get 'More...' link text for sidebar list
     *
     * @return string
     */
    protected function getMoreLinkText()
    {
        return 'More...';
    }

    /**
     * Prepare CSS files needed for popups
     * TODO: check if there is a more convinient way to do that
     *
     * @return array
     */
    protected function getPopupCSS()
    {
        return array_merge(
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Page\QuickLook')->getCSSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Image')->getCSSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Gallery')->getCSSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\QuantityBox')->getCSSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\AttributesModify')->getCSSFiles()
        );
    }

    /**
     * Prepare JS files needed for popups
     * TODO: check if there is a more convinient way to do that
     *
     * @return array
     */
    protected function getPopupJS()
    {
        return array_merge(
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Page\QuickLook')->getJSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Image')->getJSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\Gallery')->getJSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\QuantityBox')->getJSFiles(),
            $this->getWidget(array(), '\XLite\View\Product\Details\Customer\AttributesModify')->getJSFiles()
        );
    }

    /**
     * Checks whether a product was added to the cart
     *
     * @param \XLite\Model\Product $product The product to look for
     *
     * @return boolean
     */
    protected function isProductAdded(\XLite\Model\Product $product)
    {
        return $this->getCart()->isProductAdded($product->getProductId());
    }

    /**
     * Get product labels
     *
     * @param \XLite\Model\Product $product Product
     *
     * @return array
     */
    protected function getLabels(\XLite\Model\Product $product)
    {
        return array();
    }

    /**
     * Return true if quick-look is enabled on the items list
     *
     * @return boolean
     */
    protected function isQuickLookEnabled()
    {
        return true;
    }

    /**
     * Return true if 'Add to cart' buttons shoud be displayed on the list items
     *
     * @return boolean
     */
    protected function isDisplayAdd2CartButton()
    {
        return $this->getDisplayMode() != static::DISPLAY_MODE_GRID
            || \XLite\Core\Config::getInstance()->General->enable_add2cart_button_grid;
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

        $auth = \XLite\Core\Auth::getInstance();
        if ($auth->isLogged()) {
            $profile = $auth->getProfile();
            // Membership as a parameter (wholesale and other prices)
            $list[] = $profile->getMembership()
                ? $profile->getMembership()->getMembershipId()
                : '-';

            // Shipping address zone as a parameter (taxes amount)
            foreach (\XLite\Core\Database::getRepo('XLite\Model\Zone')
                ->findApplicableZones(\XLite\Model\Shipping::prepareAddressData($profile->getShippingAddress())) as $zone) {
                $list[] = $zone->getZoneId();
            }
        }

        // If the cart is changed the widget content must be recalculated
        $list[] = $this->getCart()->getItemsFingerprint();

        // If some of the registered widget/request parameters are changed
        // the widget content must be recalculated
        foreach($this->defineCachedParams() as $name) {
            $list[] = $this->getRequestParamValue($name)
                ?: (($widgetParam = $this->getWidgetParams($name)) ? $widgetParam->value : '');
        }

        return $list;
    }

    /**
     * Register the widget/request parameters that will be used as the widget cache parameters.
     * In other words changing these parameters by customer effects on widget content
     *
     * @return array
     */
    protected function defineCachedParams()
    {
        return array(
            \XLite\View\Pager\APager::PARAM_PAGE_ID,
            \XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE,
            static::PARAM_WIDGET_TYPE,
            static::PARAM_DISPLAY_MODE,
            static::PARAM_SORT_BY,
            static::PARAM_SORT_ORDER,
        );
    }

    /**
     * Check if the product list in the default design
     * It is used to check the cache state.
     * The core defines "static" state for product list if there is an empty cart
     *
     * @return boolean
     */
    protected function isStaticProductList()
    {
        return $this->getCart()->isEmpty();
    }

    // }}}

}
