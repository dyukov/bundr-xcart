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

namespace XLite\View\Checkout;

/**
 * Billing address block
 */
class BillingAddress extends \XLite\View\Checkout\AAddressBlock
{
    /**
     * Modifier (cache)
     *
     * @var \XLite\Model\Order\Modifier
     */
    protected $modifier;

    /**
     * Get JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'checkout/steps/shipping/parts/address.billing.js';

        return $list;
    }

    /**
     * Check - email field is visible or not
     *
     * @return boolean
     */
    protected function isEmailVisible()
    {
        return !$this->getModifier() || !$this->getModifier()->canApply();
    }

    /**
     * Check - password field is visible or not
     *
     * @return boolean
     */
    protected function isPasswordVisible()
    {
        return (!$this->getModifier() || !$this->getModifier()->canApply())
            && $this->isAnonymous();
    }

    /**
     * Check - create profile checkbox is visible or not
     *
     * @return boolean
     */
    protected function isCreateVisible()
    {
        return $this->isAnonymous() && (!$this->getModifier() || !$this->getModifier()->canApply());
    }

    /**
     * Check - form is visible or not
     *
     * @return boolean
     */
    protected function isFormVisible()
    {
        return !$this->isSameAddress() || !$this->isSameAddressVisible();
    }

    /**
     * Check - shipping and billing addrsses are same or not
     *
     * @return boolean
     */
    protected function isSameAddress()
    {
        return is_null(\XLite\Core\Session::getInstance()->same_address)
            ? !$this->getCart()->getProfile() || $this->getCart()->getProfile()->isEqualAddress()
            : \XLite\Core\Session::getInstance()->same_address;
    }

    /**
     * Get field name
     *
     * @param string $name File short name
     *
     * @return string
     */
    protected function getFieldFullName($name)
    {
        return in_array($name, array('email', 'password'))
            ? $name
            : ('billingAddress[' . $name . ']');
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'checkout/steps/shipping/parts/billingAddress.tpl';
    }

    /**
     * Check - same address box is visible or not
     *
     * @return boolean
     */
    protected function isSameAddressVisible()
    {
        return $this->getModifier() && $this->getModifier()->canApply();
    }

    /**
     * Get modifier
     *
     * @return \XLite\Model\Order\Modifier
     */
    protected function getModifier()
    {
        if (!isset($this->modifier)) {
            $this->modifier = $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
        }

        return $this->modifier;
    }

    /**
     * Get address info model
     *
     * @return \XLite\Model\Address
     */
    protected function getAddressInfo()
    {
        $profile = $this->getCart()->getProfile();

        return $this->isFormVisible() && $profile
            ? $profile->getBillingAddress()
            : null;
    }

    /**
     * Check - display save new field or not
     *
     * @return boolean
     */
    protected function isSaveNewField()
    {
        return parent::isSaveNewField()
            && $this->isFormVisible();
    }

    /**
     * Check - 'Save as new' checkbox checked or not
     *
     * @return boolean
     */
    protected function isSaveAsNewChecked()
    {
        return $this->getAddressInfo() && !$this->getAddressInfo()->getIsWork();
    }

}
