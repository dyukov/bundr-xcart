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
 * Google auth provider
 */
class GoogleAuthProvider extends AAuthProvider
{
    /**
     * Unique auth provider name
     */
    const PROVIDER_NAME = 'google';

    /**
     * Url to which user will be redirected
     */
    const AUTH_REQUEST_URL = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * Data to gain access to
     */
    const AUTH_REQUEST_SCOPE = 'https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email';

    /**
     * Url to get access token
     */
    const TOKEN_REQUEST_URL = 'https://accounts.google.com/o/oauth2/token';

    /**
     * Url to access user profile information
     */
    const PROFILE_REQUEST_URL = 'https://www.googleapis.com/oauth2/v1/userinfo';

    /**
     * Process authorization grant and return array with profile data
     *
     * @return array Client information containing at least id and e-mail
     */
    public function processAuth()
    {
        $profile = parent::processAuth();

        if (isset($profile['email'])) {
            $profile['id'] = $profile['email'];
        }

        return $profile;
    }

    /**
     * Returns access token based on authorization code
     *
     * @param string $code Authorization code
     *
     * @return string
     */
    protected function getAccessToken($code)
    {
        $request = new \XLite\Core\HTTP\Request(static::TOKEN_REQUEST_URL);
        $request->body = array(
            'code'          => $code,
            'client_id'     => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri'  => $this->getRedirectUrl(),
            'grant_type'    => 'authorization_code',
        );

        $response = $request->sendRequest();

        $accessToken = null;
        if (200 == $response->code) {
            $data = json_decode($response->body, true);
            $accessToken = $data['access_token'];
        }

        return $accessToken;
    }

    /**
     * Get OAuth 2.0 client ID
     *
     * @return string
     */
    protected function getClientId()
    {
        return \XLite\Core\Config::getInstance()->CDev->SocialLogin->gg_client_id;
    }

    /**
     * Get OAuth 2.0 client secret
     *
     * @return string
     */
    protected function getClientSecret()
    {
        return \XLite\Core\Config::getInstance()->CDev->SocialLogin->gg_client_secret;
    }
}
