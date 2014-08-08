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

namespace XLite\View\Base;

/**
 * Form-based sticky panel
 */
abstract class FormStickyPanel extends \XLite\View\Base\StickyPanel
{
    /**
     * Get buttons widgets
     *
     * @return array
     */
    abstract protected function getButtons();

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'form/panel';
    }

    /**
     * Get cell class
     *
     * @param integer           $idx    Button index
     * @param string            $name   Button name
     * @param \XLite\View\AView $button Button
     *
     * @return string
     */
    protected function getCellClass($idx, $name, \XLite\View\AView $button)
    {
        $classes = array('panel-cell', $name);

        if (1 == $idx) {
            $classes[] = 'first';
        }

        if (count($this->getButtons()) == $idx) {
            $classes[] = 'last';
        }


        return implode(' ', $classes);
    }

    /**
     * Get subcell class (additional buttons)
     *
     * @param integer           $idx    Button index
     * @param string            $name   Button name
     * @param \XLite\View\AView $button Button
     *
     * @return string
     */
    protected function getSubcellClass($idx, $name, \XLite\View\AView $button)
    {
        $classes = array('panel-subcell', $name);

        if (1 == $idx) {
            $classes[] = 'first';
        }

        if (count($this->getAdditionalButtons()) == $idx) {
            $classes[] = 'last';
        }


        return implode(' ', $classes);
    }

    /**
     * Check - sticky panel is active only if form is changed
     *
     * @return boolean
     */
    protected function isFormChangeActivation()
    {
        return true;
    }

    /**
     * Get class
     *
     * @return string
     */
    protected function getClass()
    {
        $class = parent::getClass();

        if ($this->isFormChangeActivation()) {
            $class .= ' form-change-activation';
        }

        return $class;
    }

}
