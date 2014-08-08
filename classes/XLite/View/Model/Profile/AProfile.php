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

namespace XLite\View\Model\Profile;

/**
 * Profile model widget
 */
abstract class AProfile extends \XLite\View\Model\AModel
{
    /**
     * Return model object to use
     *
     * @return \XLite\Model\Profile
     */
    public function getModelObject()
    {
        $profile = parent::getModelObject();

        // Reset profile if it's not valid
        if (!\XLite\Core\Auth::getInstance()->checkProfile($profile)) {
            $profile = \XLite\Model\CachingFactory::getObject(__METHOD__, '\XLite\Model\Profile');
        }

        return $profile;
    }

    /**
     * getRequestProfileId
     *
     * @return integer|void
     */
    public function getRequestProfileId()
    {
        return \XLite\Core\Request::getInstance()->profile_id;
    }

    /**
     * Return current profile ID
     *
     * @return integer
     */
    public function getProfileId()
    {
        return $this->getRequestProfileId() ?: \XLite\Core\Session::getInstance()->profile_id;
    }


    /**
     * This object will be used if another one is not pased
     *
     * @return \XLite\Model\Profile
     */
    protected function getDefaultModelObject()
    {
        $obj = \XLite\Core\Database::getRepo('XLite\Model\Profile')->find($this->getProfileId());

        if (!isset($obj)) {
            $obj = new \XLite\Model\Profile();
        }

        return $obj;
    }

    /**
     * Return name of web form widget class
     *
     * @return string
     */
    protected function getFormClass()
    {
        return '\XLite\View\Form\Profile';
    }

    /**
     * Return text for the "Submit" button
     *
     * @return string
     */
    protected function getSubmitButtonLabel()
    {
        return \XLite\Core\Auth::getInstance()->isLogged() ? 'Update profile' : 'Create new account';
    }

    /**
     * Return list of the "Button" widgets
     *
     * @return array
     */
    protected function getFormButtons()
    {
        $result = parent::getFormButtons();

        $result['submit'] = new \XLite\View\Button\Submit(
            array(
                \XLite\View\Button\AButton::PARAM_LABEL => $this->getSubmitButtonLabel(),
            )
        );

        return $result;
    }
}
