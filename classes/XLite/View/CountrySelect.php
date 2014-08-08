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

namespace XLite\View;

// FIXME - to remove

/**
 * \XLite\View\CountrySelect
 */
class CountrySelect extends \XLite\View\FormField
{
    /**
     * Widget param names
     */

    const PARAM_ALL        = 'all';
    const PARAM_FIELD_NAME = 'field';
    const PARAM_COUNTRY    = 'country';
    const PARAM_FIELD_ID   = 'fieldId';
    const PARAM_CLASS_NAME = 'className';
    const PARAM_SELECT_ONE = 'selectOne';
    const PARAM_ALLOW_LABEL_COUNTRY = 'allowLabelCountry';


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/select_country.tpl';
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_ALL        => new \XLite\Model\WidgetParam\Bool('All', false),
            static::PARAM_FIELD_NAME => new \XLite\Model\WidgetParam\String('Field name', ''),
            static::PARAM_FIELD_ID   => new \XLite\Model\WidgetParam\String('Field ID', ''),
            static::PARAM_CLASS_NAME => new \XLite\Model\WidgetParam\String('Class name', ''),
            static::PARAM_COUNTRY    => new \XLite\Model\WidgetParam\String('Value', ''),
            static::PARAM_SELECT_ONE => new \XLite\Model\WidgetParam\Bool('Select one value', false),
            static::PARAM_ALLOW_LABEL_COUNTRY => new \XLite\Model\WidgetParam\Bool('Allow label-based country selector', false),
        );
    }

    /**
     * Check - display enabled only countries or not
     *
     * @return boolean
     */
    protected function isEnabledOnly()
    {
        return !$this->getParam(static::PARAM_ALL);
    }

    /**
     * Get selected value
     *
     * @return string
     */
    protected function getSelectedValue()
    {
        return $this->getParam(static::PARAM_COUNTRY);
    }

    /**
     * Check - if country code is selected option in "SELECT" tag.
     *
     * @param string $countryCode Code of country to check.
     *
     * @return boolean
     */
    protected function isSelectedCountry($countryCode)
    {
        $country = $this->getParam(static::PARAM_COUNTRY);

        if ('' == $country) {
            $country = \XLite\Core\Config::getInstance()->Shipping->anonymous_country;
        }

        return $country === $countryCode;
    }

    /**
     * Return countries list
     *
     * @return array
     */
    protected function getCountries()
    {
        return $this->isEnabledOnly()
            ? \XLite\Core\Database::getRepo('XLite\Model\Country')->findAllEnabled()
            : \XLite\Core\Database::getRepo('XLite\Model\Country')->findAllCountries();
    }

    /**
     * Check - country selector is label-based
     *
     * @return boolean
     */
    protected function isLabelBasedSelector()
    {
        return $this->getParam(static::PARAM_ALLOW_LABEL_COUNTRY)
            && 1 == count($this->getCountries());
    }

    /**
     * Get one country
     *
     * @return \XLite\Model\Country
     */
    protected function getOneCountry()
    {
        $list = $this->getCountries();

        return reset($list);
    }

    /**
     * Return if the select one value is available
     *
     * @return boolean
     */
    protected function hasSelectOne()
    {
        return $this->getParam(static::PARAM_SELECT_ONE);
    }
}
