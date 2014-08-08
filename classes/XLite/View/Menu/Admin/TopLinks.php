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

namespace XLite\View\Menu\Admin;

/**
 * Top-right side drop down links
 */
class TopLinks extends \XLite\View\Menu\Admin\AAdmin
{

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/controller.js';

        return $list;
    }

    /**
     * Define items
     *
     * @return array
     */
    protected function defineItems()
    {
        $items = array(
            'store_setup' => array(
                static::ITEM_TITLE      => 'Store setup',
                static::ITEM_WEIGHT     => 100,
                static::ITEM_CHILDREN   => array(
                    'store_info' => array(
                        static::ITEM_TITLE      => 'Store info',
                        static::ITEM_TARGET     => 'settings',
                        static::ITEM_EXTRA      => array('page' => 'Company'),
                        static::ITEM_WEIGHT     => 100,
                    ),
                    'general' => array(
                        static::ITEM_TITLE      => 'Cart & checkout',
                        static::ITEM_TARGET     => 'general_settings',
                        static::ITEM_WEIGHT     => 200,
                    ),
                    'payment_settings' => array(
                        static::ITEM_TITLE      => 'Payments',
                        static::ITEM_TARGET     => 'payment_settings',
                        static::ITEM_WEIGHT     => 300,
                    ),
                    'countries' => array(
                        static::ITEM_TITLE      => 'Countries, states and zones',
                        static::ITEM_TARGET     => 'countries',
                        static::ITEM_WEIGHT     => 400,
                    ),
                    'shipping_methods' => array(
                        static::ITEM_TITLE      => 'Shipping',
                        static::ITEM_TARGET     => 'shipping_methods',
                        static::ITEM_WEIGHT     => 500,
                    ),
                    'tax_classes' => array(
                        static::ITEM_TITLE      => 'Taxes',
                        static::ITEM_TARGET     => 'tax_classes',
                        static::ITEM_WEIGHT     => 600,
                    ),
                    'localization' => array(
                        static::ITEM_TITLE      => 'Localization',
                        static::ITEM_TARGET     => 'units_formats',
                        static::ITEM_WEIGHT     => 700,
                    ),
                    'translations' => array(
                        static::ITEM_TITLE      => 'Translations',
                        static::ITEM_TARGET     => 'languages',
                        static::ITEM_WEIGHT     => 750,
                    ),

                    'storefront_status' => \XLite\Core\Auth::getInstance()->isClosedStorefront()
                        ? array(
                            static::ITEM_TITLE      => static::t('Your store'),
                            static::ITEM_BLOCK      => '<span class="storefront-title storefront-closed">' . static::t('Storefront is closed')
                                . '</span>[<a href="' . $this->getOpenStorefrontURL() . '">' . static::t('Open') . '</a>]'
                                . '<span class="private-link-storefront-block">'
                                . static::t('Access storefront via <a href="{{shop_url}}">private link</a>', array('shop_url' => $this->getAccessibleShopURL()))
                                . '</span>',
                            static::ITEM_WEIGHT     => 100000,
                            static::ITEM_CLASS      => 'frontend-closed',
                        )
                        : array(
                            static::ITEM_TITLE      => static::t('Your store'),
                            static::ITEM_BLOCK      => '<span class="storefront-title storefront-open">' . static::t('Storefront is open')
                                . '</span>[<a href="' . $this->getCloseStorefrontURL() . '">' . static::t('Close') . '</a>]',
                            static::ITEM_WEIGHT     => 100000,
                            static::ITEM_CLASS      => 'frontend-opened',
                        ),
                ),
            ),
            'system_settings' => array(
                static::ITEM_TITLE      => 'System settings',
                static::ITEM_WEIGHT     => 200,
                static::ITEM_CHILDREN   => array(
                    'db_backup' => array(
                        static::ITEM_TITLE      => 'Backup & Restore',
                        static::ITEM_TARGET     => 'db_backup',
                        static::ITEM_WEIGHT     => 100,
                    ),
                    'css_js' => array(
                        static::ITEM_TITLE      => 'Look & Feel',
                        static::ITEM_TARGET     => 'css_js_performance',
                        static::ITEM_WEIGHT     => 200,
                    ),
                    'rebuild_cache' => array(
                        static::ITEM_TITLE      => 'Rebuild cache',
                        static::ITEM_TARGET     => 'cache_management',
                        static::ITEM_EXTRA      => array('action' => 'rebuild'),
                        static::ITEM_CLASS      => 'rebuild-cache',
                        static::ITEM_WEIGHT     => 300,
                    ),
                    'environment' => array(
                        static::ITEM_TITLE      => 'Environment',
                        static::ITEM_TARGET     => 'settings',
                        static::ITEM_EXTRA      => array('page' => 'Environment'),
                        static::ITEM_WEIGHT     => 400,
                    ),
                    'view_log_file' => array(
                        static::ITEM_TITLE      => 'View system logs',
                        static::ITEM_TARGET     => 'upgrade',
                        static::ITEM_EXTRA      => array('action' => 'view_log_file'),
                        static::ITEM_WEIGHT     => 500,
                        static::ITEM_BLANK_PAGE => true,
                    ),
                    'email_settings' => array(
                        static::ITEM_TITLE      => 'Email settings',
                        static::ITEM_TARGET     => 'settings',
                        static::ITEM_EXTRA      => array('page' => 'Email'),
                        static::ITEM_WEIGHT     => 600,
                    ),
                    'safe_mode' => array(
                        static::ITEM_TITLE      => 'Safe mode',
                        static::ITEM_TARGET     => 'safe_mode',
                        static::ITEM_WEIGHT     => 700,
                    ),
                    'security_settings' => array(
                        static::ITEM_TITLE      => 'HTTPS settings',
                        static::ITEM_TARGET     => 'https_settings',
                        static::ITEM_WEIGHT     => 800,
                    ),
                ),
            ),
            'extensions' => array(
                static::ITEM_TITLE      => 'Extensions',
                static::ITEM_WEIGHT     => 300,
                static::ITEM_CHILDREN   => array(
                    'addons_list_installed' => array(
                        static::ITEM_TITLE      => 'Installed modules',
                        static::ITEM_TARGET     => 'addons_list_installed',
                        static::ITEM_WEIGHT     => 100,
                    ),
                    'addons_list_marketplace' => array(
                        static::ITEM_TITLE      => 'Marketplace',
                        static::ITEM_TARGET     => 'addons_list_marketplace',
                        static::ITEM_EXTRA      => array('landing' => '1'),
                        static::ITEM_WEIGHT     => 200,
                    ),
                ),
            ),
            'your_store' => array(
                static::ITEM_TITLE      => \XLite\Core\Auth::getInstance()->isClosedStorefront()
                    ? static::t('Your store') . ' <span class="closed">(' . static::t('Closed') . ')</span>'
                    : static::t('Your store'),
                static::ITEM_LINK       => $this->getShopURL(),
                static::ITEM_WEIGHT     => 1000,
                static::ITEM_CLASS      => 'your-store ' . (\XLite\Core\Auth::getInstance()->isClosedStorefront() ? 'closed' : 'open'),
                static::ITEM_ICON_CLASS => 'fa fa-desktop',
                static::ITEM_PUBLIC_ACCESS => true,
                static::ITEM_BLANK_PAGE    => true,
            ),
            'help' => array(
                static::ITEM_TITLE      => 'Help',
                static::ITEM_WEIGHT     => 2000,
                static::ITEM_CLASS      => 'help',
                static::ITEM_ICON_CLASS => 'fa fa-question-circle',
                static::ITEM_CHILDREN   => array(
                    'knoweledge_base' => array(
                        static::ITEM_TITLE      => 'Knowledge Base',
                        static::ITEM_LINK       => 'http://kb.x-cart.com/',
                        static::ITEM_WEIGHT     => 100,
                        static::ITEM_ICON_CLASS => 'fa fa-external-link',
                        static::ITEM_BLANK_PAGE => true,
                    ),
                    'suggest_idea' => array(
                        static::ITEM_TITLE      => 'Suggest an idea',
                        static::ITEM_LINK       => 'http://ideas.x-cart.com/forums/229428-x-cart-5-ideas',
                        static::ITEM_WEIGHT     => 200,
                        static::ITEM_ICON_CLASS => 'fa fa-external-link',
                        static::ITEM_BLANK_PAGE => true,
                    ),
                    'report_bug' => array(
                        static::ITEM_TITLE      => 'Report a bug',
                        static::ITEM_LINK       => 'http://bt.x-cart.com/',
                        static::ITEM_WEIGHT     => 300,
                        static::ITEM_ICON_CLASS => 'fa fa-external-link',
                        static::ITEM_BLANK_PAGE => true,
                    ),
                ),
            ),
            'account' => array(
                static::ITEM_TITLE      => 'Account',
                static::ITEM_WEIGHT     => 99999,
                static::ITEM_CLASS      => 'account',
                static::ITEM_CHILDREN   => array(
                    'account_info' => array(
                        static::ITEM_TITLE      => \XLite\Core\Auth::getInstance()->getProfile()->getLogin(),
                        static::ITEM_CLASS      => 'text',
                        static::ITEM_WEIGHT     => 100,
                        static::ITEM_PUBLIC_ACCESS => true,
                    ),
                    'profile' => array(
                        static::ITEM_TITLE      => 'Profile settings',
                        static::ITEM_TARGET     => 'profile',
                        static::ITEM_WEIGHT     => 200,
                        static::ITEM_PUBLIC_ACCESS => true,
                    ),
                    'logoff' => array(
                        static::ITEM_TITLE      => 'Sign out',
                        static::ITEM_CLASS      => 'logoff',
                        static::ITEM_TARGET     => 'login',
                        static::ITEM_EXTRA      => array('action' => 'logoff'),
                        static::ITEM_WEIGHT     => 99999,
                        static::ITEM_PUBLIC_ACCESS => true,
                    ),
                ),
            ),
        );

        return $items;
    }

    /**
     * Return widget directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'top_links';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/top_links.tpl';
    }

    /**
     * Get default widget
     *
     * @return string
     */
    protected function getDefaultWidget()
    {
        return 'XLite\View\Menu\Admin\TopLinks\Node';
    }

    /**
     * Returns the URL of action to open the storefront
     *
     * @return string
     */
    protected function getOpenStorefrontURL()
    {
        return $this->buildURL(
            'storefront',
            '',
            array(
                'action'    => 'open',
                'returnURL' => $this->getURL(),
            )
        );
    }

    /**
     * Returns the URL of action to close the storefront
     *
     * @return string
     */
    protected function getCloseStorefrontURL()
    {
        return $this->buildURL(
            'storefront',
            '',
            array(
                'action'    => 'close',
                'returnURL' => $this->getURL(),
            )
        );
    }
}
