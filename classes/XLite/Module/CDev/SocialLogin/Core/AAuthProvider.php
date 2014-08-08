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

namespace XLite\Module\CDev\SocialLogin\Core;

/**
 * Auth provider abstract class
 */
abstract class AAuthProvider extends \XLite\Base\Singleton
{
    /**
     * Authorization grant provider param name
     */
    const AUTH_PROVIDER_PARAM_NAME = 'auth_provider';

    /**
     * State parameter is used to maintain state between the request and callback
     */
    const STATE_PARAM_NAME = 'state';

    /**
     * Get OAuth 2.0 client ID
     *
     * @return string
     */
    abstract protected function getClientId();

    /**
     * Get OAuth 2.0 client secret
     *
     * @return string
     */
    abstract protected function getClientSecret();

    /**
     * Get authorization request url
     *
     * @param string $state State parameter to include in request
     *
     * @return string
     */
    public function getAuthRequestUrl($state)
    {
        return static::AUTH_REQUEST_URL
            . '?client_id=' . $this->getClientId()
            . '&redirect_uri=' . urlencode($this->getRedirectUrl())
            . '&scope=' . static::AUTH_REQUEST_SCOPE
            . '&response_type=code'
            . '&' . static::STATE_PARAM_NAME . '=' . urlencode($state);
    }

    /**
     * Get unique auth provider name to distinguish it from others
     *
     * @return string
     */
    public function getName()
    {
        return static::PROVIDER_NAME;
    }

    /**
     * Check if current request belongs to the concrete implementation of auth provider
     *
     * @return boolean
     */
    public function detectAuth()
    {
        return \XLite\Core\Request::getInstance()->{static::AUTH_PROVIDER_PARAM_NAME} == $this->getName();
    }

    /**
     * Process authorization grant and return array with profile data
     *
     * @return array Client information containing at least id and e-mail
     */
    public function processAuth()
    {
        $profile = array();

        $code = \XLite\Core\Request::getInstance()->code;

        if (!empty($code)) {
            $accessToken = $this->getAccessToken($code);

            if ($accessToken) {
                $request = new \XLite\Core\HTTP\Request($this->getProfileRequestUrl($accessToken));
                $response = $request->sendRequest();

                if (200 == $response->code) {
                    $profile = json_decode($response->body, true);
                }
            }
        }

        return $profile;
    }

    /**
     * Check if auth provider has all options configured
     *
     * @return boolean
     */
    public function isConfigured()
    {
        return $this->getClientId() && $this->getClientSecret();
    }

    /**
     * Get url to request access token
     *
     * @param string $code Authorization code
     *
     * @return string
     */
    protected function getTokenRequestUrl($code)
    {
        return static::TOKEN_REQUEST_URL
            . '?client_id=' . $this->getClientId()
            . '&redirect_uri=' . urlencode($this->getRedirectUrl())
            . '&client_secret=' . $this->getClientSecret()
            . '&code=' . urlencode($code);
    }

    /**
     * Get url used to access user profile info
     *
     * @param string $accessToken Access token
     *
     * @return string
     */
    protected function getProfileRequestUrl($accessToken)
    {
        return static::PROFILE_REQUEST_URL . '?access_token=' . urlencode($accessToken);
    }

    /**
     * Get authorization grant redirect url
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        return \XLite\Core\URLManager::getShopURL(
            \XLite\Core\Converter::buildURL(
                'social_login',
                'login',
                array('auth_provider' => $this->getName()),
                'cart.php'
            ),
            \XLite\Core\Request::getInstance()->isHTTPS(),
            array(),
            null,
            false
        );
    }
}
