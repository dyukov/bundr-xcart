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

namespace XLite\View\FormField\Input\Text;

/**
 * Float
 */
class Float extends \XLite\View\FormField\Input\Text\Base\Numeric
{
    /**
     * Widget param names
     */
    const PARAM_E = 'e';
    const PARAM_THOUSAND_SEPARATOR = 'thousand_separator';
    const PARAM_DECIMAL_SEPARATOR  = 'decimal_separator';

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'form_field/input/text/float.js';

        return $list;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->sanitizeFloat(parent::getValue());
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
            static::PARAM_E => new \XLite\Model\WidgetParam\Int(
                'Number of digits after the decimal separator',
                2
            ),
            static::PARAM_THOUSAND_SEPARATOR => new \XLite\Model\WidgetParam\String(
                'Thousand separator',
                \XLite\Core\Config::getInstance()->Units->thousand_delim
            ),
            static::PARAM_DECIMAL_SEPARATOR => new \XLite\Model\WidgetParam\String(
                'Decimal separator',
                 \XLite\Core\Config::getInstance()->Units->decimal_delim
            ),
        );
    }

    /**
     * Sanitize value
     *
     * @return mixed
     */
    protected function sanitize()
    {
       return $this->sanitizeFloat(parent::sanitize());
    }

    /**
     * Sanitize value
     *
     * @return mixed
     */
    protected function sanitizeFloat($value)
    {
       return round(doubleval($value), $this->getParam(self::PARAM_E));
    }

    /**
     * Assemble validation rules
     *
     * @return array
     */
    protected function assembleValidationRules()
    {
        $rules = parent::assembleValidationRules();

        $rules[] = 'custom[number]';

        return $rules;
    }

    /**
     * Assemble classes
     *
     * @param array $classes Classes
     *
     * @return array
     */
    protected function assembleClasses(array $classes)
    {
        $classes = parent::assembleClasses($classes);

        $classes[] = 'float';

        return $classes;
    }

    /**
     * Get default maximum size
     *
     * @return integer
     */
    protected function getDefaultMaxSize()
    {
        return 15;
    }

    /**
     * getCommonAttributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        $attributes = parent::getCommonAttributes();

        $attributes['data-decimal-delim']  = $this->getParam(self::PARAM_DECIMAL_SEPARATOR);
        $attributes['data-thousand-delim'] = $this->getParam(self::PARAM_THOUSAND_SEPARATOR);

        $e = $this->getE();
        if (isset($e)) {
            $attributes['data-e'] = $e;
        }

        return $attributes;
    }

    /**
     * Get mantis
     *
     * @return integer
     */
    protected function getE()
    {
        return $this->getParam(static::PARAM_E);
    }

}
