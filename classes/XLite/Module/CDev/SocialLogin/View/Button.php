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

namespace XLite\Module\CDev\SocialLogin\View;

/**
 * Abstract sign-in button
 */
abstract class Button extends \XLite\View\AView
{
    /**
     * Widget icon path
     */
    const ICON_PATH = 'modules/CDev/SocialLogin/icons/default.png';

    /**
     * Returns an instance of auth provider
     *
     * @return \XLite\Module\CDev\SocialLogin\Core\AAuthProvider
     */
    abstract protected function getAuthProvider();

    /**
     * Get widget display name
     *
     * @return string
     */
    public function getName()
    {
        return static::DISPLAY_NAME;
    }

    /**
     * Get path to auth provider icon
     *
     * @return string
     */
    public function getIconPath()
    {
        return static::ICON_PATH;
    }

    /**
     * Get web path to a provider's icon
     *
     * @param string $iconPath Icon path relative to skins directory
     *
     * @return string Icon web path
     */
    public function getIconWebPath($iconPath)
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            $iconPath,
            \XLite\Core\Layout::WEB_PATH_OUTPUT_URL
        );
    }

    /**
     * Get authentication request url
     *
     * @return string
     */
    public function getAuthRequestUrl()
    {
        $state = get_class(\XLite::getController());

        return $this->getAuthProvider()->getAuthRequestUrl($state);
    }

    /**
     * Return default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/SocialLogin/button.tpl';
    }

    /**
     * Check if widget is visible
     * (auth provider must be fully configured)
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getAuthProvider()->isConfigured();
    }
}
