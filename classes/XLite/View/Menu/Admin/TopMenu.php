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

namespace XLite\View\Menu\Admin;

/**
 * Top menu widget
 */
class TopMenu extends \XLite\View\Menu\Admin\AAdmin
{

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/menu.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/controller.js';

        return $list;
    }

    /**
     * Define items
     *
     * @return array
     */
    protected function defineItems()
    {
        $items = array(
            'home' => array(
                self::ITEM_TITLE      => 'Home',
                self::ITEM_TARGET     => 'main',
                self::ITEM_CLASS      => 'home',
                self::ITEM_WEIGHT     => 1,
                self::ITEM_PUBLIC_ACCESS => true,
            ),
            'sales' => array(
                self::ITEM_TITLE      => 'Orders',
                self::ITEM_TARGET     => 'recent_orders',
                self::ITEM_WEIGHT     => 100,
                self::ITEM_CHILDREN   => array(
                    'recent_orders' => array(
                        self::ITEM_TITLE      => 'Recent orders',
                        self::ITEM_TARGET     => 'recent_orders',
                        self::ITEM_PERMISSION => 'manage orders',
                        self::ITEM_WEIGHT     => 100,
                    ),
                    'order_list' => array(
                        self::ITEM_TITLE      => 'Search for orders',
                        self::ITEM_TARGET     => 'order_list',
                        self::ITEM_PERMISSION => 'manage orders',
                        self::ITEM_WEIGHT     => 200,
                    ),
                    'orders_stats' => array(
                        self::ITEM_TITLE      => 'Statistics',
                        self::ITEM_TARGET     => 'orders_stats',
                        self::ITEM_PERMISSION => 'manage orders',
                        self::ITEM_WEIGHT     => 300,
                    ),
                ),
            ),
            'users' => array(
                self::ITEM_TITLE      => 'Users',
                self::ITEM_TARGET     => 'profile_list',
                self::ITEM_WEIGHT     => 200,
                self::ITEM_CHILDREN   => array(
                    'profile_list' => array(
                        self::ITEM_TITLE      => 'Users',
                        self::ITEM_TARGET     => 'profile_list',
                        self::ITEM_PERMISSION => 'manage users',
                        self::ITEM_WEIGHT     => 100,
                    ),
                    'memberships' => array(
                        self::ITEM_TITLE      => 'Membership levels',
                        self::ITEM_TARGET     => 'memberships',
                        self::ITEM_PERMISSION => 'manage users',
                        self::ITEM_WEIGHT     => 200,
                    ),
                ),
            ),
            'promotions' => array(
                self::ITEM_TITLE      => 'Promotions',
                self::ITEM_TARGET     => 'promotions',
                self::ITEM_WEIGHT     => 300,
                self::ITEM_CHILDREN   => array(),
            ),
            'catalog' => array(
                self::ITEM_TITLE      => 'Catalog',
                self::ITEM_TARGET     => 'product_list',
                self::ITEM_WEIGHT     => 400,
                self::ITEM_CHILDREN   => array(
                    'add_product' => array(
                        self::ITEM_TITLE      => 'Add product',
                        self::ITEM_TARGET     => 'add_product',
                        self::ITEM_CLASS      => 'add-product',
                        self::ITEM_PERMISSION => 'manage catalog',
                        self::ITEM_WEIGHT     => 100,
                    ),
                    'product_list' => array(
                        self::ITEM_TITLE      => 'Products',
                        self::ITEM_TARGET     => 'product_list',
                        self::ITEM_PERMISSION => 'manage catalog',
                        self::ITEM_WEIGHT     => 200,
                    ),
                    'categories' => array(
                        self::ITEM_TITLE      => 'Categories',
                        self::ITEM_TARGET     => 'categories',
                        self::ITEM_PERMISSION => 'manage catalog',
                        self::ITEM_WEIGHT     => 300,
                    ),
                    'front_page' => array(
                        self::ITEM_TITLE      => 'Front page',
                        self::ITEM_TARGET     => 'front_page',
                        self::ITEM_PERMISSION => 'manage catalog',
                        self::ITEM_WEIGHT     => 350,
                    ),
                    'product_classes' => array(
                        self::ITEM_TITLE      => 'Classes & attributes',
                        self::ITEM_TARGET     => 'product_classes',
                        self::ITEM_PERMISSION => 'manage catalog',
                        self::ITEM_WEIGHT     => 400,
                    ),
                    'import' => array(
                        self::ITEM_TITLE      => static::t('Import'),
                        self::ITEM_TARGET     => 'import',
                        self::ITEM_PERMISSION => 'manage import',
                        self::ITEM_WEIGHT     => 500,
                    ),
                    'export' => array(
                        self::ITEM_TITLE      => static::t('Export'),
                        self::ITEM_TARGET     => 'export',
                        self::ITEM_PERMISSION => 'manage export',
                        self::ITEM_WEIGHT     => 600,
                    ),
                ),
            ),
        );

        // Check if cloned products exists and add menu item
        // TODO: need to be reviewed - search should not be used on each load of admin interface pages
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = '[ clone ]';
        $cnd->{\XLite\Model\Repo\Product::P_BY_TITLE} = 'Y';

        if (0 < \XLite\Core\Database::getRepo('\XLite\Model\Product')->search($cnd, true)) {
            $items['catalog'][self::ITEM_CHILDREN]['clone_products'] = array(
                self::ITEM_TITLE      => 'Cloned products',
                self::ITEM_TARGET     => 'cloned_products',
                self::ITEM_PERMISSION => 'manage catalog',
                self::ITEM_WEIGHT     => 220,
            );
        }

        $pagesStatic = \XLite\Controller\Admin\Promotions::getPagesStatic();
        if ($pagesStatic) {
            foreach ($pagesStatic as $k => $v) {
                $items['promotions'][self::ITEM_CHILDREN][$k] = array(
                    self::ITEM_TITLE  => $v['name'],
                    self::ITEM_TARGET => 'promotions',
                    self::ITEM_EXTRA  => array('page' => $k),
                );

                $items['promotions'][self::ITEM_EXTRA] = array('page' => $k);
            }
        }

        return $items;
    }

    /**
     * Return widget directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'top_menu';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Get default widget
     *
     * @return string
     */
    protected function getDefaultWidget()
    {
        return 'XLite\View\Menu\Admin\TopMenu\Node';
    }
}
