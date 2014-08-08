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

namespace XLite\Module\CDev\FedEx\Model\Shipping\Processor;

/**
 * FedEx shipping processor model
 * API documentation: FedEx Web Services, Developer Guide 2012, Ver.13 (XCN-1035)
 */
class FEDEX extends \XLite\Model\Shipping\Processor\AProcessor
{
    /**
     * Unique processor Id
     *
     * @var   string
     */
    protected $processorId = 'fedex';


    /**
     * Check if 'Cash on delivery (FedEx)' payment method enabled
     *
     * @return boolean
     */
    public static function isCODPaymentEnabled()
    {
        $method = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('service_name' => 'COD_FEDEX'));

        return $method && $method->getEnabled();
    }


    /**
     * Returns processor name (displayed name)
     *
     * @return string
     */
    public function getProcessorName()
    {
        return 'FedEx';
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
     * Returns API URL
     *
     * @return string
     */
    public function getApiURL()
    {
        $protocol = 'https://';

        $host = \XLite\Core\Config::getInstance()->CDev->FedEx->test_mode == 'Y'
            ? 'wsbeta.fedex.com:443/web-services'
            : 'ws.fedex.com:443/web-services';

        return $protocol . $host;
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
        return 'https://www.fedex.com/fedextrack/index.html';
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
        $list = parent::getTrackingInformationParams($trackingNumber);
        $list['tracknumbers']   = $trackingNumber;
        $list['ascend_header']  = 1;
        $list['clienttype']     = 'dotcom';
        $list['cntry_code']     = 'us';
        $list['language']       = 'english';

        return $list;
    }

    /**
     * Returns shipping rates by shipping order modifier (used on checkout)
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

            $data = $this->prepareRequestData($inputData);

            if (!empty($data)) {

                // Calculate rates
                $rates = $this->doRequest($data, $ignoreCache);

                if (!$ignoreCache && !empty($data['cod_enabled']) && $rates && self::isCODPaymentEnabled()) {

                    // Calculate rates with COD enabled

                    $data['cod_enabled'] = true;

                    $ratesWithCOD = $this->doRequest($data, $ignoreCache);

                    if ($ratesWithCOD) {

                        foreach ($rates as $k => $rate) {

                            $rateCode = $rate->getMethod()->getCode();

                            foreach ($ratesWithCOD as $rt) {

                                if ($rt->getMethod()->getCode() == $rateCode) {

                                    $extra = $rates[$k]->getExtraData() ?: new \XLite\Core\CommonCell();
                                    $extra->cod_supported = true;
                                    $extra->cod_rate = $rt->getBaseRate();
                                    $rates[$k]->setExtraData($extra);
                                    break;
                                }
                            }
                        }
                    }
                }

            } else {
                $this->errorMsg = 'Wrong input data';
            }

        } else {
            $this->errorMsg = 'FedEx module is not configured';
        }

        return $rates;
    }

    /**
     * Returns true if FedEx module is configured
     *
     * @return boolean
     */
    protected function isConfigured()
    {
        return !empty(\XLite\Core\Config::getInstance()->CDev->FedEx->meter_number)
            && !empty(\XLite\Core\Config::getInstance()->CDev->FedEx->key)
            && !empty(\XLite\Core\Config::getInstance()->CDev->FedEx->password)
            && !empty(\XLite\Core\Config::getInstance()->CDev->FedEx->account_number);
    }

    /**
     * Get package limits
     *
     * @return array
     */
    protected function getPackageLimits()
    {
        $limits = parent::getPackageLimits();

        $config = \XLite\Core\Config::getInstance()->CDev->FedEx;

        // Weight in store weight units
        $limits['weight'] = \XLite\Core\Converter::convertWeightUnits(
            $config->max_weight,
            'lbs',
            \XLite\Core\Config::getInstance()->Units->weight_unit
        );

        $limits['length'] = $config->dim_length;
        $limits['width']  = $config->dim_width;
        $limits['height'] = $config->dim_height;

        return $limits;
    }

    /**
     * Returns array of data for request
     *
     * @param array|\XLite\Logic\Order\Modifier\Shipping $inputData Array of input data or a shipping order modifier
     *
     * @return array
     */
    protected function prepareRequestData($inputData)
    {
        $result = array();

        $data = array();
        $data['packages'] = array();

        if ($inputData instanceOf \XLite\Logic\Order\Modifier\Shipping) {

            $data['srcAddress'] = array(
                'zipcode' => \XLite\Core\Config::getInstance()->Company->location_zipcode,
                'country' => \XLite\Core\Config::getInstance()->Company->location_country,
            );

            if (isset(\XLite\Core\Config::getInstance()->Company->location_state)) {
                $data['srcAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                    \XLite\Core\Config::getInstance()->Company->location_state
                );
            }

            $data['dstAddress'] = \XLite\Model\Shipping::getInstance()->getDestinationAddress($inputData);

            if (empty($data['dstAddress']['country'])) {
                $data['dstAddress'] = null;

            } elseif (isset($data['dstAddress']['state'])) {
                $data['dstAddress']['state'] = \XLite\Core\Database::getRepo('XLite\Model\State')->getCodeById(
                    $data['dstAddress']['state']
                );
            }

            $data['packages'] = $this->getPackages($inputData);

            // Detect if COD payment method has been selected by customer on checkout

            if ($inputData->getOrder()->getFirstOpenPaymentTransaction()) {

                $paymentMethod = $inputData->getOrder()->getPaymentMethod();

                if ($paymentMethod && 'COD_FEDEX' == $paymentMethod->getServiceName()) {
                    $data['cod_enabled'] = true;
                }
            }

        } else {
            $data = $inputData;
        }

        if (!empty($data['packages']) && !empty($data['srcAddress']) && !empty($data['dstAddress'])) {

            $result = $data;

            $result['packages'] = array();

            foreach ($data['packages'] as $packKey => $package) {
                $package['price'] = sprintf('%.2f', $package['subtotal']); // decimal, min=0.00, totalDigits=10
                $package['weight'] = \XLite\Core\Converter::convertWeightUnits(
                    $package['weight'],
                    \XLite\Core\Config::getInstance()->Units->weight_unit,
                    'lbs'
                );

                $result['packages'][] = $package;
            }
        }

        return $result;
    }

    /**
     * Performs request to FedEx server and returns array of rates
     *
     * @param array   $data        Array of request parameters
     * @param boolean $ignoreCache Flag: if true then do not get rates from cache
     *
     * @return array
     */
    protected function doRequest($data, $ignoreCache)
    {
        $rates = array();

        $availableMethods = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')
            ->findMethodsByProcessor($this->getProcessorId());

        if ($availableMethods) {

            $xmlData = $this->getXMLData($data);

            $postURL = $this->getApiURL();

            try {

                if (!$ignoreCache) {
                    $cachedRate = $this->getDataFromCache($xmlData);
                }

                if (isset($cachedRate)) {
                    $result = $cachedRate;

                } else {

                    $bouncer = new \XLite\Core\HTTP\Request($postURL);
                    $bouncer->body = $xmlData;
                    $bouncer->verb = 'POST';
                    $bouncer->requestTimeout = 5;
                    $response = $bouncer->sendRequest();

                    if (200 == $response->code || !empty($response->body)) {
                        $result = $response->body;
                        if (200 == $response->code) {
                            $this->saveDataInCache($xmlData, $result);
                        }

                        if (\XLite\Core\Config::getInstance()->CDev->FedEx->debug_enabled) {
                            \XLite\Logger::logCustom(
                                'FEDEX',
                                var_export(
                                    array(
                                        'Request URL' => $postURL,
                                        'Request XML' => $xmlData,
                                        'Response'    => \XLite\Core\XML::getInstance()->getFormattedXML($result),
                                    ),
                                    true
                                )
                            );
                        }

                    } else {
                        $this->errorMsg = sprintf('Error while connecting to the FedEx host (%s)', $this->getApiURL());
                    }
                }

                if (!isset($this->errorMsg)) {
                    $response = $this->parseResponse($result);

                } else {
                    $response = array();
                }

                //save communication log for test request only (ignoreCache is set for test requests only)
                if ($ignoreCache === true) {
                    $xmlDataLog = preg_replace('|<v13:AccountNumber>.+</v13:AccountNumber>|i','<v13:AccountNumber>xxx</v13:AccountNumber>',$xmlData);
                    $xmlDataLog = preg_replace('|<v13:MeterNumber>.+</v13:MeterNumber>|i','<v13:MeterNumber>xxx</v13:MeterNumber>',$xmlDataLog);
                    $xmlDataLog = preg_replace('|<v13:Key>.+</v13:Key>|i','<v13:Key>xxx</v13:Key>',$xmlData);
                    $xmlDataLog = preg_replace('|<v13:Password>.+</v13:Password>|i','<v13:Password>xxx</v13:Password>',$xmlDataLog);

                    $this->apiCommunicationLog[] = array(
                        'request'  => $postURL,
                        'xml' => htmlentities($xmlDataLog),
                        'response' => htmlentities(\XLite\Core\XML::getInstance()->getFormattedXML($result))
                    );
                }

                if (!isset($this->errorMsg) && !isset($response['err_msg'])) {

                    foreach ($response as $code => $_rate) {

                        $rate = new \XLite\Model\Shipping\Rate();

                        $method = $this->getShippingMethod($code, $availableMethods);

                        if ($method && $method->getEnabled()) {
                            // Method is registered and enabled

                            $rate->setMethod($method);
                            $rate->setBaseRate($_rate['amount']);

                            if (!empty($data['cod_enabled'])) {

                                $extraData = new \XLite\Core\CommonCell();
                                $extraData->cod_supported = true;
                                $rate->setExtraData($extraData);
                            }

                            $rates[] = $rate;
                        }
                    }

                } elseif (!isset($this->errorMsg)) {
                    $this->errorMsg = (isset($response['err_msg']) ? $response['err_msg'] : 'Unknown error');
                }

            } catch (\Exception $e) {
                $this->errorMsg = 'Exception: ' . $e->getMessage();
            }

        }

        return $rates;
    }

    /**
     * Returns shipping method
     *
     * @param string $code             Unique code of payment method
     * @param array  $availableMethods Array of shipping methods objects gathered from database
     *
     * @return \XLite\Model\Shipping\Method
     */
    protected function getShippingMethod($code, $availableMethods)
    {
        $result = null;

        // Check if method with $code exists in $availableMethods
        if (!empty($availableMethods) && is_array($availableMethods)) {

            foreach ($availableMethods as $method) {

                if ($method->getCode() == $code) {
                    $result = $method;
                    break;
                }
            }
        }

        // If not found - check if this method available in database
        if (!isset($result)) {
            $result = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Method')->findOneByCode($code);
        }

        return $result;
    }

    /**
     * Returns XML-formatted request string for current type of API
     *
     * @param array $data Array of request values
     *
     * @return string
     */
    protected function getXMLData($data)
    {
        \XLite\Core\Config::getInstance()->CDev->FedEx->rewind();
        $settings = \XLite\Core\Config::getInstance()->CDev->FedEx;

        while ($settings->valid()) {
            $fedexOptions[$settings->key()] = $settings->current();
            $settings->next();
        }

        // Define ship date
        $fedexOptions['ship_date_ready'] = date('c', \XLite\Core\Converter::time() + intval($fedexOptions['ship_date']) * 24 * 3600);

        // Define available carrier codes
        $carrierCodes = '';

        foreach (array('fdxe', 'fdxg', 'fxsp') as $code) {
            if (isset($fedexOptions[$code]) && 'Y' == $fedexOptions[$code]) {
                $carrierCodes .= str_repeat(' ', 9) . '<v13:CarrierCodes>' . strtoupper($code) . '</v13:CarrierCodes>' . PHP_EOL;
            }
        }

        // Define address fields
        $fedexOptions['destination_state_code'] = (isset($data['dstAddress']['state']) ? $data['dstAddress']['state'] : '');
        $fedexOptions['destination_country_code'] = (isset($data['dstAddress']['country']) ? $data['dstAddress']['country'] : '');
        $fedexOptions['destination_postal_code'] = (isset($data['dstAddress']['zipcode']) ? $data['dstAddress']['zipcode'] : '');
        $fedexOptions['origin_state_code'] = (isset($data['srcAddress']['state']) ? $data['srcAddress']['state'] : '');
        $fedexOptions['origin_country_code'] = (isset($data['srcAddress']['country']) ? $data['srcAddress']['country'] : '');
        $fedexOptions['origin_postal_code'] = (isset($data['srcAddress']['zipcode']) ? $data['srcAddress']['zipcode'] : '');

        // TODO: Move option to the settings page
        // Shipper address type: 1 - Residential, 0 - Commercial
        $fedexOptions['origin_address_type'] = ('Y' == $fedexOptions['opt_residential_delivery'] ? 1 : 0);

        // TODO: Add this field to address book and get option from this
        //  address type: 1 - Residential, 0 - Commercial
        $fedexOptions['destination_address_type'] = (isset($data['dstAddress']['type']) ? $data['dstAddress']['type'] : 1);

        $fedexOptions['dim_units'] = 'IN';
        $fedexOptions['weight_units'] = 'LB';

        $packagesCount = is_array($data['packages']) ? count($data['packages']) : 1;

        // Define packages XML
        $packagesXML = $this->preparePackagesXML($data, $fedexOptions);

        // Define Special services XML
        $specialServicesXML = $this->prepareSpecialServicesShipmentXML($data, $fedexOptions);

        $result =<<<OUT
<soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:v13='http://fedex.com/ws/rate/v13'>
   <soapenv:Header/>
   <soapenv:Body>
      <v13:RateRequest>
         <v13:WebAuthenticationDetail>
            <v13:UserCredential>
               <v13:Key>{$fedexOptions['key']}</v13:Key>
               <v13:Password>{$fedexOptions['password']}</v13:Password>
            </v13:UserCredential>
         </v13:WebAuthenticationDetail>
         <v13:ClientDetail>
            <v13:AccountNumber>{$fedexOptions['account_number']}</v13:AccountNumber>
            <v13:MeterNumber>{$fedexOptions['meter_number']}</v13:MeterNumber>
         </v13:ClientDetail>
         <v13:TransactionDetail>
            <v13:CustomerTransactionId>X-Cart 5: Rate an order packages v13</v13:CustomerTransactionId>
         </v13:TransactionDetail>
         <v13:Version>
            <v13:ServiceId>crs</v13:ServiceId>
            <v13:Major>13</v13:Major>
            <v13:Intermediate>0</v13:Intermediate>
            <v13:Minor>0</v13:Minor>
         </v13:Version>
         <v13:ReturnTransitAndCommit>1</v13:ReturnTransitAndCommit>
{$carrierCodes}
         <v13:RequestedShipment>
            <v13:ShipTimestamp>{$fedexOptions['ship_date_ready']}</v13:ShipTimestamp>
            <v13:DropoffType>{$fedexOptions['dropoff_type']}</v13:DropoffType>
            <v13:Shipper>
               <v13:AccountNumber>{$fedexOptions['account_number']}</v13:AccountNumber>
               <v13:Address>
                  <v13:StateOrProvinceCode>{$fedexOptions['origin_state_code']}</v13:StateOrProvinceCode>
                  <v13:PostalCode>{$fedexOptions['origin_postal_code']}</v13:PostalCode>
                  <v13:CountryCode>{$fedexOptions['origin_country_code']}</v13:CountryCode>
                  <v13:Residential>{$fedexOptions['origin_address_type']}</v13:Residential>
               </v13:Address>
            </v13:Shipper>
            <v13:Recipient>
               <v13:Address>
                  <v13:StateOrProvinceCode>{$fedexOptions['destination_state_code']}</v13:StateOrProvinceCode>
                  <v13:PostalCode>{$fedexOptions['destination_postal_code']}</v13:PostalCode>
                  <v13:CountryCode>{$fedexOptions['destination_country_code']}</v13:CountryCode>
                  <v13:Residential>{$fedexOptions['destination_address_type']}</v13:Residential>
               </v13:Address>
            </v13:Recipient>
            <v13:ShippingChargesPayment>
               <v13:PaymentType>SENDER</v13:PaymentType>
               <v13:Payor>
                  <v13:ResponsibleParty>
                     <v13:AccountNumber>{$fedexOptions['account_number']}</v13:AccountNumber>
                  </v13:ResponsibleParty>
               </v13:Payor>
            </v13:ShippingChargesPayment>
{$specialServicesXML}
            <v13:RateRequestTypes>ACCOUNT</v13:RateRequestTypes>
            <v13:PackageCount>{$packagesCount}</v13:PackageCount>
{$packagesXML}
         </v13:RequestedShipment>
      </v13:RateRequest>
   </soapenv:Body>
</soapenv:Envelope>
OUT;

        return $result;
    }

    /**
     * Return XML string with packages description
     *
     * @param array $data         Request data
     * @param array $fedexOptions FedEx options array
     *
     * @return string
     */
    protected function preparePackagesXML($data, $fedexOptions)
    {
        $i = 1;
        $itemsXML = '';
        $isSmartpostRequested = ('Y' == $fedexOptions['fxsp']);

        $packages = $data['packages'];

        foreach ($packages as $pack) {

            if ('YOUR_PACKAGING' == $fedexOptions['packaging']) {

                if (isset($pack['box'])) {
                    $length = $pack['box']['length'];
                    $width  = $pack['box']['width'];
                    $height = $pack['box']['height'];

                } else {
                    $length = $fedexOptions['dim_length'];
                    $width  = $fedexOptions['dim_width'];
                    $height = $fedexOptions['dim_height'];
                }

                $dimensionsXML = <<<OUT
               <v13:Dimensions>
                  <v13:Length>{$length}</v13:Length>
                  <v13:Width>{$width}</v13:Width>
                  <v13:Height>{$height}</v13:Height>
                  <v13:Units>{$fedexOptions['dim_units']}</v13:Units>
               </v13:Dimensions>
OUT;
            } else {
                $dimensionsXML = '';
            }

            $pack['weight'] = \XLite\Core\Converter::convertWeightUnits(
                $pack['weight'],
                \XLite\Core\Config::getInstance()->Units->weight_unit,
                'lbs'
            );

            $weightXML =<<<OUT
               <v13:Weight>
                  <v13:Units>{$fedexOptions['weight_units']}</v13:Units>
                  <v13:Value>{$pack['weight']}</v13:Value>
               </v13:Weight>
OUT;

            // Declared value
            $declaredValueXML = '';

            $subtotal = $this->getPackagesSubtotal($data);

            if (
                $subtotal > 0
                && 'Y' == $fedexOptions['send_insured_value']
                && !$isSmartpostRequested
            ) {
                $declaredValueXML = <<<OUT
               <v13:InsuredValue>
                 <v13:Currency>{$fedexOptions['currency_code']}</v13:Currency>
                 <v13:Amount>{$subtotal}</v13:Amount>
               </v13:InsuredValue>
OUT;
            }

            $specialServicesXML = $this->prepareSpecialServicesPackageXML($data, $fedexOptions);

            $specialServicesXML = str_replace('{{fedex_weight}}', $pack['weight'], $specialServicesXML);

            $itemsXML .= <<<EOT
            <v13:RequestedPackageLineItems>
               <v13:SequenceNumber>{$i}</v13:SequenceNumber>
               <v13:GroupPackageCount>1</v13:GroupPackageCount>
{$declaredValueXML}
{$weightXML}
{$dimensionsXML}
{$specialServicesXML}
            </v13:RequestedPackageLineItems>

EOT;
            $i++;
        } // foreach ($packages as $pack)

        return $itemsXML;
    }

    /**
     * Return XML string with special services description
     *
     * @param array  $data         Input data
     * @param array  $fedexOptions FedEx options array
     * @param string $serviceType  Type of special services (package or shipment)
     *
     * @return string
     */
    protected function prepareSpecialServicesPackageXML($data, $fedexOptions)
    {
        $result = '';
        $specialServices = array();

        if (!empty($fedexOptions['dg_accessibility'])) {
            $specialServices[] = <<<OUT
                 <v13:SpecialServiceTypes>DANGEROUS_GOODS</v13:SpecialServiceTypes>
                 <v13:DangerousGoodsDetail>
                   <v13:Accessibility>{$fedexOptions['dg_accessibility']}</v13:Accessibility>
                 </v13:DangerousGoodsDetail>
OUT;
        }

        /* Option disabled
        if ('Y' == $fedexOptions['dry_ice']) {
            $specialServices[] = <<<OUT
                 <v13:SpecialServiceTypes>DRY_ICE</v13:SpecialServiceTypes>
                 <v13:DryIceWeight>
                   <v13:Units>LB</ns:Units>
                   <v13:Value>{{fedex_weight}}</v13:Value>
                 </v13:DryIceWeight>
OUT;
        }
         */

        /* Option disabled
        if ('Y' == $fedexOptions['opt_nonstandard_container']) {
            $specialServices[] = <<<OUT
                 <v13:SpecialServiceTypes>NON_STANDARD_CONTAINER</v13:SpecialServiceTypes>
OUT;
        }
         */

        if (!empty($fedexOptions['signature'])) {
            $specialServices[] = <<<OUT
                 <v13:SignatureOptionDetail>
                   <v13:OptionType>{$fedexOptions['signature']}</v13:OptionType>
                 </v13:SignatureOptionDetail>
OUT;
        }

        if (!empty($specialServices)) {
            $specialServicesString = implode('', $specialServices);
            $result =<<<OUT
               <v13:SpecialServicesRequested>
{$specialServicesString}
               </v13:SpecialServicesRequested>
OUT;
        }

        return $result;
    }

    /**
     * Return XML string with special services description
     *
     * @param array  $data         Input data
     * @param array  $fedexOptions FedEx options array
     *
     * @return string
     */
    protected function prepareSpecialServicesShipmentXML($data, $fedexOptions)
    {
        $result = '';
        $specialServices = array();
        $specialServicesTypes = array();

        if (!empty($data['cod_enabled']) && $this->isCODAllowed($data)) {

            $subtotal = $this->getPackagesSubtotal($data);

            if (empty($fedexOptions['cod_type'])) {
                $fedexOptions['cod_type'] = 'ANY';
            }

            $specialServices[] = <<<OUT
                <v13:SpecialServiceTypes>COD</v13:SpecialServiceTypes>
                <v13:CodDetail>
                  <v13:CodCollectionAmount>
                    <v13:Currency>{$fedexOptions['currency_code']}</v13:Currency>
                    <v13:Amount>{$subtotal}</v13:Amount>
                  </v13:CodCollectionAmount>
                  <v13:CollectionType>{$fedexOptions['cod_type']}</v13:CollectionType>
                </v13:CodDetail>
OUT;
        }

        if ('Y' == $fedexOptions['opt_saturday_pickup'] && 6 == date('w', \XLite\Core\Converter::time() + intval($fedexOptions['ship_date']) * 24 * 3600)) {
            $specialServicesTypes[] = 'SATURDAY_PICKUP';
        }

        foreach ($specialServicesTypes as $type) {
            $specialServices[] =<<<OUT
                <v13:SpecialServiceTypes>{$type}</v13:SpecialServiceTypes>
OUT;
        }

        if (!empty($specialServices)) {
            $specialServicesString = implode('', $specialServices);
            $result =<<<OUT
            <v13:SpecialServicesRequested>
{$specialServicesString}
            </v13:SpecialServicesRequested>
OUT;
        }

        return $result;
    }

    /**
     * Parses response and returns an associative array
     *
     * @param string $stringData Response received from FedEx
     *
     * @return array
     */
    protected function parseResponse($stringData)
    {
        $result = array();

        $xml = \XLite\Core\XML::getInstance();

        $xmlParsed = $xml->parse($stringData, $err);

        if (isset($xmlParsed['soapenv:Envelope']['#']['soapenv:Body'][0]['#']['soapenv:Fault'][0]['#'])) {

            // FedEx responses with error of request validation

            $result['err_msg'] = $xml->getArrayByPath($xmlParsed, 'soapenv:Envelope/#/soapenv:Body/0/#/soapenv:Fault/0/#/faultstring/0/#');

        } else {

            $rateReply = $xml->getArrayByPath($xmlParsed, 'SOAP-ENV:Envelope/#/SOAP-ENV:Body/0/#/RateReply/0/#');

            $errorCodes = array('FAILURE','ERROR');

            if (in_array($xml->getArrayByPath($rateReply, 'HighestSeverity/0/#'), $errorCodes)) {

                // FedEx failed to return valid rates

                $result['err_msg'] = $xml->getArrayByPath($rateReply, 'Notifications/0/#/Message/0/#');
                $result['err_code'] = $xml->getArrayByPath($rateReply, 'Notifications/0/#/Code/0/#');

            } else {

                // Success

                $rateDetails = $xml->getArrayByPath($rateReply, 'RateReplyDetails');

                if (!empty($rateDetails) && is_array($rateDetails)) {

                    $conversionRate = $this->getCurrencyConversionRate();

                    foreach ($rateDetails as $rate) {

                        $serviceType = $xml->getArrayByPath($rate, '#/ServiceType/0/#');

                        $result[$serviceType]['amount'] = $this->getRateAmount($rate);

                        $variableHandlingCharge = $xml->getArrayByPath(
                            $rate,
                            '#/RatedShipmentDetails/ShipmentRateDetail/TotalVariableHandlingCharges/VariableHandlingCharge/Amount/0/#'
                        );

                        $result[$serviceType]['amount'] += floatval($variableHandlingCharge);

                        if (1 != $conversionRate) {
                            $result[$serviceType]['amount'] *= $conversionRate;
                        }
                    }
                }
            }
        }

        // Log error
        if (isset($result['err_msg'])) {
            \XLite\Logger::logCustom(
                'FEDEX',
                var_export(
                    array(
                        'Error'    => $result['err_msg'],
                        'Response' => \XLite\Core\XML::getInstance()->getFormattedXML($stringData)
                    ),
                    true
                )
            );
        }

        return $result;
    }

    /**
     * Get shipping rate
     *
     * @param array $entry
     *
     * @return array
     */
    protected function getRateAmount($entry)
    {
        $xml = \XLite\Core\XML::getInstance();

        $currencyCode = \XLite\Core\Config::getInstance()->CDev->FedEx->currency_code;

        $rateCurrency = $xml->getArrayByPath($entry, 'RatedShipmentDetails/ShipmentRateDetail/TotalNetCharge/Currency/0/#');

        if ($rateCurrency != $currencyCode) {

            // Currency conversion is needed
            $ratedShipmentDetails = $xml->getArrayByPath($entry, 'RatedShipmentDetails');

            // Try to find extact rate value
            $preciseRateFound = false;

            foreach ($ratedShipmentDetails as $key => $shipmentRateDetail) {

                $currencyExchangeRate = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/CurrencyExchangeRate/RATE/0/#');
                $fromCurrency = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/CurrencyExchangeRate/FromCurrency/0/#');
                $rateCurrency = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/TotalNetCharge/Currency/0/#');
                $estimatedRate = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/TotalNetCharge/Amount/0/#');

                if (
                    $currencyExchangeRate == '1.0'
                    && $fromCurrency == $currencyCode
                    && $rateCurrency == $currencyCode
                ) {
                    // This rate type can be used without conversion
                    $preciseRateFound = true;
                    break;
                }
            }

            if (!$preciseRateFound) {

                // Rate type without conversion is not found/ Use conversion
                foreach ($ratedShipmentDetails as $key => $shipmentRateDetail) {

                    $currencyExchangeRate = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/CurrencyExchangeRate/RATE/0/#');

                    if ($currencyExchangeRate == 0) {
                        continue;
                    }

                    $fromCurrency = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/CurrencyExchangeRate/FromCurrency/0/#');
                    $intoCurrency = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/CurrencyExchangeRate/IntoCurrency/0/#');
                    $rateCurrency = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/TotalNetCharge/Currency/0/#');
                    $estimatedRate = $xml->getArrayByPath($shipmentRateDetail, 'ShipmentRateDetail/TotalNetCharge/Amount/0/#');

                    if ($fromCurrency == $rateCurrency) {
                        $estimatedRate *= $currencyExchangeRate;
                        break;

                    } elseif ($intoCurrency == $rateCurrency) {
                        $estimatedRate /= $currencyExchangeRate;
                        break;
                    }
                }
            }

        } // if ($rateCurrency != $currencyCode) {

        if (empty($estimatedRate)) {
            $estimatedRate = $xml->getArrayByPath($entry, 'RatedShipmentDetails/ShipmentRateDetail/TotalNetCharge/Amount/0/#');
        }

        return $estimatedRate;
    }

    /**
     * Get key hash (to use this for caching rates)
     *
     * @param string $key Key value
     *
     * @return string
     */
    protected function getKeyHash($key)
    {
        $key = preg_replace('/<v13:ShipTimestamp>.+<\/v13:ShipTimestamp>/i', '', $key);

        return parent::getKeyHash($key);
    }

    /**
     * Get sum of subtotals of all packages
     *
     * @param array $data Input data
     *
     * @return float
     */
    protected function getPackagesSubtotal($data)
    {
        $subtotal = 0;

        if (is_array($data)) {
            foreach ($data['packages'] as $pack) {
                $subtotal += doubleval($pack['price']);
            }
        }

        return round($subtotal / $this->getCurrencyConversionRate(), 2);
    }

    /**
     * Get currency conversion rate
     *
     * @return float
     */
    protected function getCurrencyConversionRate()
    {
        $rate = doubleval(\XLite\Core\Config::getInstance()->CDev->FedEx->currency_rate);

        return $rate ?: 1;
    }

    /**
     * Check if COD is allowed
     *
     * @param array $data Input data array
     *
     * @return boolean
     */
    protected function isCODAllowed($data)
    {
        return true;
    }
}
