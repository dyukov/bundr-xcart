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

namespace XLite\Module\CDev\XPaymentsConnector\Core;

/**
 * XPayments connector tabs and settings
 *
 */
class Settings extends \XLite\Base\Singleton
{
    /**
     * Tabs/sections
     */
    const SECTION_PAYMENT_METHODS = 'payment_methods';
    const SECTION_CONNECTION      = 'connection';
    const SECTION_ZERO_AUTH       = 'zero_auth';
    const SECTION_MAP_RULES       = 'map_rules';
    const SECTION_WELCOME         = 'welcome';

    /**
     * Default error text 
     */
    const TXT_CONNECT_FAILED = 'Test transaction failed. Please check the X-Payment Connector settings and try again. 
                                If all options is ok review your X-Payments settings and make sure you have properly 
                                defined shopping cart properties.';

    /**
     * URL to grab the list of X-Payments allowed payment methods
     */
    const LIST_URL = 'http://www.x-cart.com/xml/xp.modules.xml';

    /**
     * How often load list of allowed modules from site
     */
    const ALLOWED_MODULES_CACHE_TTL = 86400;

    /**
     * List of API versions 
     */
    public $apiVersions = array(
        '1.3',
        '1.2',
        '1.1',
    );

    /**
     * List of configuration fields separated by sections 
     */
    public $sectionFields = array(

        self::SECTION_WELCOME => array(),

        self::SECTION_PAYMENT_METHODS => array(),

        self::SECTION_CONNECTION => array(
            'xpc_shopping_cart_id',
            'xpc_xpayments_url',
            'xpc_public_key',
            'xpc_private_key',
            'xpc_private_key_password',
            'xpc_allowed_ip_addresses',
            'xpc_currency',
            'xpc_api_version',
            'xpc_use_iframe',
        ),

        self::SECTION_ZERO_AUTH => array(
            'xpc_zero_auth_method_id',
            'xpc_zero_auth_amount',
            'xpc_zero_auth_description',
        ),

        self::SECTION_MAP_RULES => array(
            'xpc_status_new',
            'xpc_status_auth',
            'xpc_status_charged',
            'xpc_status_charged_part',
            'xpc_status_declined',
            'xpc_status_refunded',
            'xpc_status_refunded_part',
        ),
    );

    /**
     * Map fields
     *
     * @var array
     */
    protected $mapFields = array(
        'store_id'             => 'xpc_shopping_cart_id',
        'url'                  => 'xpc_xpayments_url',
        'public_key'           => 'xpc_public_key',
        'private_key'          => 'xpc_private_key',
        'private_key_password' => 'xpc_private_key_password',
    );

    /**
     * Required fields
     *
     * @var array
     */
    protected $requiredFields = array(
        'store_id',
        'url',
        'public_key',
        'private_key',
        'private_key_password',
    );

    /**
     * Get all sections
     *
     * @return string
     */
    public static function getAllSections() 
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->isModuleConfigured()
            ? array(
                static::SECTION_PAYMENT_METHODS,
                static::SECTION_CONNECTION,
                static::SECTION_ZERO_AUTH,
                static::SECTION_MAP_RULES,
              )
            : array( 
                static::SECTION_WELCOME,
                static::SECTION_CONNECTION,
            );
    }

    /**
     * Check - is payment configurations imported early or not
     *
     * @return boolean
     */
    public static function hasPaymentMethods($processor = 'XPayments')
    {
        return 0 < count(static::getPaymentMethods($processor));
    }

    /**
     * Get payment methods
     *
     * @return array
     */
    public static function getPaymentMethods($processor = 'XPayments')
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->class = 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\\' . $processor;

        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')->search($cnd);
    }

    /**
     * Get default section
     *
     * @return string
     */
    public function getDefaultSection()
    {
        return \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->isModuleConfigured()
            ? static::SECTION_PAYMENT_METHODS
            : static::SECTION_WELCOME;
    }

    /**
     * Get list of fields for section 
     *
     * @param string $section Section name
     *
     * @return array
     */
    public function getFieldsForSection($section = '')
    {
        $fields = isset($this->sectionFields[$section])
            ? $this->sectionFields[$section]
            : array();

        // Remove currency setting for API 1.3 and higher
        if (
            static::SECTION_CONNECTION == $section
            && version_compare(\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version, '1.3') >= 0
        ) {

            $key = array_search('xpc_currency', $fields);

            if ($key !== false) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }

    /**
     * Test connection
     *
     * @return string
     */
    public function testConnection($silent = true)
    {
        $result = false;

        if ($silent) {

            // Test connection using API version from settings
            $response = \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->requestTest();
            $result = (true === $response['status']);

        } else {

            foreach ($this->apiVersions as $version) {

                $response = \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->requestTest($version);

                if (true === $response['status']) {

                    if (\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version !== $version) {

                        $apiVersionSetting = \XLite\Core\Database::getRepo('XLite\Model\Config')
                            ->findOneBy(array('name' => 'xpc_api_version', 'category' => 'CDev\XPaymentsConnector'));

                        \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                            $apiVersionSetting,
                            array('value' => $version)
                        );
                        // Update config data
                        \XLite\Core\Config::updateInstance();
                    }

                    \XLite\Core\TopMessage::addInfo('Test transaction completed successfully for API version ' . $version);

                    $result = true;
                    break;
                }
            }

            if (true !== $result) {

                $message = (false === $response['status'])
                    ? $response['response']
                    : $response['response']['message'];

                \XLite\Core\TopMessage::addWarning(self::TXT_CONNECT_FAILED);

                if ($message) {
                    \XLite\Core\TopMessage::addError($message);
                }
            }
        }

        return $result;
    }

    // {{{ Deploy configuration

    /**
     * Check and deploy configuration
     *
     * @param string $deployConfig String containing a deployment configuration
     *
     * @return string
     */
    public function deployConfiguration($deployConfig)
    {
        $xpcConfig = $this->getConfiguration($deployConfig);

        $errorMsg = '';

        if (true === $this->checkDeployConfiguration($xpcConfig)) {

            $this->setConfiguration($xpcConfig);

            \XLite\Core\Config::updateInstance();

            if (true !== \XLite\Module\CDev\XPaymentsConnector\Core\Settings::getInstance()->testConnection(false)) {

                $errorMsg = 'Configuration has been deployed, but X-Cart is unable to connect to X-Payments';

            } else {

                $this->importPaymentMethods();

            }

        } else {
            $errorMsg = 'Your configuration string is not correct';
        }

        return $errorMsg;
    }

    /**
     * Get configuration array from configuration deployement path
     *
     * @return array
     */
    protected function getConfiguration($deployConfig)
    {
        return unserialize(base64_decode($deployConfig));
    }

    /**
     * Check if the deploy configuration is correct array
     *
     * @param array $configuration Configuration array
     *
     * @return boolean
     */
    protected function checkDeployConfiguration($configuration)
    {
        return is_array($configuration)
            && ($this->requiredFields === array_intersect(array_keys($configuration), $this->requiredFields));
    }

    /**
     * Store configuration array into DB
     *
     * @param array $configuration Configuration array
     *
     * @return void
     */
    protected function setConfiguration($configuration)
    {
        foreach ($this->mapFields as $origName => $dbName) {
            $setting = \XLite\Core\Database::getRepo('XLite\Model\Config')
                ->findOneBy(array('name' => $dbName, 'category' => 'CDev\XPaymentsConnector'));

            \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                $setting,
                array('value' => $configuration[$origName])
            );
        }
    }

    // }}}

    /**
     * Get Human Readable name for 3-D Secure type 
     *
     * @return array
     */
    protected static function get3DSecureType($secure3d)
    {
        $secure3dType = '';

        switch ($secure3d) {

            case '0' :
                $secure3dType = 'Not supported';
                break;

            case '1' :
                $secure3dType = 'via Cardinal Commerce';
                break;

            case '2' :
            case '3' :
                $secure3dType = 'Internal';
                break;

            default:

        }

        return $secure3dType;
    }

    /**
     * Load XML list of X-Payments allowed payment methods
     *
     * @return string 
     */
    protected static function getModulesXml()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::LIST_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $xml = curl_exec($ch);

        curl_close($ch);

        if (false === $xml) {

            // Use previously loaded XML in case of any error
            $xml = \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_allowed_modules;

        }

        return $xml;
    }

    /**
     * Parse XML of X-Payments allowed payment methods into array 
     *
     * @return array
     */
    protected function getAllowedModulesList()
    {
        $xml = self::getModulesXml();
        $json = json_encode(simplexml_load_string($xml));
        $modules = json_decode($json, true);

        $list = array();

        if (
            isset($modules['module'])
            && !empty($modules['module'])
            && is_array($modules['module'])
        ) {

            $modules = $modules['module'];

            foreach ($modules as $id => $module) {

                if (
                    isset($module['name'])
                    && isset($module['canSaveCards'])
                    && isset($module['secure3d'])
                    && isset($module['transactions'])
                ) {

                    $list[$module['name']] = array(
                        'id'            => $id,
                        'name'          => $module['name'],
                        'secure3d'      => $module['secure3d'],
                        'secure3dType'  => self::get3DSecureType($module['secure3d']),
                        'canSaveCards'  => $module['canSaveCards'],
                        'transactions'  => is_array($module['transactions'])
                            ? $module['transactions']
                            : array($module['transactions']),
                    );

                }

            }

            if (!empty($list)) {

                $allowedModulesSetting = \XLite\Core\Database::getRepo('XLite\Model\Config')
                    ->findOneBy(array('name' => 'xpc_allowed_modules', 'category' => 'CDev\XPaymentsConnector'));
                \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                    $allowedModulesSetting,
                    array('value' => $xml)
                );

                $allowedModulesLoadDateSetting = \XLite\Core\Database::getRepo('XLite\Model\Config')
                    ->findOneBy(array('name' => 'xpc_allowed_modules_load_date', 'category' => 'CDev\XPaymentsConnector'));
                \XLite\Core\Database::getRepo('XLite\Model\Config')->update(
                    $allowedModulesLoadDateSetting,
                    array('value' => \XLite\Core\Converter::time())
                );
                
                \XLite\Core\Config::updateInstance();

            }

        }

        return $list;
    }

    // {{{ Import payment methods

    /**
     * Check if it's necessary to update list of allowed payment methods by date 
     *
     * @return bool 
     */
    protected static function checkUpdateAllowedModulesLoadDate()
    {
        $currentDate = \XLite\Core\Converter::time();
        $loadDate = \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_allowed_modules_load_date;

        return $currentDate - $loadDate > self::ALLOWED_MODULES_CACHE_TTL;
    }

    /**
     * Check if it's necessary to update list of allowed payment methods 
     *
     * @return bool
     */
    public static function checkUpdateAllowedModules()
    {
        return !\XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->isModuleConfigured()
            && (
                !self::hasPaymentMethods('XPaymentsAllowed')
                || self::hasPaymentMethods('XPayments')
                || self::hasPaymentMethods('SavedCard')
                || self::checkUpdateAllowedModulesLoadDate()  
            );
    }


    /**
     * Import Payment methods allowed for X-Payments
     *
     * @return array
     */
    public function importAllowedModules()
    {
        $list = $this->getAllowedModulesList();

        if (is_array($list) && !empty($list)) {

            foreach ($this->getPaymentMethods('XPaymentsAllowed') as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }

            foreach ($this->getPaymentMethods('XPayments') as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }

            foreach ($this->getPaymentMethods('SavedCard') as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }

            foreach ($list as $module) {

                $pm = new \XLite\Model\Payment\Method;
                \XLite\Core\Database::getEM()->persist($pm);
                $pm->setClass('Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPaymentsAllowed');
                $pm->setServiceName('XPayments.Allowed.' . $module['id']);
                $pm->setName($module['name']);
                $pm->setType(\XLite\Model\Payment\Method::TYPE_CC_GATEWAY);
            }

            \XLite\Core\Database::getEM()->flush();

        }
        
    }

    /**
     * Import payment methods from X-Payments and return error or warning message (if any)
     *
     * @return void 
     */
    public function importPaymentMethods()
    {
        $list = \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->requestPaymentMethods();

        if (is_array($list) && !empty($list)) {

            foreach ($this->getPaymentMethods() as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }

            foreach ($this->getPaymentMethods('XPaymentsAllowed') as $pm) {
                \XLite\Core\Database::getEM()->remove($pm);
            }

            foreach ($list as $settings) {

                $pm = new \XLite\Model\Payment\Method;
                \XLite\Core\Database::getEM()->persist($pm);
                $pm->setClass('Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments');
                $pm->setServiceName('XPayments.' . $settings['id']);
                $pm->setName($settings['moduleName']);
                $pm->setType(\XLite\Model\Payment\Method::TYPE_CC_GATEWAY);
                $pm->setAdded(true);

                $this->setPaymentMethodSettings($pm, $settings);

                // Tokenization is disabled by default
                $pm->setSetting('saveCards', 'N');
            }

            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo('Payment methods have been imported successfully');

        } elseif (is_array($list)) {

            \XLite\Core\TopMessage::addWarning('There are no payment configurations for this store.');

        } else {

            \XLite\Core\TopMessage::addError('Error had occured during the requesting of payment methods from X-Payments. See log files for details.');

        }
    }

    /**
     * Set payment method settings
     *
     * @param \XLite\Model\Payment\Method $pm       Payment method
     * @param array                       $settings Settings
     *
     * @return void
     */
    protected function setPaymentMethodSettings(\XLite\Model\Payment\Method $pm, array $settings)
    {
        foreach ($settings as $k => $v) {

            if (is_array($v)) {

                $this->setPaymentMethodSettings($pm, $v);

            } elseif ('currency' == $k) {

                $currency = \XLite\Core\Database::getRepo('XLite\Model\Currency')->findOneByCode($v);

                if (is_object($currency)) {
                    $pm->setSetting($k, $currency->getCurrencyId());
                } else {
                    $pm->setSetting($k, '840'); // USD
                }


            } else {
                $pm->setSetting($k, $v);
            }
        }

        // Consider that all methods can save cards for old X-Payments
        if (version_compare(\XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_api_version, '1.3') < 0) {
            $pm->setSetting('canSaveCards', 'Y');
        }
    }

    // }}}
}
