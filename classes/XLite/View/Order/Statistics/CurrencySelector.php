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

namespace XLite\View\Order\Statistics;

/**
 * Currency selector
 */
class CurrencySelector extends \XLite\View\AView
{
    /**
     * Current currency
     *
     * @var \XLite\Model\Currency
     */
    protected $currency;

    /**
     * Currencies (cache)
     *
     * @var array
     */
    protected $currencies;

    /**
     * Get currencies
     *
     * @return array
     */
    protected function getCurrencies()
    {
        if (!isset($this->currencies)) {
            $this->currencies = parent::getCurrencies();
        }

        return $this->currencies;
    }

    /**
     * Check - currency is selected or not
     *
     * @param \XLite\Model\Currency $currency Currency
     *
     * @return boolean
     */
    protected function isCurrencySelected(\XLite\Model\Currency $currency)
    {
        if (!isset($this->currency)) {
            if (\XLite\Core\Request::getInstance()->currency) {
                $this->currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')
                    ->find(\XLite\Core\Request::getInstance()->currency);
            }

            if (!$this->currency) {
                $this->currency = \XLite::getInstance()->getCurrency();
            }
        }

        return $currency->getCurrencyId() == $this->currency->getCurrencyId();
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'order/currency_selector.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return 1 < count($this->getCurrencies());
    }
}
