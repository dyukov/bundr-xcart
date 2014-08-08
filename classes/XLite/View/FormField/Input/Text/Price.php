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
 * Price
 */
class Price extends \XLite\View\FormField\Input\Text\Symbol
{
    const PARAM_CURRENCY = 'currency';

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/input/price.css';

        return $list;
    }

    /**
     * Set widget params
     *
     * @param array $params Handler params
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        $currency = $this->getCurrency();
        foreach ($this->getWidgetParams() as $name => $param) {
            if (static::PARAM_E == $name) {
                $param->setValue($currency->getE());

            } elseif (static::PARAM_THOUSAND_SEPARATOR == $name) {
                $param->setValue($currency->getThousandDelimiter());

            } elseif (static::PARAM_DECIMAL_SEPARATOR == $name) {
                $param->setValue($currency->getDecimalDelimiter());
            }
        }
    }

    /**
     * Get currency
     *
     * @return \XLite\Model\Currency
     */
    public function getCurrency()
    {
        return $this->getParam(static::PARAM_CURRENCY);
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->getCurrency()->getSymbol() ?: $this->getCurrency()->getCode();
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
            self::PARAM_CURRENCY => new \XLite\Model\WidgetParam\Object(
                'Currency',
                \XLite::getInstance()->getCurrency(),
                false,
                'XLite\Model\Currency'
            ),
        );
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
        $classes[] = 'price';

        return $classes;
    }

    /**
     * getCommonAttributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        $attributes = parent::getCommonAttributes();
        $attributes['value'] = $this->formatValue($attributes['value']);

        return $attributes;
    }

    /**
     * Format value
     *
     * @param float $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        return number_format(
            round($value, $this->getE()),
            $this->getE(),
            '.',
            ''
        );
    }

    /**
     * Get mantis
     *
     * @return integer
     */
    protected function getE()
    {
        return $this->getCurrency()->getE();
    }

}

