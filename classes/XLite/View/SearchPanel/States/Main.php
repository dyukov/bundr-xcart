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

namespace XLite\View\SearchPanel\States;

/**
 * States search panel
 */
class Main extends \XLite\View\SearchPanel\ASearchPanel
{
    /**
     * Get form class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\States\Search';
    }

    /**
     * Disable the search form for states (XCN-2775)
     */
    protected function isVisible()
    {
        return false;
    }

    /**
     * Define conditions
     *
     * @return array
     */
    protected function defineConditions()
    {
        $countryCode = \XLite::getController()->getCountryCode();

        return parent::defineConditions() + array(
            'country_code' => array(
                static::CONDITION_CLASS                             => 'XLite\View\FormField\Select\Country',
                \XLite\View\FormField\AFormField::PARAM_FIELD_ONLY  => true,
                \XLite\View\FormField\Input\Text::PARAM_PLACEHOLDER => static::t('Select country'),
                \XLite\View\FormField\Select\Country::PARAM_VALUE   => $countryCode,
                \XLite\View\FormField\Select\Country::PARAM_SELECT_ONE => false,
            ),
        );
    }

    /**
     * Define actions
     *
     * @return array
     */
    protected function defineActions()
    {
        $actions = parent::defineActions();

        $actions['submit'][\XLite\View\Button\AButton::PARAM_LABEL] = static::t('Find states');

        return $actions;
    }
}
