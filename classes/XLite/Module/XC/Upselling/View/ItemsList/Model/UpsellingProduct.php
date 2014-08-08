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

namespace XLite\Module\XC\Upselling\View\ItemsList\Model;

/**
 * U products items list
 */
class UpsellingProduct extends \XLite\View\ItemsList\Model\Table
{
    const PARAM_PARENT_PRODUCT_ID = 'product_id';
    const PARAM_PRODUCT_ID        = 'product_id';

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'modules/XC/Upselling/u_products/style.css';

        return $list;
    }

    /**
     * Get top actions
     *
     * @return array
     */
    protected function getTopActions()
    {
        $actions = parent::getTopActions();
        $actions[] = 'modules/XC/Upselling/u_products/parts/create.tpl';

        return $actions;
    }

    /**
     * Define the URL for popup product selector
     *
     * @return string
     */
    protected function getRedirectURL()
    {
        return $this->buildURL(
            'upselling_products',
            'add',
            array(
                'parent_product_id' => $this->getParentProductId(),
            )
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
                static::COLUMN_NAME => \XLite\Core\Translation::lbl('SKU'),
                static::COLUMN_NO_WRAP => true,
                static::COLUMN_ORDERBY  => 100,
            ),
            'product' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Product'),
                static::COLUMN_TEMPLATE => 'modules/XC/Upselling/u_products/parts/info.product.tpl',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_MAIN     => true,
                static::COLUMN_ORDERBY  => 200,
            ),
            'price' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Price'),
                static::COLUMN_TEMPLATE => 'modules/XC/Upselling/u_products/parts/info.price.tpl',
                static::COLUMN_ORDERBY  => 300,
            ),
            'amount' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Stock'),
                static::COLUMN_ORDERBY  => 400,
            ),
            'bidirectional' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Mutual link'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Input\Checkbox\Simple',
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_ORDERBY  => 500,
            ),
        );
    }

    /**
     * The product column displays the product name
     *
     * @param \XLite\Model\Product $product Product info
     *
     * @return string
     */
    protected function preprocessProduct(\XLite\Model\Product $product)
    {
        return $product->getName();
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\XC\Upselling\Model\UpsellingProduct';
    }

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
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
    }

    // }}}

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_PARENT_PRODUCT_ID => new \XLite\Model\WidgetParam\Int(
                'parent product ID ', $this->getParentProductId(), false
            ),
        );
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' u_products';
    }

    /**
     * Check - sticky panel is visible or not
     *
     * @return boolean
     */
    protected function isPanelVisible()
    {
        return true;
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\Module\XC\Upselling\View\StickyPanel\ItemsList\UpsellingProduct';
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
            \XLite\Module\XC\Upselling\Model\Repo\UpsellingProduct::SEARCH_PARENT_PRODUCT_ID => static::PARAM_PARENT_PRODUCT_ID,
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

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        return $result;
    }

    /**
     * Get URL common parameters
     *
     * @return array
     */
    protected function getCommonParams()
    {
        $this->commonParams = parent::getCommonParams();
        $this->commonParams[static::PARAM_PRODUCT_ID] = \XLite\Core\Request::getInstance()->product_id;
        $this->commonParams['page'] = 'upselling_products';

        return $this->commonParams;
    }

    // }}}

}
