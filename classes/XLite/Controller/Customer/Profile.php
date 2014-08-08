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

namespace XLite\Controller\Customer;

/**
 * User profile page controller
 */
class Profile extends \XLite\Controller\Customer\ACustomer
{
    /**
     * Types of model form
     */
    const SECTIONS_MAIN      = 'main';
    const SECTIONS_ADDRESSES = 'addresses';
    const SECTIONS_ALL       = 'all';

    /**
     * Return value for the "register" mode param
     *
     * @return string
     */
    public static function getRegisterMode()
    {
        return 'register';
    }

    /**
     * handleRequest
     *
     * @return void
     */
    public function handleRequest()
    {
        if (!$this->isLogged() && !$this->isRegisterMode()) {
            $this->setReturnURL($this->buildURL('login'));
        }

        return parent::handleRequest();
    }

    /**
     * Set if the form id is needed to make an actions
     * Form class uses this method to check if the form id should be added
     *
     * @return boolean
     */
    public static function needFormId()
    {
        return true;
    }

    /**
     * Check - controller must work in secure zone or not
     *
     * @return boolean
     */
    public function isSecure()
    {
        return \XLite\Core\Config::getInstance()->Security->customer_security;
    }

    /**
     * Returns title of the page
     *
     * @return void
     */
    public function getTitle()
    {
        return 'delete' == \XLite\Core\Request::getInstance()->mode
            ? 'Delete account'
            : 'Account details';
    }

    /**
     * Check whether the title is to be displayed in the content area
     *
     * @return boolean
     */
    public function isTitleVisible()
    {
        return 'delete' == \XLite\Core\Request::getInstance()->mode;
    }

    /**
     * The "mode" parameter used to determine if we create new or modify existing profile
     *
     * @return boolean
     */
    public function isRegisterMode()
    {
        return self::getRegisterMode() === \XLite\Core\Request::getInstance()->mode
            || !$this->getModelForm()->getModelObject()->isPersistent();
    }

    /**
     * Define current location for breadcrumbs
     *
     * @return string
     */
    protected function getLocation()
    {
        return 'Account details';
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode('My account');
    }

    /**
     * Return class name of the register form
     *
     * @return string|void
     */
    protected function getModelFormClass()
    {
        return '\XLite\View\Model\Profile\Main';
    }

    /**
     * Check if profile is not exists
     *
     * @return boolean
     */
    protected function doActionValidate()
    {
        return $this->getModelForm()->performAction('validateInput');
    }

    /**
     * doActionRegister
     *
     * @return boolean
     */
    protected function doActionRegister()
    {
        $result = $this->getModelForm()->performAction('create');
        $this->postprocessActionRegister();

        return $result;
    }

    /**
     * Postprocess register action
     *
     * @return void
     */
    protected function postprocessActionRegister()
    {
        $this->setReturnURL(
            $this->isActionError()
                // Return to the register page
                ? $this->buildURL('profile', '', $this->postprocessActionRegisterFail())
                // Return to the created account page
                : $this->buildURL('address_book', '', $this->postprocessActionRegisterSuccess())
        );
    }

    /**
     * Postprocess register action (fail)
     *
     * @return array
     */
    protected function postprocessActionRegisterFail()
    {
        // Return back to register page
        return array('mode' => self::getRegisterMode());
    }

    /**
     * Postprocess register action (success)
     *
     * @return array
     */
    protected function postprocessActionRegisterSuccess()
    {
        // Send notification to the user
        \XLite\Core\Mailer::sendProfileCreatedUserNotification($this->getModelForm()->getModelObject());

        // Send notification to the users department
        \XLite\Core\Mailer::sendProfileCreatedAdminNotification($this->getModelForm()->getModelObject());

        $params = array('profile_id' => $this->getModelForm()->getProfileId(false));

        $this->getCart()->login($this->getModelForm()->getModelObject());

        // Log in user with created profile
        \XLite\Core\Auth::getInstance()->loginProfile($this->getModelForm()->getModelObject());

        return $params;
    }

    /**
     * doActionUpdate
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $result = $this->getModelForm()->performAction('update');

        if ($result) {

            // Send notification to the user
            \XLite\Core\Mailer::sendProfileUpdatedUserNotification($this->getModelForm()->getModelObject());

            // Send notification to the users department
            \XLite\Core\Mailer::sendProfileUpdatedAdminNotification($this->getModelForm()->getModelObject());
        }

        return $result;
    }

    /**
     * doActionModify
     *
     * @return void
     */
    protected function doActionModify()
    {
        if ($this->isRegisterMode()) {

            $this->doActionRegister();

        } else {

            $this->doActionUpdate();
        }
    }

    /**
     * doActionDelete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        if (\XLite\Core\Auth::getInstance()->isAdmin()) {

            \XLite\Core\TopMessage::addWarning(
                static::t('Administrator account cannot be deleted via customer interface.')
            );

            $result = false;

        } else {

            $userLogin = $this->getModelForm()->getModelObject()->getLogin();

            $result = $this->getModelForm()->performAction('delete');

            if ($result) {
                // Send notification to the users department
                \XLite\Core\Mailer::sendProfileDeletedAdminNotification($userLogin);
            }

            $this->setHardRedirect();
            $this->setReturnURL($this->buildURL());
        }

        return $result;
    }
}
