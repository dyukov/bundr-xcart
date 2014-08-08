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

namespace XLite\Module\CDev\XPaymentsConnector\Controller\Admin;

/**
 * X-Payments Connector module settings
 *
 */
class Xpc extends \XLite\Controller\Admin\Module
{
    /**
     * handleRequest
     *
     * @return void
     */
    public function handleRequest()
    {
        parent::handleRequest();

        $sections = \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getAllSections();

        if (!in_array(\XLite\Core\Request::getInstance()->section, $sections)) {

            $this->setHardRedirect();

            $this->setReturnURL(
                $this->buildURL(
                    'xpc',
                    '',
                    array(
                        'section'  => \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->getDefaultSection(),
                    )
                )
            );

            $this->doRedirect();
        }
    }

    /**
     * Get current module ID
     *
     * @return integer
     */   
    public function getModuleID()
    {
        if (!isset($this->moduleID)) {
            $module = \XLite\Core\Database::getRepo('\XLite\Model\Module')->findOneBy(
                array(
                    'name' => 'XPaymentsConnector',
                    'author' => 'CDev',
                    'installed' => 1,
                    'enabled' => 1,
                )
            );

            if ($module) {
                $this->moduleID = $module->getModuleID();
                $this->module = $module;
            }
        }

        return $this->moduleID;
    }

    /**
     * Check if connection to X-Payments is OK 
     *
     * @return boolean
     */
    public function isConnected()
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->testConnection();
    }

    /**
     * Wrapper for X-Payments client isModuleConfigured() method 
     *
     * @return boolean
     */
    public function isConfigured()
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->isModuleConfigured();
    }

    /**
     * Check - is there are any actve payment methods which can save cards 
     *
     * @return boolean
     */
    public function hasActiveMethodsSavingCards()
    {
        $paymentMethods = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->findAllActive();

        $result = false;

        foreach ($paymentMethods as $pm) {
            if (
                'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments' == $pm->getClass()
                && 'Y' == $pm->getSetting('saveCards')
            ) {
                $result = true;
                break;
            }
        }


        return $result;
    }

    /**
     * Check - is payment configurations imported early or not
     *
     * @return boolean
     */
    public static function hasPaymentMethods()
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\Settings::hasPaymentMethods();
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public static function getPaymentMethods($processor = 'XPayments')
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getPaymentMethods($processor);
    }

    /**
     * Get link to purchase X-Payments
     *
     * @return array
     */
    public static function getPurchaseLink()
    {
        return 'http://www.x-cart.com/xpayments-pricing.html?utm_source=xcart&utm_medium=welcome_page&utm_campaign=xp_connector_xc5';
    }

    /**
     * Get link to X-Payments description
     *
     * @return array
     */
    public static function getXpaymentsLink()
    {
        return 'http://www.x-cart.com/extensions/modules/xpayments.html?utm_source=xcart&utm_medium=welcome_page&utm_campaign=xp_connector_xc5';
    }

    /**
     * Get link to purchase X-Payments
     *
     * @return array
     */
    public function getConnectionLink()
    {
        return $this->buildURL('xpc', '', array('section' => \XLite\Module\CDev\XPaymentsConnector\Core\Settings::SECTION_CONNECTION));
    }


    /**
     * Update payment methods: save cards, currency, etc 
     *
     * @return void
     */
    protected function doActionUpdatePaymentMethods()
    {
        $methods = $this->getPaymentMethods();

        $request = \XLite\Core\Request::getInstance()->data;

        $saveCardsMethodSubmitted = false;

        foreach ($methods as $method) {

            $pmData = \XLite\Core\Request::getInstance()->data[$method->getMethodId()];

            if (
                'Y' == $pmData['save_cards']
                && 'Y' == $method->getSetting('canSaveCards')
            ) {
                $method->setSetting('saveCards', 'Y');
                $saveCardsMethodSubmitted = true;

            } else {
                $method->setSetting('saveCards', 'N');
            }

            if ($pmData['currency']) {
                $method->setSetting('currency', $pmData['currency']);
            }

            if ($pmData['enabled']) {
                $method->setEnabled(true);

            } else {
                $method->setEnabled(false);
            }
        
        }

        $saveCardsMethodInStore = $this->getPaymentMethods('SavedCard');

        if (
            !$saveCardsMethodInStore
            && $saveCardsMethodSubmitted
        ) {

            // Add Saved credit card payment method if at least one of X-Payments payment methods saves cards   
            $pm = new \XLite\Model\Payment\Method;
            \XLite\Core\Database::getEM()->persist($pm);
            $pm->setClass('Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard');
            $pm->setServiceName('SavedCard');
            $pm->setName('Use a saved credit card');
            $pm->setType(\XLite\Model\Payment\Method::TYPE_CC_GATEWAY);
            $pm->setAdded(true);
            $pm->setEnabled(true);

        } elseif (
            $saveCardsMethodInStore
            && !$saveCardsMethodSubmitted
        ) {
            // Remove Seved credit card payment method if all X-Payments payment methods do not save cards
            foreach ($saveCardsMethodInStore as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }
        }

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Deploy configuration
     *
     * @return void
     */
    protected function doActionDeployConfiguration()
    {
        $errorMsg = \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()
            ->deployConfiguration(\XLite\Core\Request::getInstance()->deploy_configuration);

        if ($errorMsg) {
            \XLite\Core\TopMessage::addError($errorMsg);

        } else {
            \XLite\Core\TopMessage::addInfo('Configuration has been successfully deployed');

            $this->setHardRedirect();

            $this->setReturnURL(
                $this->buildURL(
                    'xpc',
                    '',
                    array(
                        'section'  => \XLite\Module\CDev\XPaymentsConnector\Core\Settings::SECTION_PAYMENT_METHODS,
                    )
                )
            );

            $this->doRedirect();

        }
    }

    /**
     * Update module settings
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        parent::doActionUpdate();

        \XLite\Core\Config::updateInstance();
        \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->testConnection(false);

        $section = \XLite\Core\Request::getInstance()->section
            ? \XLite\Core\Request::getInstance()->section
            : \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->getDefaultSection();

        $this->setReturnURL(
            $this->buildURL(
                'xpc',
                null,
                array('section' => $section)
            )
        );
    }

    /**
     * Request and import payment configurations
     *
     * @return void
     */
    protected function doActionImport()
    {
        \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->importPaymentMethods();
    }

    /**
     * getModelFormClass
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return '\XLite\Module\CDev\XPaymentsConnector\View\Model\Settings';
    }
}
