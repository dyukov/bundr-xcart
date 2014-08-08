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

namespace XLite\View\FormField\Input\Text;

/**
 * Date range
 */
class DateRange extends \XLite\View\FormField\Input\Text
{
    /**
     * Labels displayed
     *
     * @var   boolean
     */
    protected static $labelsDisplayed = false;

    /**
     * Parse range as string
     *
     * @param string $string String
     *
     * @return array
     */
    public static function convertToArray($string)
    {
        if (is_string($string)) {
            $string = $string && preg_match('/^(\d+)-(\d+)-(\d+)\D+(\d+)-(\d+)-(\d+)$/Ss', trim($string), $match)
            ? array(mktime(0, 0, 0, $match[2], $match[3], $match[1]), mktime(23, 59, 59, $match[5], $match[6], $match[4]))
            : array(null, null);
        }

        return $string;
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    protected function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        $list[static::RESOURCE_JS][] = 'js/moment-with-langs.min.js';
        $list[static::RESOURCE_JS][] = 'js/daterangepicker.js';
        $list[static::RESOURCE_CSS][] = 'css/daterangepicker.css';

        return $list;
    }

    /**
     * Get CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'form_field/input/text/date_range.css';

        return $list;
    }

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/js/date_range.js';

        return $list;
    }

    /**
     * Set value
     *
     * @param mixed $value Value to set
     *
     * @return void
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $value = $this->convertToString($value);
        }

        parent::setValue($value);
    }

    /**
     * Get formatted range
     *
     * @return string
     */
    protected function convertToString(array $value)
    {
        if (!empty($value[0]) || !empty($value[1])) {
            $value[0] = !empty($value[0]) ? date('Y-m-d', $value[0]) : date('Y-m-d');
            $value[1] = !empty($value[1]) ? date('Y-m-d', $value[1]) : date('Y-m-d');
            $value = implode(' ~ ', $value);

        } else {
            $value = '';
        }

        return $value;
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
        $list = parent::assembleClasses($classes);

        $list[] = 'date-range';

        return $list;
    }

    /**
     * Some JavaScript code to insert
     *
     * @return string
     */
    protected function getInlineJSCode()
    {
        return parent::getInlineJSCode() . PHP_EOL
            . 'jQuery.dateRangePickerLanguages.en = ' . json_encode($this->getJavascriptLanguagesLabels()) . PHP_EOL;
    }

    /**
     * Get languages labels
     *
     * @return array
     */
    protected function getJavascriptLanguagesLabels()
    {
        return array(
            'selected'        => static::t('Selected:'),
            'days'            => static::t('Days'),
            'apply'           => static::t('Close'),
            'week-1'          => static::t('MO'),
            'week-2'          => static::t('TU'),
            'week-3'          => static::t('WE'),
            'week-4'          => static::t('TH'),
            'week-5'          => static::t('FR'),
            'week-6'          => static::t('SA'),
            'week-7'          => static::t('SU'),
            'month-name'      => array(
                static::t('JANUARY'),
                static::t('FEBRUARY'),
                static::t('MARCH'),
                static::t('APRIL'),
                static::t('MAY'),
                static::t('JUNE'),
                static::t('JULY'),
                static::t('AUGUST'),
                static::t('SEPTEMBER'),
                static::t('OCTOBER'),
                static::t('NOVEMBER'),
                static::t('DECEMBER'),
            ),
            'shortcuts'       => static::t('Shortcuts'),
            'past'            => static::t('Past'),
            '7days'           => static::t('7days'),
            '14days'          => static::t('14days'),
            '30days'          => static::t('30days'),
            'previous'        => static::t('Previous'),
            'prev-week'       => static::t('Week'),
            'prev-month'      => static::t('Month'),
            'prev-quarter'    => static::t('Quarter'),
            'prev-year'       => static::t('Year'),
            'less-than'       => static::t('Date range should longer than %d days'),
            'more-than'       => static::t('Date range should less than %d days'),
            'default-more'    => static::t('Please select a date range longer than %d days'),
            'default-less'    => static::t('Please select a date range less than %d days'),
            'default-range'   => static::t('Please select a date range between %d and %d days'),
            'default-default' => static::t('Please select a date range'),
        );
    }

    /**
     * Get default placeholder
     *
     * @return string
     */
    protected function getDefaultPlaceholder()
    {
        return static::t('Enter date range');
    }
}
