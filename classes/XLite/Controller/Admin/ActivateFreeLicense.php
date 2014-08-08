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

namespace XLite\Controller\Admin;

/**
 * Activate license key page controller
 */
class ActivateFreeLicense extends \XLite\Controller\Admin\ModuleKey
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        // Download data from marketplace
        \XLite\Core\Marketplace::getInstance()->saveAddonsList(0);
    }

    /**
     * Return page title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Activate free license';
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    public function checkACL()
    {
        return parent::checkACL() && !(bool)\XLite::getXCNLicense();
    }

    /**
     * We send the free license activation key
     *
     * @return void
     */
    protected function doActionActivate()
    {
        if (\XLite\Core\Marketplace::getInstance()->doTestMarketplace()) {
            $this->doActivateFreeLicense();
        } else {
            \XLite\Core\TopMessage::addError('License activation is not available');

            $this->setReturnURL($this->buildURL('activate_free_license'));
        }
    }

    /**
     * We send the free license activation key
     *
     * @return void
     */
    protected function doActivateFreeLicense()
    {
        $info = \XLite\Core\Marketplace::getInstance()->activateFreeLicense(\XLite\Core\Request::getInstance()->email);

        if (\XLite\Core\Marketplace::getInstance()->getError()) {

            // Marketplace returned an error
            $message = \XLite\Core\Marketplace::getInstance()->getError();

            if (\XLite\Core\Marketplace::ERROR_CODE_FREE_LICENSE_REGISTERED == \XLite\Core\Marketplace::getInstance()->getLastErrorCode()) {
                // Free license is already registered: prepare specific error message
                $message = static::t(
                    'Free license key for this email is already registered',
                    array(
                        'email' => \XLite\Core\Request::getInstance()->email,
                        'url'   => $this->buildURL(
                            'activate_free_license',
                            'resend_key',
                            array('email' => \XLite\Core\Request::getInstance()->email)
                        ),
                    )
                );
            }

            \XLite\Core\TopMessage::addError($message);

        } elseif ($info && isset($info[\XLite\Core\Marketplace::XC_FREE_LICENSE_KEY])) {

            // License key is successfully activated: register the key in database

            $key = $info[\XLite\Core\Marketplace::XC_FREE_LICENSE_KEY][0];

            // Get key value from the response field 'key' or (if empty) use default value
            $keyValue = !empty($key[\XLite\Core\Marketplace::FIELD_KEY])
                ? $key[\XLite\Core\Marketplace::FIELD_KEY]
                : \XLite\Core\Marketplace::XC_FREE_LICENSE_KEY;

            \XLite\Core\Database::getRepo('\XLite\Model\ModuleKey')->insert(
                $key + array(
                    'keyValue' => $keyValue,
                )
            );

            // Clear cache for proper installation
            \XLite\Core\Marketplace::getInstance()->clearActionCache(
                \XLite\Core\Marketplace::ACTION_GET_ADDONS_LIST
            );

            // If there is at least one enabled module from non-free edition we will rebuild the cache
            if (\XLite\Core\Database::getRepo('XLite\Model\Module')->getNonFreeEditionModulesList(true)) {
                \XLite::setCleanUpCacheFlag(true);
            }

            \XLite\Core\TopMessage::addInfo('Free license is activated successfully');

            $this->setReturnURL($this->buildURL());

        } else {
            \XLite\Core\TopMessage::addError('Can\'t connect to the marketplace server');
        }
    }

    /**
     * Request marketplace to resend license key info on the specified email
     *
     * @return void
     */
    protected function doActionResendKey()
    {
        $result = \XLite\Core\Marketplace::getInstance()->doResendLicenseKey(\XLite\Core\Request::getInstance()->email);

        if ($result) {
            \XLite\Core\TopMessage::addInfo('Information about free license key has been sent', array('email' => \XLite\Core\Request::getInstance()->email));

        } else {
            \XLite\Core\TopMessage::addError(\XLite\Core\Marketplace::getInstance()->getError());
        }

        $this->setReturnURL($this->buildURL('activate_free_license'));
    }
}
