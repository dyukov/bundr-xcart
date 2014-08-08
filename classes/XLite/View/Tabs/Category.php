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

namespace XLite\View\Tabs;

/**
 * Tabs related to category section
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Category extends \XLite\View\Tabs\ATabs
{
    /**
     * Description of tabs related to user profile section and their targets
     *
     * @var array
     */
    protected $tabs = array(
        'categories' => array(
            'title'    => 'Subcategories for',
            'template' => 'categories/body.tpl',
        ),
        'category' => array(
            'title'    => 'Category info',
            'template' => 'category/body.tpl',
        ),
        'category_products' => array(
            'title' => 'Products',
            'template' => 'category_products/body.tpl',
        ),
    );

    /**
     * Returns the list of targets where this widget is available
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'categories';
        if (\XLite\Core\Request::getInstance()->id) {
            $list[] = 'category';
            $list[] = 'category_products';
        }

        return $list;
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (
            in_array(\XLite\Core\Request::getInstance()->target, static::getAllowedTargets())
            && \XLite\Core\Request::getInstance()->id
        ) {
            $this->tabs['categories']['title'] = static::t($this->tabs['categories']['title'])
                . ' "' . $this->getCategoryName() . '"';
        }

        if (
            !\XLite\Core\Request::getInstance()->id
            && !\XLite\Core\Request::getInstance()->parent
        ) {
            // Front page
            $this->tabs['categories']['title'] = 'Main categories';
            unset($this->tabs['category']);
            unset($this->tabs['category_products']);
        } elseif (!\XLite\Core\Request::getInstance()->id) {
            // New category
            unset($this->tabs['categories']);
            unset($this->tabs['category_products']);
        }
    }

    /**
     * Returns an URL to a tab
     *
     * @param string $target Tab target
     *
     * @return string
     */
    protected function buildTabURL($target)
    {
        return $this->buildURL($target, '', array('id' => \XLite\Core\Request::getInstance()->id));
    }

    /** 
     * Checks whether the tabs navigation is visible, or not
     *
     * @return boolean
     */
    protected function isTabsNavigationVisible()
    {  
        $visible = parent::isTabsNavigationVisible();

        if (
            !\XLite\Core\Request::getInstance()->id
            && !\XLite\Core\Request::getInstance()->parent
        ) {
            $visible = false;
        }

        return $visible;
    }  
}
