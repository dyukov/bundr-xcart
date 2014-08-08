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

/**
 * X-Cart installation settings
 */

if (!defined('XLITE_INSTALL_MODE')) {
    die('Incorrect call of the script. Stopping.');
}

// Current X-Cart version
define('LC_VERSION', '5.1.4');

// Minimum PHP version supported
define('LC_PHP_VERSION_MIN', '5.3.0');

// Maximum PHP version supported (none if empty)
define('LC_PHP_VERSION_MAX', '');

// Minimum memory_limit option value (php.ini)
define('LC_PHP_MEMORY_LIMIT_MIN', PHP_INT_SIZE === 8 ? '256M' : '128M');

// Minimum MySQL version supported
define('LC_MYSQL_VERSION_MIN', '5.0.3');

// Default config file name
define('LC_DEFAULT_CONFIG_FILE', 'config.default.php');

// Config file name
define('LC_CONFIG_FILE', 'config.php');

// Other X-Cart settings
global $lcSettings;

$lcSettings = array(

    // Default language code
    'default_language_code' => XLITE_EDITION_LNG,

    // PHP versions that are not supported
    'forbidden_php_versions' => array(),

    'mustBeWritable' => array(
        'var',
        'images',
        'files',
        'etc' . LC_DS . 'config.php',
        '.htaccess',
    ),

    // The list of directories that should have writeble permissions
    'writable_directories' => array(
        'var',
        'images',
        'files',
    ),

    // The list of directories that should be created by installation script
    'directories_to_create' => array(
    ),

    // The list of files that should be created by installation script
    'files_to_create' => array(),

    // YAML files list
    'yaml_files' => array(
        'base' => array(
            'sql' . LC_DS . 'xlite_data.yaml',
        ),
        'demo' => XLITE_EDITION_LNG === 'ru' ? array(
            'sql' . LC_DS . 'xlite_demo.yaml',
            'sql' . LC_DS . 'xlite_demo_orders_ru.yaml',
            'sql' . LC_DS . 'xlite_demo_ru.yaml',
            'sql' . LC_DS . 'xlite_data_ru.yaml',
            'sql' . LC_DS . 'xlite_demo_sale.yaml',
            'sql' . LC_DS . 'product_attributes.sql',
        ) : array(
            'sql' . LC_DS . 'xlite_demo.yaml',
            'sql' . LC_DS . 'xlite_demo_orders_en.yaml',
            'sql' . LC_DS . 'xlite_demo_sale.yaml',
            'sql' . LC_DS . 'product_attributes.sql',
        ),
    ),

    // The list of modules that must be enabled by installation script
    'enable_modules' => array(
        'CDev' => array(
            'AuthorizeNet',
            'Bestsellers',
            'ContactUs',
            'Coupons',
            'FeaturedProducts',
            'FedEx',
            'GoogleAnalytics',
            'GoSocial',
            'InstantSearch',
            'ProductAdvisor',
            'Sale',
            'SimpleCMS',
            'SocialLogin',
            'TinyMCE',
            'TwoCheckout',
            'UserPermissions',
            'XMLSitemap',
            'VolumeDiscounts',
            'Wholesale',
        ),
        'XC' => array(
            'ColorSchemes',
    	    'ThemeTweaker',
            'Add2CartPopup',
            'Upselling',
            'Reviews',
            'UPS',
        ),
    ),
);

if (XLITE_EDITION_LNG === 'ru') {
    $lcSettings['enable_modules'] = array_merge_recursive(
        $lcSettings['enable_modules'],
        array(
            'CDev' => array(
                // RU edition
                'RuTranslation',
                'PaypalWPS',
            ),
            'XC' => array(
                // RU edition
                'EMS',
                'Robokassa',
                'Qiwi',
                'Webmoney',
            ),
        )
    );
} elseif (XLITE_EDITION_LNG === 'en') {
    $lcSettings['enable_modules'] = array_merge_recursive(
        $lcSettings['enable_modules'],
        array(
            'CDev' => array(
                // EN edition
                'Paypal',
                'Moneybookers',
                'XPaymentsConnector',
                'Quantum',
                'AuthorizeNet',
                'TwoCheckout',
                'USPS',
            ),
            'XC' => array(
                // EN edition
                'EPDQ',
                'IdealPayments',
                'Stripe',
                'SagePay',
                'CanadaPost',
            ),
        )
    );
}

//if (defined('DRUPAL_CMS_INSTALL_MODE')) {
//    $lcSettings['enable_modules']['CDev'][] = 'DrupalConnector';
//}

if (defined('CMS_INSTALL_SETTINGS_CALLBACK') && function_exists(CMS_INSTALL_SETTINGS_CALLBACK)) {
    $lcSettings = call_user_func(CMS_INSTALL_SETTINGS_CALLBACK, $lcSettings);
}

