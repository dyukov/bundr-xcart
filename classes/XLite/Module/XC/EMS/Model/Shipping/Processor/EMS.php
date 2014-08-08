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

namespace XLite\Module\XC\EMS\Model\Shipping\Processor;

/**
 * Shipping processor model
 * API: Postage Assessment Calculator (PAC)
 * API documentation: http://www.emspost.ru/ru/corp_clients/dogovor_docements/api/
 *
 * Shipments supported: Domestic(RU -> RU), International (RU -> Intl)
 */
class EMS extends \XLite\Model\Shipping\Processor\AProcessor
{
    /**
     * Unique processor Id
     *
     * @var string
     */
    protected $processorId = 'ems';

    /**
     * EMS live API URL
     *
     * @var string
     */
    protected $apiURL = 'http://emspost.ru';

    /**
     * API request types and some specifications
     *
     * @var array
     */
    protected $apiRequestTypes = array(
        'EmsTestEcho' =>  array(
            'uri' => '/api/rest/?method=ems.test.echo',
        ),
        'EmsGetMaxWeight' =>  array(
            'uri' => '/api/rest/?method=ems.get.max.weight',
        ),
        'EmsGetLocationsCities' => array(
            'uri' => '/api/rest/?method=ems.get.locations&type=cities&plain=true',
        ),
        'EmsGetLocationsRegions' => array(
            'uri' => '/api/rest/?method=ems.get.locations&type=regions&plain=true',
        ),
        'EmsGetLocationsCountries' => array(
            'uri' => '/api/rest/?method=ems.get.locations&type=countries&plain=true',
        ),
        'EmsGetLocationsRussia' => array(
            'uri' => '/api/rest/?method=ems.get.locations&type=russia&plain=true',
        ),
        'EmsCalculate' =>  array(
            'uri' => '/api/rest/?method=ems.calculate',
        ),
    );

    /**
     * Defines whether the form must be used for tracking information.
     * The 'getTrackingInformationURL' result will be used as tracking link instead
     *
     * @param string $trackingNumber Tracking number value
     *
     * @return boolean
     */
    public function isTrackingInformationForm($trackingNumber)
    {
        return false;
    }

    /**
     * This method must return the URL to the detailed tracking information about the package.
     * Tracking number is provided.
     *
     * @param string $trackingNumber
     *
     * @return null|string
     */
    public function getTrackingInformationURL($trackingNumber)
    {
        return 'http://www.russianpost.ru/tracking20/?' . $trackingNumber;
    }

    /**
     * Defines the form parameters of tracking information form
     *
     * @param string $trackingNumber Tracking number
     *
     * @return array Array of form parameters
     */
    public function getTrackingInformationParams($trackingNumber)
    {
        return parent::getTrackingInformationParams($trackingNumber);
    }

    /**
     * getProcessorName
     *
     * @return string
     */
    public function getProcessorName()
    {
        return 'EMS Russian Post';
    }

    /**
     * Disable the possibility to edit the names of shipping methods in the interface of administrator
     *
     * @return boolean
     */
    public function isMethodNamesAdjustable()
    {
        return false;
    }

    /**
     * Get API URL depending on request type
     *
     * @return string
     */
    public function getApiURL()
    {
        return $this->apiURL;
    }

    /**
     * Returns shipping rates
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData   Shipping order modifier or array of data for request
     * @param boolean                                    $ignoreCache Flag: if true then do not get rates from cache OPTIONAL
     *
     * @return array
     */
    public function getRates($inputData, $ignoreCache = false)
    {
        $this->errorMsg = null;
        $rates = array();

        if ($this->isConfigured()) {

            $data = $this->prepareInputData($inputData);

            if (!empty($data)) {
                $rates = $this->doQuery($data, $ignoreCache);

            } else {
                $this->errorMsg = 'Wrong input data';
            }

        } else {
            $this->errorMsg = 'EMS module is not configured';
        }

        // Return shipping rates list
        return $rates;
    }

    /**
     * Do request to EMS API to get EMS city code by city name
     *
     * @param string $city City name
     *
     * @return string
     */
    public function getEMSCityCodeByName($city)
    {
        $cityCode = '';

        if (!empty($city)) {
            if (function_exists('mb_strtoupper')) {
                $city = mb_strtoupper($city, 'UTF-8');
            } else {
                $city = strtoupper($city);
            }

            $requestType = 'EmsGetLocationsCities';

            // Try to get cached result
            $cachedCityCode = $this->getDataFromCache($requestType . $city);

            if (!empty($cachedCityCode)) {
                // Get result from cache
                $cityCode = $cachedCityCode;

            } else {
                // Get allowable EMS cities
                $result = $this->doRequest($requestType);

                if (
                    !empty($result)
                    && $result['rsp']['stat'] == 'ok'
                    && !empty($result['rsp']['locations'])
                    && is_array($result['rsp']['locations'])
                ) {
                    foreach ($result['rsp']['locations'] as $k => $v) {
                        if (
                            $city == $v['name']
                            && !empty($v['value'])
                        ) {
                            $cityCode = $v['value'];

                            break;
                        }
                    }
                }
            }

            $this->saveDataInCache($requestType . $city, $cityCode);
        }

        return $cityCode;
    }

    /**
     * Get package limits
     *
     * @return array
     */
    protected function getPackageLimits()
    {
        $limits = parent::getPackageLimits();

        $config = \XLite\Core\Config::getInstance()->XC->EMS;

        // Weight in store weight units
        $limits['weight'] = \XLite\Core\Converter::convertWeightUnits(
            $config->max_weight,
            'kg',
            \XLite\Core\Config::getInstance()->Units->weight_unit
        );

        $limits['length'] = $limits['width'] = $limits['height'] = 0;

        return $limits;
    }

    /**
     * Returns true if EMS module is configured
     *
     * @return boolean
     */
    protected function isConfigured()
    {
        return \XLite\Core\Config::getInstance()->XC->EMS->max_weight > 0
            && \XLite\Core\Config::getInstance()->XC->EMS->intl_package_type != '';

    }

    /**
     * Prepare input data from order shipping modifier
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData Array of input data or a shipping order modifier
     *
     * @return array
     */
    protected function prepareInputData($inputData)
    {
        $data = array();
        $commonData = array();

        if ($inputData instanceOf \XLite\Logic\Order\Modifier\Shipping) {

            if ('RU' == \XLite\Core\Config::getInstance()->Company->location_country) {
                $commonData['srcAddress'] = array(
                    'country' => \XLite\Core\Config::getInstance()->Company->location_country,
                    'zipcode' => \XLite\Core\Config::getInstance()->Company->location_zipcode,
                    'state' => \XLite\Core\Config::getInstance()->Company->location_state,
                    'city' => \XLite\Core\Config::getInstance()->Company->location_city,
                );
            }

            $commonData['dstAddress'] = \XLite\Model\Shipping::getInstance()->getDestinationAddress($inputData);

            if (!empty($commonData['srcAddress']) && !empty($commonData['dstAddress'])) {
                $data['packages'] = $this->getPackages($inputData);
            }

        } else {
            $data = $inputData;
        }

        if (!empty($data['packages'])) {

            foreach ($data['packages'] as $key => $package) {

                $package = array_merge($package, $commonData);

                $package['shipment_type'] = ('RU' == $package['dstAddress']['country'] ? 'Domestic' : 'International');

                $package['package_type'] = \XLite\Core\Config::getInstance()->XC->EMS->intl_package_type;

                $package['weight'] = \XLite\Core\Converter::convertWeightUnits(
                    $package['weight'],
                    \XLite\Core\Config::getInstance()->Units->weight_unit,
                    'kg'
                );

                $data['packages'][$key] = $package;
            }

        } else {
            $data = array();

            $this->errorMsg = 'There are no defined packages to delivery';
        }

        return $data;
    }

    /**
     * doQuery
     *
     * @param mixed   $data        Can be either \XLite\Model\Order instance or an array
     * @param boolean $ignoreCache Flag: if true then do not get rates from cache
     *
     * @return array
     */
    protected function doQuery($data, $ignoreCache)
    {
        $rates = array();
        $packageRates = array();
        $serviceCode = 'EMS';
        $requestType = 'EmsCalculate';

        foreach ($data['packages'] as $pid => $package) {
            $serviceRates = $this->doRequest($requestType, $package, $ignoreCache);

            // Prepare rates for package
            if ('ok' == $serviceRates['rsp']['stat']) {
                $rate = new \XLite\Model\Shipping\Rate();
                $rate->setBaseRate($serviceRates['rsp']['price']);

                if (!empty($serviceRates['rsp']['term'])) {
                    $extraData = new \XLite\Core\CommonCell();
                    $extraData->deliveryDays = $serviceRates['rsp']['term'];

                    $rate->setExtraData($extraData);
                }

                // Save rates for each package
                $packageRates[$serviceCode]['packages'][$pid] = $rate;

                // Save service name
                $packageRates[$serviceCode]['name'] = static::t('Regular parcel');

                // Save common rate (sum of rate totals of all packages)
                if (!isset($packageRates[$serviceCode]['rate'])) {
                    $packageRates[$serviceCode]['rate'] = $rate;

                } else {
                    $packageRates[$serviceCode]['rate']->setBaseRate(
                        $packageRates[$serviceCode]['rate']->getBaseRate() + $rate->getBaseRate()
                    );
                }
            }
        }

        // Prepare final rates
        if ($packageRates) {
            $availableMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')
                ->findMethodsByProcessor($this->getProcessorId(), false);

            foreach ($packageRates as $code => $info) {
                if (count($info['packages']) == count($data['packages'])) {
                    $method = null;

                    foreach ($availableMethods as $m) {
                        if ($m->getCode() == $code) {
                            $method = $m;

                            break;
                        }
                    }

                    if (
                        $method
                        && $method->getEnabled()
                    ) {
                        $info['rate']->setMethod($method);
                        $rates[] = $info['rate'];
                    }
                }
            }
        }

        return $rates;
    }

    /**
     * Get API request type data
     *
     * @param string $type Request type OPTIONAL
     *
     * @return array
     */
    protected function getApiRequestType($type = null)
    {
        $result = array();

        if ($type && !empty($this->apiRequestTypes[$type])) {
            $result = $this->apiRequestTypes[$type];

        } elseif (!$type) {
            $result = $this->apiRequestTypes;
        }

        return $result;
    }

    /**
     * Get address field value to calculate EMS shipping rate
     *
     * @param array $address Array of address fields
     *
     * @return string
     */
    protected function getEmsLocationFromAddress($address)
    {
        $emsLocation = '';

        if (
            !empty($address)
            && is_array($address)
        ) {
            if ($address['country'] == 'RU') {
                if (!empty($address['city'])) {
                    $cityCode = $this->getEMSCityCodeByName($address['city']);

                    if ($cityCode) {
                        $emsLocation = $cityCode;
                    }
                }

                if (
                    '' == $emsLocation
                    && !empty($address['state'])
                ) {
                    if (is_numeric($address['state'])) {
                        $emsLocation = \XLite\Core\Database::getRepo('XLite\Model\State')
                            ->getCodeById($address['state']);

                    } elseif (is_string($address['state'])) {
                        $emsLocation = $address['state'];
                    }
                }

            } elseif (!empty($address['country'])) {
                $emsLocation = $address['country'];
            }

            $emsLocation = func_htmlspecialchars($emsLocation);
        }

        return $emsLocation;
    }

    /**
     * Do request to EMS API
     *
     * @param string  $type        Request type
     * @param array   $params      Array of parameters OPTIONAL
     * @param boolean $ignoreCache Flag: ignore cache OPTIONAL
     *
     * @return array|null
     */
    protected function doRequest($type, $params = array(), $ignoreCache = false)
    {
        $result = null;

        $requestType = $this->getApiRequestType($type);

        $methodName = 'getRequestData' . $type;

        if (method_exists($this, $methodName)) {
            // Call method to prepare request data
            $data = $this->$methodName($params);

        } else {
            $data = array();
        }

        // Validate request data
        if ($this->validateRequestData($requestType, $data)) {
            // Prepare post data
            $postData = array();

            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $postData[] = sprintf('%s=%s', $key, $value);
                }
            }

            $postURL = $this->getApiURL() . $requestType['uri'];

            if (!empty($postData)) {
                $postURL .= '&' . implode('&', $postData);
            }

            if (!$ignoreCache) {
                // Try to get cached result
                $cachedRate = $this->getDataFromCache($postURL);
            }

            if (isset($cachedRate)) {
                \XLite\Logger::logCustom('EMS', var_export(array('Cache used' => 'Y'), true));

                // Get result from cache
                $result = $cachedRate;

            } else {

                \XLite\Logger::logCustom(
                    'EMS',
                    var_export(
                        array(
                            'Request URL'  => $postURL,
                        ),
                        true
                    )
                );
                \XLite\Logger::logCustom('EMS', var_export(array('Cache NOT used' => 'Y'), true));

                // Get result from EMS server
                try {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $postURL);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

                    $response = curl_exec($ch);

                    if (!empty($response)) {
                        $result = json_decode($response, true);

                        if (!empty($result['err'])) {
                            $this->errorMsg = 'Code: ' . $result['err']['code'] . ' ' . $result['err']['msg'];

                        } elseif ($result['rsp']['stat'] == 'ok') {
                            $this->saveDataInCache($postURL, $result);
                        }

                    } else {
                        $this->errorMsg = sprintf('Error while connecting to the EMS host (%s)', $postURL);
                    }

                } catch (\Exception $e) {
                    $this->errorMsg = $e->getMessage();

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Validate request data
     *
     * @param array $requestType Request type data
     * @param array $params      Input data for request
     *
     * @return boolean
     */
    protected function validateRequestData($requestType, $params)
    {
        $result = true;

        if (
            'EmsCalculate' == $requestType
            && isset($params['weight'])
            && $params['weight'] > 0
        ) {
            if ($params['weight'] > \XLite\Core\Config::getInstance()->XC->EMS->max_weight) {
                $result = false;
                $this->errorMsg = sprintf(
                    'Validation failed: %s = %s (max value: %s)',
                    'weight',
                    $params['weight'],
                    $params['weight']
                );
            }
        }

        return $result;
    }

    /**
     * Prepare data for the specific request
     *
     * @param array $params Array of parameters
     *
     * @return array
     */
    protected function getRequestDataEmsCalculate($params)
    {
        $result = array(
            'from'      => $this->getEmsLocationFromAddress($params['srcAddress']),
            'to'        => $this->getEmsLocationFromAddress($params['dstAddress']),
            'weight'    => $params['weight'],
        );

        if (
            !empty($params['dstAddress']['country'])
            && $params['dstAddress']['country'] != 'RU'
        ) {
            $result['type'] = \XLite\Core\Config::getInstance()->XC->EMS->intl_package_type;
        }

        return $result;
    }
}
