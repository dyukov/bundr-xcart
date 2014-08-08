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

namespace XLite\View\Tabs;

/**
 * Tabs related to user profile section
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class AdminProfile extends \XLite\View\Tabs\ATabs
{
    /**
     * User profile object
     *
     * @var \XLite\Model\Profile
     */
    protected $profile;

    /**
     * Description of tabs related to user profile section and their targets
     *
     * @var array
     */
    protected $tabs = array(
        'profile' => array(
            'title'    => 'Account details',
            'template' => 'profile/account.tpl',
        ),
        'address_book' => array(
            'title'    => 'Address book',
            'template' => 'profile/address_book.tpl',
        ),
    );


    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (\XLite\Controller\Admin\Profile::getInstance()->isRegisterMode()) {
            foreach ($this->tabs as $key => $tab) {
                if ('profile' != $key) {
                    unset($this->tabs[$key]);
                }
            }
        }
    }

    /**
     * getProfile
     *
     * @return \XLite\Model\Profile
     */
    public function getProfile()
    {
        if (!isset($this->profile)) {
            $profileId = \XLite\Core\Request::getInstance()->profile_id;

            if (isset($profileId)) {
                $this->profile = \XLite\Core\Database::getRepo('XLite\Model\Profile')->find($profileId);

            } else {
                $this->profile = \XLite\Core\Auth::getInstance()->getProfile();
            }
        }

        return $this->profile;
    }


    /**
     * Returns an URL to a tab
     *
     * @param string $target Tab target
     *
     * @return string
     */
    protected function buildTabURL($target)
    {
        $profileId = \XLite\Core\Request::getInstance()->profile_id;

        return $this->buildURL($target, '', isset($profileId) ? array('profile_id' => $profileId) : array());
    }
}
