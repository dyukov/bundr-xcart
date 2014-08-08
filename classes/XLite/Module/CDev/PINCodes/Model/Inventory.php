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

namespace XLite\Module\CDev\PINCodes\Model;

/**
 * Inventory
 *
 */
class Inventory extends \XLite\Model\Inventory implements \XLite\Base\IDecorator
{
    /**
     * Alias: is product in stock or not
     *
     * @return boolean
     */
    public function isOutOfStock()
    {
        $result = false;

        if ($this->getProduct() && $this->getProduct()->hasManualPinCodes()) {
            $result = 0 >= $this->getProduct()->getRemainingPinCodesCount();
        } else {
            $result = parent::isOutOfStock();
        }

        return $result;
    }

    /**
     * Check if product amount is less than its low limit
     *
     * @return boolean
     */
    public function isLowLimitReached()
    {
        $amount = false;
        if ($this->getProduct()->hasManualPinCodes()) {
            $amount = $this->getProduct()->getRemainingPinCodesCount();
        } else {
            $amount = $this->getAmount();
        }

        return $this->getEnabled() && $this->getLowLimitEnabled() && $amount < $this->getLowLimitAmount();
    }

    /**
     * Return product amount available to add to cart
     *
     * @return integer
     */
    public function getAvailableAmount()
    {
        $amount = 0;
        if ($this->getProduct() && $this->getProduct()->hasManualPinCodes()) {
            $amount = $this->getProduct()->getRemainingPinCodesCount();
        } else {
            $amount = parent::getAvailableAmount();
        }
        
        return $amount;
    }

    /**
     * Get enabled. Should always be true for manual pin codes products
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        $enabled = true;
        if (!$this->getProduct() || !$this->getProduct()->hasManualPinCodes()) {
            $enabled = $enabled && parent::getEnabled();
        } 
        
        return $enabled;
    }
}
