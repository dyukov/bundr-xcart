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

namespace XLite\Module\XC\Reviews\View\ItemsList\Model;

/**
 * Reviews items list (common reviews page)
 *
 */
class Review extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Allowed sort criterions
     */
    const SORT_BY_MODE_REVIEWER         = 'r.reviewerName';
    const SORT_BY_MODE_RATING           = 'r.rating';
    const SORT_BY_MODE_STATUS           = 'r.status';
    const SORT_BY_MODE_ADDITION_DATE    = 'r.additionDate';

    /**
     * Widget param names
     */
    const PARAM_SEARCH_DATE_RANGE   = 'dateRange';
    const PARAM_SEARCH_KEYWORDS     = 'keywords';
    const PARAM_SEARCH_RATING       = 'rating';

    /**
     * The product selector cache
     *
     * @var mixed
     */
    protected $productSelectorWidget = null;

    /**
     * The profile selector cache
     *
     * @var mixed
     */
    protected $profileSelectorWidget = null;

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            \XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_DATE_RANGE => static::PARAM_SEARCH_DATE_RANGE,
            \XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_KEYWORDS => static::PARAM_SEARCH_KEYWORDS,
            \XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_RATING => static::PARAM_SEARCH_RATING,
        );
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XC/Reviews/reviews/style.css';
        $list[] = 'modules/XC/Reviews/review/style.css';
        $list[] = 'vote_bar/vote_bar.css';
        $list[] = 'modules/XC/Reviews/form_field/input/rating/rating.css';

        $list = array_merge($list, $this->getProductSelectorWidget()->getCSSFiles());
        $list = array_merge($list, $this->getProfileSelectorWidget()->getCSSFiles());

        return $list;
    }

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/XC/Reviews/form_field/input/rating/rating.js';
        $list[] = 'modules/XC/Reviews/review/buttons.js';

        $list = array_merge($list, $this->getProductSelectorWidget()->getJSFiles());
        $list = array_merge($list, $this->getProfileSelectorWidget()->getJSFiles());

        return $list;
    }

    /**
     * Getter of the product selector widget
     *
     * @return \XLite\View\FormField\Select\Model\ProductSelector
     */
    protected function getProductSelectorWidget()
    {
        if (is_null($this->productSelectorWidget)) {
            $this->productSelectorWidget = new \XLite\View\FormField\Select\Model\ProductSelector();
        }

        return $this->productSelectorWidget;
    }

    /**
     * Getter of the product selector widget
     *
     * @return \XLite\View\FormField\Select\Model\ProductSelector
     */
    protected function getProfileSelectorWidget()
    {
        if (is_null($this->profileSelectorWidget)) {
            $this->profileSelectorWidget = new \XLite\View\FormField\Select\Model\ProfileSelector();
        }

        return $this->profileSelectorWidget;
    }

    /**
     * Return profile id
     *
     * @return integer
     */
    public function getProfileId(\XLite\Module\XC\Reviews\Model\Review $entity)
    {
        return $entity->getProfile()
            ? $entity->getProfile()->getProfileId()
            : 0;
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
        $this->sortByModes += array(
            static::SORT_BY_MODE_REVIEWER       => 'Reviewer',
            static::SORT_BY_MODE_RATING         => 'Rating',
            static::SORT_BY_MODE_STATUS         => 'Status',
            static::SORT_BY_MODE_ADDITION_DATE  => 'Addition date',
        );

        parent::__construct($params);
    }

    // {{{ Search

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
     * Get right actions templates name
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = parent::getRightActions();

        $list[] = 'modules/XC/Reviews/' . $this->getDir() . '/' . $this->getPageBodyDir() . '/review/action.link.tpl';

        return $list;
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        $result->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_ORDERBY} = $this->getOrderBy();

        // Comment this line to search reviews and ratings
        // $result->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_TYPE} =
        //    \XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_TYPE_REVIEWS_ONLY;

        return $result;
    }

    // }}}

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_SEARCH_DATE_RANGE => new \XLite\Model\WidgetParam\String('Date range', ''),
            static::PARAM_SEARCH_KEYWORDS => new \XLite\Model\WidgetParam\String('Product, SKU or customer info', ''),
            static::PARAM_SEARCH_RATING => new \XLite\Model\WidgetParam\String('Rating', ''),
        );

    }

    /**
     * Get column value for 'product' column
     *
     * @param \XLite\Module\XC\Reviews\Model\Review $entity Review
     *
     * @return string
     */
    protected function getProductColumnValue(\XLite\Module\XC\Reviews\Model\Review $entity)
    {
        return $entity->getProduct()->getName();
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'product' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Product'),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_LINK     => 'product',
                static::COLUMN_ORDERBY  => 100,
            ),
            'reviewerName' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Reviewer'),
                static::COLUMN_TEMPLATE => 'modules/XC/Reviews/reviews/cell/reviewer_info.tpl',
                static::COLUMN_MAIN     => true,
                static::COLUMN_SORT     => static::SORT_BY_MODE_REVIEWER,
                static::COLUMN_ORDERBY  => 200,
            ),
            'rating' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Rating'),
                static::COLUMN_TEMPLATE => 'modules/XC/Reviews/reviews/cell/rating.tpl',
                static::COLUMN_SORT     => static::SORT_BY_MODE_RATING,
                static::COLUMN_ORDERBY  => 300,
            ),
            'status' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Status'),
                static::COLUMN_TEMPLATE => 'modules/XC/Reviews/reviews/cell/status.tpl',
                static::COLUMN_SORT     => static::SORT_BY_MODE_STATUS,
                static::COLUMN_ORDERBY  => 400,
            ),
            'additionDate' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Date'),
                static::COLUMN_SORT     => static::SORT_BY_MODE_ADDITION_DATE,
                static::COLUMN_ORDERBY  => 500,
            ),
        );
    }

    /**
     * Preprocess addition date
     *
     * @param integer                               $date   Date
     * @param array                                 $column Column data
     * @param \XLite\Module\XC\Reviews\Model\Review $entity Review
     *
     * @return string
     */
    protected function preprocessAdditionDate($date, array $column, \XLite\Module\XC\Reviews\Model\Review $entity)
    {
        return $date
            ? \XLite\Core\Converter::getInstance()->formatTime($date)
            : static::t('Unknown');
    }

    /**
     * Return true if review is approved
     *
     * @param \XLite\Module\XC\Reviews\Model\Review $entity Review
     *
     * @return boolean
     */
    protected function isApproved(\XLite\Module\XC\Reviews\Model\Review $entity)
    {
        return \XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED == $entity->getStatus();
    }

    /**
     * isFooterVisible
     *
     * @return boolean
     */
    protected function isFooterVisible()
    {
        return true;
    }

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
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' reviews';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\XC\Reviews\View\StickyPanel\ItemsList\Review';
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return 'XLite\View\Pager\Admin\Model\Table';
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\XC\Reviews\Model\Review';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildURL('review');
    }

    /**
     * Build entity page URL
     *
     * @param \XLite\Model\AEntity $entity Entity
     * @param array                $column Column data
     *
     * @return string
     */
    protected function buildEntityURL(\XLite\Model\AEntity $entity, array $column)
    {
        if ('product' == $column[static::COLUMN_CODE]) {
            $result = \XLite\Core\Converter::buildURL(
                'product',
                '',
                array('product_id' => $entity->getProduct()->getProductId())
            );
        } else {
            $result = parent::buildEntityURL($entity, $column);
        }

        return $result;
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
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_MODE_ADDITION_DATE;
    }

    /**
     * getSortOrderDefault
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        return \XLite\View\ItemsList\AItemsList::SORT_ORDER_DESC;
    }

    /**
     * Check - table header is visible or not
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return true;
    }
}
