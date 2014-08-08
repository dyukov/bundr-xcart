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

namespace XLite\View\FormField\Input\Base;

/**
 * String-based
 */
abstract class String extends \XLite\View\FormField\Input\AInput
{
    /**
     * Widget param names
     */
    const PARAM_DEFAULT_VALUE = 'defaultValue';
    const PARAM_MAX_LENGTH    = 'maxlength';
    const PARAM_AUTOCOMPLETE  = 'autocomplete';

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_DEFAULT_VALUE => new \XLite\Model\WidgetParam\String('Default value', ''),
            static::PARAM_MAX_LENGTH    => new \XLite\Model\WidgetParam\Int('Maximum length', $this->getDefaultMaxSize()),
            static::PARAM_AUTOCOMPLETE  => new \XLite\Model\WidgetParam\Bool('Autocomplete', false),
        );
    }

    /**
     * getCommonAttributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        $list = parent::getCommonAttributes();
        $list['autocomplete'] = $this->getParam(static::PARAM_AUTOCOMPLETE) ? 'on' : 'off';

        if ($this->getParam(static::PARAM_MAX_LENGTH)) {
            $list['maxlength'] = $this->getParam(static::PARAM_MAX_LENGTH);
        }

        return $list;
    }

    /**
     * Get default maximum size
     *
     * @return integer
     */
    protected function getDefaultMaxSize()
    {
        return 255;
    }

    /**
     * Check field validity
     *
     * @return boolean
     */
    protected function checkFieldValidity()
    {
        $result = parent::checkFieldValidity();

        if ($result && strlen($result) > $this->getParam(self::PARAM_MAX_LENGTH)) {
            $result = false;
            $this->errorMessage = \XLite\Core\Translation::lbl(
                'The value of the X field should not be longer than Y',
                array(
                    'name' => $this->getLabel(),
                    'max'  => $this->getParam(self::PARAM_MAX_LENGTH),
                )
            );
        }

        return $result;
    }

    /**
     * Assemble validation rules
     *
     * @return array
     */
    protected function assembleValidationRules()
    {
        $rules = parent::assembleValidationRules();

        $rules[] = 'maxSize[' . $this->getParam(self::PARAM_MAX_LENGTH) . ']';

        return $rules;
    }

    /**
     * Register some data that will be sent to template as special HTML comment
     *
     * @return array
     */
    protected function getCommentedData()
    {
        return array(
            'defaultValue' => $this->getParam(self::PARAM_DEFAULT_VALUE),
        );
    }

    /**
     * Sanitize value
     *
     * @return mixed
     */
    protected function sanitize()
    {
       return trim(parent::sanitize());
    }
}
