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

/**
 * Date picker widget
 */
class DatePicker extends \XLite\View\FormField\Input\Text
{
    /*
     * Constants: names of a widget parameters
     */
    const PARAM_FIELD     = 'field';
    const PARAM_VALUE     = 'value';
    const PARAM_HIGH_YEAR = 'highYear';
    const PARAM_LOW_YEAR  = 'lowYear';


    /**
     * Date format (PHP)
     *
     * @var string
     */
    protected $phpDateFormat = '%b %d, %Y';

    /**
     * Date format (javascript)
     *
     * @var string
     */
    protected $jsDateFormat = 'M dd, yy';


    /**
     * Get element class name
     *
     * @return string
     */
    public function getClassName()
    {
        $name = str_replace(
            array('[', ']'),
            array('-', ''),
            $this->getParam(self::PARAM_FIELD)
        );

        return strtolower($name);
    }

    /**
     * Get widget value as string
     *
     * @return string
     */
    public function getValueAsString()
    {
        return 0 >= $this->getParam(self::PARAM_VALUE)
            ? ''
            : \XLite\Core\Converter::formatDate($this->getParam(self::PARAM_VALUE), $this->phpDateFormat, false);
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'common/ui.datepicker.css';
        $list[] = $this->getDir() . '/datepicker.css';

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

        $list[] = $this->getDir() . '/datepicker.js';

        return $list;
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getFieldTemplate()
    {
        return 'datepicker.tpl';
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
            self::PARAM_FIELD     => new \XLite\Model\WidgetParam\String('Name of date field prefix', 'date'),
            self::PARAM_VALUE     => new \XLite\Model\WidgetParam\Int('Value of date field (timestamp)', null),
            self::PARAM_HIGH_YEAR => new \XLite\Model\WidgetParam\Int('The high year', date('Y', \XLite\Core\Converter::time()) - 1),
            self::PARAM_LOW_YEAR  => new \XLite\Model\WidgetParam\Int('The low year', 2035),
        );
    }

    /**
     * Return specific for JS code widget options
     *
     * @return array
     */
    protected function getDatePickerOptions()
    {
        return array(
            'dateFormat' => $this->jsDateFormat,
            'highYear'   => $this->getParam(static::PARAM_HIGH_YEAR),
            'lowYear'    => $this->getParam(static::PARAM_LOW_YEAR),
        );
    }

    /**
     * Getter for Field-only flag
     *
     * @return boolean
     */
    protected function getDefaultParamFieldOnly()
    {
        return true;
    }
}
