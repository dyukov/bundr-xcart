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
 * Common surcharge
 */
class Surcharge extends \XLite\View\AView
{
    /**
     * Widget parameter names
     */
    const PARAM_SURCHARGE = 'surcharge';
    const PARAM_CURRENCY  = 'currency';
    const PARAM_PURPOSE   = 'purpose';


    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'common/surcharge.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/surcharge.tpl';
    }

    /**
     * Return surcharge
     *
     * @return float
     */
    protected function getSurcharge()
    {
        return $this->getParam(self::PARAM_SURCHARGE);
    }

    /**
     * Return currency
     *
     * @return \XLite\Model\Currency
     */
    protected function getCurrency()
    {
        return $this->getParam(self::PARAM_CURRENCY);
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
            self::PARAM_SURCHARGE => new \XLite\Model\WidgetParam\Float('Surcharge', null),
            self::PARAM_CURRENCY  => new \XLite\Model\WidgetParam\Object(
                'Currency',
                \XLite::getInstance()->getCurrency(),
                false,
                'XLite\Model\Currency'
            ),
            self::PARAM_PURPOSE   => new \XLite\Model\WidgetParam\String('Purpose', null),
        );
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && !is_null($this->getParam(self::PARAM_SURCHARGE));
    }
}
