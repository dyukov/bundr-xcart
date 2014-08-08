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

namespace XLite\Module\CDev\SocialLogin\Controller\Customer;

/**
 * Authorization grants are routed to this controller
 */
class SocialLogin extends \XLite\Controller\Customer\ACustomer
{

    /**
     * Perform login action
     *
     * @return void
     */
    protected function doActionLogin()
    {
        $authProviders = \XLite\Module\CDev\SocialLogin\Core\AuthManager::getAuthProviders();

        $requestProcessed = false;

        foreach ($authProviders as $authProvider) {
            if ($authProvider->detectAuth()) {

                $profileInfo = $authProvider->processAuth();

                if ($profileInfo && !empty($profileInfo['id']) && !empty($profileInfo['email'])) {

                    $profile = $this->getSocialLoginProfile(
                        $profileInfo['email'],
                        $authProvider->getName(),
                        $profileInfo['id']
                    );

                    if ($profile) {
                        if ($profile->isEnabled()) {
                            \XLite\Core\Auth::getInstance()->loginProfile($profile);

                            // We merge the logged in cart into the session cart
                            $profileCart = $this->getCart();
                            $profileCart->login($profile);
                            \XLite\Core\Database::getEM()->flush();

                            if ($profileCart->isPersistent()) {
                                $this->updateCart();
                            }

                            $this->setAuthReturnURL($authProvider::STATE_PARAM_NAME);

                        } else {
                            \XLite\Core\TopMessage::addError('Profile is disabled');
                            $this->setAuthReturnURL($authProvider::STATE_PARAM_NAME, true);
                        }

                    } else {
                        $provider = \XLite\Core\Database::getRepo('XLite\Model\Profile')
                            ->findOneBy(array('login' => $profileInfo['email'], 'order' => null))
                            ->getSocialLoginProvider();

                        if ($provider) {
                            $signInVia = 'Please sign in with ' . $provider . '.';
                        } else {
                            $signInVia = 'Profile with the same e-mail address already registered. '
                                . 'Please sign in the classic way.';
                        }

                        \XLite\Core\TopMessage::addError($signInVia);
                        $this->setAuthReturnURL($authProvider::STATE_PARAM_NAME, true);
                    }

                    $requestProcessed = true;
                }
            }
        }

        if (!$requestProcessed) {
            \XLite\Core\TopMessage::addError('We were unable to process this request');
            $this->setAuthReturnURL('', true);
        }
    }

    /**
     * Fetches an existing social login profile or creates new
     *
     * @param string $login          E-mail address
     * @param string $socialProvider SocialLogin auth provider
     * @param string $socialId       SocialLogin provider-unique id
     *
     * @return \XLite\Model\Profile
     */
    protected function getSocialLoginProfile($login, $socialProvider, $socialId)
    {
        $profile = \XLite\Core\Database::getRepo('\XLite\Model\Profile')->findOneBy(
            array(
                'socialLoginProvider'   => $socialProvider,
                'socialLoginId'         => $socialId,
                'order'              => null,
            )
        );

        if (!$profile) {
            $profile = new \XLite\Model\Profile();
            $profile->setLogin($login);
            $profile->setSocialLoginProvider($socialProvider);
            $profile->setSocialLoginId($socialId);

            $existingProfile = \XLite\Core\Database::getRepo('XLite\Model\Profile')
                ->findOneBy(array('login' => $login, 'order' => null));

            if ($existingProfile) {
                $profile = null;
            } else {
                $profile->create();
            }
        }

        return $profile;
    }

    /**
     * Set redirect URL
     *
     * @param string $stateParamName Name of the state parameter containing
     *      class name of the controller that initialized auth request OPTIONAL
     * @param mixed  $failure        Indicates if auth process failed OPTIONAL
     *
     * @return void
     */
    protected function setAuthReturnUrl($stateParamName = '', $failure = false)
    {
        $controller = \XLite\Core\Request::getInstance()->$stateParamName;

        $redirectTo = $failure ? 'login' : '';

        if ('XLite\Controller\Customer\Checkout' == $controller) {
            $redirectTo = 'checkout';
        } elseif ('XLite\Controller\Customer\Profile' == $controller) {
            $redirectTo = 'profile';
        }

        $this->setReturnURL($this->buildURL($redirectTo));
    }
}
