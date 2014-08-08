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
 * Product selection in popup
 */
class PopupProductSelector extends \XLite\View\Button\APopupButton
{
    const PARAM_REDIRECT_URL = 'redirect_url';

    /**
     * getJSFiles
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'button/js/product_selection.js';
        foreach ($this->getWidgets() as $widget) {
            $list = array_merge($list, $this->getWidget(array(), $widget)->getJSFiles());
        }

        return $list;
    }

    /**
     * getCSSFiles
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        foreach ($this->getWidgets() as $widget) {
            $list = array_merge($list, $this->getWidget(array(), $widget)->getCSSFiles());
        }

        return $list;
    }

    /**
     * Defines the widgets from which the CSS/JS files must be taken
     *
     * @return array
     */
    protected function getWidgets()
    {
        return array(
            $this->getSelectorViewClass(),
            '\XLite\View\ItemsList\Model\ProductSelection',
            '\XLite\View\Form\ItemsList\ProductSelection\Table',
            '\XLite\View\FormField\Inline\Input\Checkbox\Switcher\EnabledReadOnly',
            '\XLite\View\FormField\Input\Checkbox\SwitcherReadOnly',
            '\XLite\View\Pager\Admin\Model\ProductSelection\Table',
            '\XLite\View\SearchPanel\ProductSelections\Admin\Main',
            '\XLite\View\StickyPanel\ItemsList\ProductSelection',
        );
    }

    /**
     * Return URL parameters to use in AJAX popup
     *
     * @return array
     */
    protected function prepareURLParams()
    {
        return array(
            'target'        => $this->getSelectorTarget(),
            'widget'        => $this->getSelectorViewClass(),
            'redirect_url'  => $this->getParam(static::PARAM_REDIRECT_URL),
            'substring'     => '',
        );
    }

    /**
     * Defines the target of the product selector
     * The main reason is to get the title for the selector from the controller
     *
     * @return string
     */
    protected function getSelectorTarget()
    {
        return 'product_selections';
    }

    /**
     * Defines the class name of the widget which will display the product list dialog
     *
     * @return string
     */
    protected function getSelectorViewClass()
    {
        return '\XLite\View\ProductSelections';
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
            static::PARAM_REDIRECT_URL => new \XLite\Model\WidgetParam\String('URL to redirect to', ''),
        );
    }

    /**
     * Return CSS classes
     *
     * @return string
     */
    protected function getClass()
    {
        return trim(parent::getClass() . ' popup-product-selection');
    }

    /**
     * getDefaultLabel
     *
     * @return string
     */
    protected function getDefaultLabel()
    {
        return 'Select products';
    }
}
