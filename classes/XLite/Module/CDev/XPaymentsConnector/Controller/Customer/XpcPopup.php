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
 namespace XLite\Module\CDev\XPaymentsConnector\Controller\Customer;

/**
 * Saved credit cards 
 */
class XpcPopup extends \XLite\Controller\Customer\ACustomer
{

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\Iframe::IFRAME_DO_NOTHING == $this->getType()
            ? 'X-Payments info'
            : 'X-Payments error';
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return \XLite\Core\Request::getInstance()->widget;
    }

    /**
     * Check if current page is accessible
     *
     * @return boolean
     */
    public function checkAccess()
    {
        return parent::checkAccess() && \XLite\Core\Auth::getInstance()->isLogged();
    }

    /**
     * Checkout. Recognize iframe and save that
     *
     * @return string
     */
    public function getMessage()
    {
        return urldecode(\XLite\Core\Request::getInstance()->message);
    }

    /**
     * Checkout. Recognize iframe and save that
     *
     * @return integer
     */
    public function getType()
    {
        $type = \XLite\Core\Request::getInstance()->type
            ? intval(\XLite\Core\Request::getInstance()->type)
            : 0;
    
        return 3 < $type ? 0 : $type;
    }

    /**
     * Get button action 
     * 
     * @return string
     */
    public function getButtonAction()
    {
        $result = 'void(0);';

        switch ($this->getType()) {

            case \XLite\Module\CDev\XPaymentsConnector\Core\Iframe::IFRAME_CHANGE_METHOD:
                $result = 'location.reload();';
                break;

            case \XLite\Module\CDev\XPaymentsConnector\Core\Iframe::IFRAME_CLEAR_INIT_DATA:
                $result = 'window.location.href = "' . $this->buildUrl('checkout', 'clear_init_data') . '";';
                break;

            case \XLite\Module\CDev\XPaymentsConnector\Core\Iframe::IFRAME_DO_NOTHING:
            default:
                $result = 'popup.close();';
                break;
        }

        return $result;
    }

    /**
     * Common method to determine current location
     *
     * @return string
     */
    protected function getLocation()
    {
        return '';
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode('X-Payments');
    }

}
