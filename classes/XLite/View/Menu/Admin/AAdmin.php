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

namespace XLite\View\Menu\Admin;

/**
 * Abstract admin menu
 */
abstract class AAdmin extends \XLite\View\Menu\AMenu
{
   /**
     * Item parameter names
     */
    const ITEM_TITLE         = 'title';
    const ITEM_LINK          = 'link';
    const ITEM_BLOCK         = 'block';
    const ITEM_LIST          = 'list';
    const ITEM_CLASS         = 'className';
    const ITEM_ICON_CLASS    = 'iconClass';
    const ITEM_TARGET        = 'linkTarget';
    const ITEM_EXTRA         = 'extra';
    const ITEM_PERMISSION    = 'permission';
    const ITEM_PUBLIC_ACCESS = 'publicAccess';
    const ITEM_CHILDREN      = 'children';
    const ITEM_WEIGHT        = 'weight';
    const ITEM_WIDGET        = 'widget';
    const ITEM_BLANK_PAGE    = 'blankPage';

    /**
     * Array of targets related to the same menu link
     *
     * @var array
     */
    protected $relatedTargets = array(
        'orders_stats' => array(
            'top_sellers',
        ),
        'order_list' => array(
            'order',
        ),
        'product_list' => array(
            'product',
        ),
        'categories' => array(
            'category',
            'category_products',
        ),
        'profile_list' => array(
            'profile',
            'address_book',
        ),
        'shipping_methods' => array(
            'shipping_rates',
        ),
        'countries' => array(
            'zones',
            'states',
        ),
        'payment_settings' => array(
            'payment_method',
            'payment_appearance',
        ),
        'db_backup' => array(
            'db_restore',
        ),
        'product_classes' => array(
            'product_class',
            'attributes',
        ),
        'tax_classes' => array(
            'tax_class',
        ),
        'units_formats' => array(
            'currency',
        ),
        'languages' => array(
            'labels',
        ),
        'general_settings' => array(
            'shipping_settings',
            'address_fields'
        ),
        'css_js_performance' => array(
            'css_js_performance',
        ),
    );

    /**
     * Selected item
     *
     * @var array
     */
    protected $selectedItem = array();

    /**
     * Sort items
     *
     * @param array $item1 Item 1
     * @param array $item2 Item 2
     *
     * @return boolean
     */
    protected function sortItems($item1, $item2)
    {
        return isset($item1[self::ITEM_WEIGHT])
            && isset($item2[self::ITEM_WEIGHT])
            && $item1[self::ITEM_WEIGHT] > $item2[self::ITEM_WEIGHT];
    }

    /**
     * Mark selected
     *
     * @param array $items Items
     *
     * @return array
     */
    protected function markSelected($items)
    {
        if (
            !empty($this->selectedItem)
            && $items
        ){
            foreach ($items as $index => $item) {
                if ($index == $this->selectedItem['index']) {
                    $items[$index]->setWidgetParams(
                        array(
                            \XLite\View\Menu\Admin\TopMenu\Node::PARAM_SELECTED => true
                        )
                    );
                    break;

                } elseif ($item->getParam(self::ITEM_CHILDREN)) {
                    $items[$index]->setWidgetParams(
                        array(
                            self::ITEM_CHILDREN => $this->markSelected($item->getParam(self::ITEM_CHILDREN))
                        )
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Prepare items
     *
     * @param array $items Items
     *
     * @return array
     */
    protected function prepareItems($items)
    {
        uasort($items, array($this, 'sortItems'));
        foreach ($items as $index => $item) {
            if (
                isset($item[self::ITEM_CHILDREN])
                && is_array($item[self::ITEM_CHILDREN])
                && !empty($item[self::ITEM_CHILDREN])
            ) {
                $item[self::ITEM_CHILDREN] = $this->prepareItems($item[self::ITEM_CHILDREN]);
                $item[\XLite\View\Menu\Admin\TopMenu\Node::PARAM_LIST] = $index;
            } else {
                $item[self::ITEM_CHILDREN] = array();
            }

            $item[\XLite\View\Menu\Admin\TopMenu\Node::PARAM_TITLE] = static::t($item[self::ITEM_TITLE]);

            if (
                isset($item[self::ITEM_TARGET])
                && in_array($this->getTarget(), $this->getRelatedTargets($item[self::ITEM_TARGET]))
            ) {
                $selected = true;
                $weight = 1;

                if (
                    isset($item[self::ITEM_EXTRA])
                    && $item[self::ITEM_EXTRA]
                    && is_array($item[self::ITEM_EXTRA])
                ) {
                    foreach ($item[self::ITEM_EXTRA] as $k => $v) {
                        if ($v == \XLite\Core\Request::getInstance()->$k) {
                            $weight++;
                        } else {
                            $selected = false;
                            break;
                        }
                    }
                }

                if (
                    $selected
                    && (
                        empty($this->selectedItem)
                        || $weight > $this->selectedItem['weight']
                    )
                ) {
                    $this->selectedItem = array(
                        'weight' => $weight,
                        'index'  => $index,
                    );
                }
            }

            $items[$index] = $this->getWidget(
                $item,
                isset($item[self::ITEM_WIDGET]) ? $item[self::ITEM_WIDGET] : $this->getDefaultWidget()
            );

            if (
                !$items[$index]->checkACL()
                || !$items[$index]->isVisible()
            ) {
                unset($items[$index]);
            }
        }

        return $items;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return \XLite\Core\Auth::getInstance()->isAdmin()
            && parent::isVisible();
    }

    /**
     * Returns the list of related targets
     *
     * @param string $target Target name
     *
     * @return array
     */
    public function getRelatedTargets($target)
    {
        return isset($this->relatedTargets[$target])
            ? array_merge(array($target), $this->relatedTargets[$target])
            : array($target);
    }
}
