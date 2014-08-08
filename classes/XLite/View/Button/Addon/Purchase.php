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

namespace XLite\View\Button\Addon;

/**
 * Purchase module button-link
 *
 */
class Purchase extends \XLite\View\Button\AButton
{
    const PARAM_MODULE = 'moduleObj';

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/addon/purchase.tpl';
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_MODULE => new \XLite\Model\WidgetParam\Object('Module', null, false, '\XLite\Model\Module'),
        );
    }

    /**
     * Return button text
     *
     * @return string
     */
    protected function getButtonLabel()
    {
        return 'Purchase';
    }

    /**
     * Define the button type (btn-warning and so on)
     *
     * @return string
     */
    protected function getDefaultButtonType()
    {
        return 'regular-main-button';
    }

    /**
     * Return button CSS class
     *
     * @return string
     */
    protected function getClass()
    {
        return parent::getClass() . ' purchase-module';
    }

    /**
     * Get JS code
     *
     * @return string
     */
    protected function getJSCode()
    {
        return 'onclick="javascript:self.location=\'' . $this->getPurchaseURL() . '\'"';
    }

    /**
     * Defines the purchase URL in the marketplace specific for the module provided
     *
     * @return string
     */
    protected function getPurchaseURL()
    {
        $apiURL = trim(
            \Includes\Utils\Converter::trimTrailingChars(
                \XLite\Core\Marketplace::getInstance()->getMarketplaceURL()
                , '/'
            )
        );

        // Remove 'api' directory
        $apiURL = preg_replace('/\?q=.+/Ss', '', $apiURL);
        $apiURL = preg_replace('/\/api$/Ss', '/', $apiURL);

        return $apiURL . '?' . http_build_query($this->getFields());
    }

    /**
     * Defines the URL parameters which are specific for the module in the purchase link
     *
     * @return array
     */
    protected function getFields()
    {
        $module = $this->getParam(static::PARAM_MODULE);

        $result = array(
            'q'          => 'pay',
            'name'       => $module->getName(),
            'author'     => $module->getAuthor(),
            'return_url' => $this->getReturnURL(),
            'email'      => \XLite\Core\Auth::getInstance()->getProfile()->getLogin(),
        );

        $affiliateId = \XLite::getAffiliateId();
        if ($affiliateId) {
            $result['aff_id'] = $affiliateId;
        }

        return $result;
    }

    /**
     * Get return URL for Purchase operation
     *
     * @return string
     */
    protected function getReturnURL()
    {
        return \XLite\Core\URLManager::getShopURL(\XLite\Core\Converter::buildURL());
    }
}
