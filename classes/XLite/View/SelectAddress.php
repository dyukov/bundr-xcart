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
 * Pick address from address book
 *
 * @ListChild (list="center")
 */
class SelectAddress extends \XLite\View\Dialog
{
    /**
     * Columns number
     *
     * @var integer
     */
    protected $columnsNumber = 2;

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();
        $result[] = 'select_address';

        return $result;
    }

    /**
     * Get a list of JS files required to display the widget properly
     * FIXME - decompose these files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'select_address/controller.js';

        return $list;
    }

    /**
     * Check - specified address is selected or not
     *
     * @param \XLite\Model\Address $address Address
     *
     * @return boolean
     */
    public function isSelectedAddress(\XLite\Model\Address $address)
    {
        $atype = \XLite\Core\Request::getInstance()->atype;

        return ($address->getIsShipping() && \XLite\Model\Address::SHIPPING == $atype)
            || ($address->getIsBilling() && \XLite\Model\Address::BILLING == $atype);
    }

    /**
     * Get addresses list
     *
     * @return array
     */
    public function getAddresses()
    {
        $list = $this->getCart()->getProfile()->getAddresses()->toArray();
        foreach ($list as $i => $address) {
            if ($address->getIsWork()) {
                unset($list[$i]);
            }
        }

        return array_values($list);
    }

    /**
     * Check - profile has addresses list or not
     *
     * @return boolean
     */
    public function hasAddresses()
    {
        return 0 < count($this->getAddresses());
    }

    /**
     * Get list item class name
     *
     * @param \XLite\Model\Address $address Address
     * @param integer              $i       Address position in addresses list
     *
     * @return string
     */
    public function getItemClassName(\XLite\Model\Address $address, $i)
    {
        $class = 'address-' . $address->getAddressId();

        if ($this->isSelectedAddress($address)) {
            $class .= ' selected';
        }

        if (0 == $i % $this->columnsNumber) {
            $class .= ' last';
        }

        return $class;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'select_address';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Auth::getInstance()->isLogged();
    }
}
