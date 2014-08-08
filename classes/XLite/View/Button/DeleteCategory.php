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

namespace XLite\View\Button;

/**
 * Delete category popup button
 */
class DeleteCategory extends \XLite\View\Button\APopupButton
{
    /**
     * Widget param names
     */
    const PARAM_CATEGORY_ID          = 'categoryId';
    const PARAM_REMOVE_SUBCATEGORIES = 'removeSubcategories';

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'button/js/delete_category.js';

        return $list;
    }

    /**
     * Return URL parameters to use in AJAX popup
     *
     * @return array
     */
    protected function prepareURLParams()
    {
        return array(
            'target'      => 'categories',
            'pre_action'  => 'delete',
            'widget'      => '\XLite\View\DeleteCategory',
            'category_id' => $this->getParam(self::PARAM_CATEGORY_ID),
            'subcats'     => (bool) $this->getParam(self::PARAM_REMOVE_SUBCATEGORIES),
        );
    }

    /**
     * Return default button label
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Delete';
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
            self::PARAM_CATEGORY_ID          => new \XLite\Model\WidgetParam\Int('Category ID', 1),
            self::PARAM_REMOVE_SUBCATEGORIES => new \XLite\Model\WidgetParam\Bool('Remove subcategories', false),
        );
    }

    /**
     * Return CSS classes
     *
     * @return string
     */
    protected function getClass()
    {
        return parent::getClass() . ' delete-category-button';
    }

    /**
     * Return template path
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/delete_category.tpl';
    }
}
