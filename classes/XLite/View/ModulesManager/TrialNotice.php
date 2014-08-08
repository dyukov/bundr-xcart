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

namespace XLite\View\ModulesManager;

/**
 * Trial notice page
 *
 * @ListChild (list="admin.center", zone="admin", weight=0)
 */
class TrialNotice extends \XLite\View\ModulesManager\AModulesManager
{
    /**
     * The allowed targets for the trial notice is defined
     * in the static::getAllowedTargetsTrialNotice() method
     *
     * @see static::getAllowedTargetsTrialNotice()
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(
            parent::getAllowedTargets(),
            static::getAllowedTargetsTrialNotice()
        );
    }

    /**
     * The allowed targets for admin area are defined in the static::getAllowedAdminTargetsTrialNotice()
     * The allowed targets for customer area are defined in the static::getAllowedCustomerTargetsTrialNotice()
     *
     * @see static::getAllowedAdminTargetsTrialNotice()
     * @see static::getAllowedCustomerTargetsTrialNotice()
     *
     * @return array
     */
    public static function getAllowedTargetsTrialNotice()
    {
        return \XLite::isAdminZone()
            ? static::getAllowedAdminTargetsTrialNotice()
            : static::getAllowedCustomerTargetsTrialNotice();
    }

    /**
     * In the admin area the following targets are allowed.
     *
     * @return array
     */
    public static function getAllowedAdminTargetsTrialNotice()
    {
        return \XLite::isTrialPeriodExpired()
            ? array(
                'trial_notice', // the popup window target
                'recent_orders',
                'order',
                'order_list',
            ) : array(
                'trial_notice',
            );
    }

    /**
     * In the customer area the following targets are allowed.
     *
     * @return array
     */
    public static function getAllowedCustomerTargetsTrialNotice()
    {
        return array(
            'trial_notice',
            'main',
            'checkout',
        );
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/css/style.css';

        return $list;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'trial_notice';
    }

    /**
     * URL of the page where license can be purchased
     *
     * @return string
     */
    protected function getPurchaseURL()
    {
        return \XLite\Core\Marketplace::getPurchaseURL();
    }

    /**
     * URL of the X-Cart company's Contact Us page
     *
     * @return string
     */
    protected function getContactUsURL()
    {
        return \XLite\Core\Marketplace::getContactUsURL();
    }

    /**
     * URL of the X-Cart company's License Agreement page
     *
     * @return string
     */
    protected function getLicenseAgreementURL()
    {
        return \XLite\Core\Marketplace::getLicenseAgreementURL();
    }
    
}
