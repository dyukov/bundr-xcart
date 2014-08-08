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

namespace XLite\View;

/**
 * CoreVersionTopLink
 */
class CoreVersionTopLink extends \XLite\View\AView
{
    /**
     * Flags
     *
     * @var array
     */
    protected $updateFlags;


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'top_links/version_notes/body.tpl';
    }

    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    protected function checkACL()
    {
        return parent::checkACL()
            && \XLite\Core\Auth::getInstance()->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS);
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Auth::getInstance()->isAdmin();
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getCurrentCoreVersion()
    {
        return \XLite::getInstance()->getVersion();
    }

    /**
     * Alias
     *
     * @return string
     */
    protected function getEditionName()
    {
        $result = '';

        $license = \XLite::getXCNLicense();

        if ($license && ($keyData = $license->getKeyData()) && !empty($keyData['editionName'])) {

            if (!is_array($keyData)) {
                $keyData = unserialize($keyData);
            }

            if (isset($keyData['editionName'])) {
                $result = $keyData['editionName'];
            }
        }

        return $result;
    }

    /**
     * Check if there is a new core version
     *
     * @return boolean
     */
    protected function isCoreUpgradeAvailable()
    {
        $flags = $this->getUpdateFlags();

        return !empty($flags[\XLite\Core\Marketplace::FIELD_IS_UPGRADE_AVAILABLE]);
    }

    /**
     * Check if there are updates (new core revision and/or module revisions)
     *
     * @return boolean
     */
    protected function areUpdatesAvailable()
    {
        $flags = $this->getUpdateFlags();

        return !empty($flags[\XLite\Core\Marketplace::FIELD_ARE_UPDATES_AVAILABLE]);
    }

    /**
     * Return upgrade flags
     *
     * @return array
     */
    protected function getUpdateFlags()
    {
        if (!isset($this->updateFlags)) {
            $this->updateFlags = \XLite\Core\Marketplace::getInstance()->checkForUpdates();
        }

        return is_array($this->updateFlags) ? $this->updateFlags : array();
    }

    /**
     * Check if notice should be displayed in the header
     *
     * @return boolean
     */
    protected function isNoticeActive()
    {
        return !\XLite::getXCNLicense();
    }

    /**
     * Get notice message
     *
     * @return string
     */
    protected function getNoticeMessage()
    {
        return \XLite::isTrialPeriodExpired()
            ? static::t('Trial period is expired')
            : (
                0 < \XLite::getTrialPeriodLeft()
                ? static::t('Trial period X days', array('X' => \XLite::getTrialPeriodLeft()))
                : static::t('Trial period expires today')
            );
    }
}
