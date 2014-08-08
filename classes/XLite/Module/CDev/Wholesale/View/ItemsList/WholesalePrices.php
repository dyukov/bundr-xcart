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

namespace XLite\Module\CDev\Wholesale\View\ItemsList;

/**
 * Wholesale prices items list
 */
class WholesalePrices extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Product object releated to the wholesale prices itemsList
     *
     * @var   \XLite\Model\Product
     */
    protected $product = null;


    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/Wholesale/pricing/prices/style.css';

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
            'quantityRangeBegin' => array(
                static::COLUMN_NAME         => \XLite\Core\Translation::lbl('Quantity range'),
                static::COLUMN_CLASS        => 'XLite\Module\CDev\Wholesale\View\FormField\QuantityRangeBegin',
                static::COLUMN_ORDERBY  => 100,
            ),
            'price' => array(
                static::COLUMN_NAME         => \XLite\Core\Translation::lbl('Price'),
                static::COLUMN_CLASS        => 'XLite\Module\CDev\Wholesale\View\FormField\Price',
                static::COLUMN_ORDERBY  => 200,
            ),
            'membership' => array(
                static::COLUMN_NAME         => \XLite\Core\Translation::lbl('Membership'),
                static::COLUMN_CLASS        => 'XLite\Module\CDev\Wholesale\View\FormField\Membership',
                static::COLUMN_ORDERBY  => 300,
            ),
        );
    }

    /**
     * getRightActions
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = parent::getRightActions();

        foreach ($list as $k => $v) {
            if ('items_list/model/table/parts/remove.tpl' == $v) {
                $list[$k] = 'modules/CDev/Wholesale/pricing/prices/remove.tpl';
            }
        }

        return $list;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\CDev\Wholesale\Model\WholesalePrice';
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New tier';
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isDisplayWithEmptyList()
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
        return false;
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
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
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
        return array('wholesalePrices');
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' wholesale-prices';
    }

    /**
     * createEntity
     *
     * @return \XLite\Model\Product
     */
    protected function createEntity()
    {
        $entity = parent::createEntity();

        $entity->product = $this->getProduct();

        return $entity;
    }

    /**
     * getProduct
     *
     * @return \XLite\Model\Product
     */
    protected function getProduct()
    {
        if (!isset($this->product)) {
            $this->product = parent::getProduct();
        }

        return $this->product;
    }

    /**
     * Return true if entity is removable
     *
     * @param \XLite\Module\CDev\Wholesale\Model\WholesalePrice $entity Wholesale price object
     *
     * @return boolean
     */
    protected function isRemovableEntity($entity)
    {
        return !$entity->isDefaultPrice();
    }

    // {{{ Data

    /**
     * Return wholesale prices
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        // Update default wholesale price (for 1 item and all customers)
        \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->updateDefaultWholesalePrice($this->getProduct());

        // Search wholesale prices to display in the items list
        $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_PRODUCT} = $this->getProduct()->getProductId();
        $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_ORDER_BY_MEMBERSHIP} = true;
        $cnd->{\XLite\Module\CDev\Wholesale\Model\Repo\WholesalePrice::P_ORDER_BY}
            = array('w.quantityRangeBegin', 'ASC');

        return \XLite\Core\Database::getRepo('XLite\Module\CDev\Wholesale\Model\WholesalePrice')
            ->search($cnd, $countOnly);
    }

    // }}}

    /**
     * Get URL common parameters
     *
     * @return array
     */
    protected function getCommonParams()
    {
        $this->commonParams = parent::getCommonParams();
        $this->commonParams['product_id'] = \XLite\Core\Request::getInstance()->product_id;
        $this->commonParams['page'] = 'wholesale_pricing';

        return $this->commonParams;
    }

    // }}}
}
