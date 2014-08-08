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

namespace XLite\Module\CDev\FedEx\Controller\Admin;

/**
 * FedEx module settings page controller
 *
 * @see   ____class_see____
 * @since 1.0.0
 */
class Fedex extends \XLite\Controller\Admin\ShippingSettings
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getTitle()
    {
        return 'FedEx settings';
    }

    /**
     * getOptionsCategory
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getOptionsCategory()
    {
        return 'CDev\FedEx';
    }

    /**
     * Returns options for Packaging selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getPackagingOptions()
    {
        return array(
            'YOUR_PACKAGING'     => 'My packaging',
            'FEDEX_ENVELOPE'     => 'FedEx Envelope',
            'FEDEX_PAK'          => 'FedEx Pak',
            'FEDEX_BOX'          => 'FedEx Box',
            'FEDEX_TUBE'         => 'FedEx Tube',
            'FEDEX_10KG_BOX'     => 'FedEx 10Kg Box',
            'FEDEX_25KG_BOX'     => 'FedEx 25Kg Box'
        );
    }

    /**
     * Returns options for Dropoff Type selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getDropoffTypeOptions()
    {
        return array(
            'REGULAR_PICKUP'            => 'Regular pickup',
            'REQUEST_COURIER'           => 'Request courier',
            'DROP_BOX'                  => 'Drop box',
            'BUSINESS_SERVICE_CENTER'   => 'Business Service Center',
            'STATION'                   => 'Station',
        );
    }

    /**
     * Returns options for Ship date selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getShipDateOptions()
    {
        return array(
            '0'     => 0,
            '1'     => 1,
            '2'     => 2,
            '3'     => 3,
            '4'     => 4,
            '5'     => 5,
            '6'     => 6,
            '7'     => 7,
            '8'     => 8,
            '9'     => 9,
            '10'    => 10,
        );
    }

    /**
     * Returns options for Currency code selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getCurrencyCodeOptions()
    {
        return array(
                'USD' => 'U.S. Dollars (USD)',
                'CAD' => 'Canadian Dollars (CAD)',
                'EUR' => 'European Currency Unit (EUR)',
                'JYE' => 'Japanese Yen (JYE)',
                'UKL' => 'British Pounds (UKL)',
                'NOK' => 'Norwegian Kronen (NOK)',
                'AUD' => 'Australian Dollars (AUD)',
                'HKD' => 'Hong Kong Dollars (HKD)',
                'NTD' => 'New Taiwan Dollars (NTD)',
                'SID' => 'Singapore Dollars (SID)',
                'ANG' => 'Antilles Guilder (ANG)',
                'RDD' => 'Dominican Peso (RDD)',
                'ARN' => 'Argentina Peso (ARN)',
                'ECD' => 'E. Caribbean Dollars (ECD)',
                'PKR' => 'Pakistan Rupee (PKR)',
                'AWG' => 'Aruban Florins (AWG)',
                'EGP' => 'Egyptian Pound (EGP)',
                'PHP' => 'Philippine Pesos (PHP)',
                'SAR' => 'Saudi Arabian Riyals (SAR)',
                'BHD' => 'Bahraini Dinars (BHD)',
                'BBD' => 'Barbados Dollars (BBD)',
                'INR' => 'Indian Rupees (INR)',
                'WON' => 'South Korea Won (WON)',
                'BMD' => 'Bermuda Dollars (BMD)',
                'JAD' => 'Jamaican Dollars (JAD)',
                'SEK' => 'Swedish Krona (SEK)',
                'BRL' => 'Brazil Real (BRL)',
                'SFR' => 'Swiss Francs (SFR)',
                'KUD' => 'Kuwaiti Dinars (KUD)',
                'THB' => 'Thailand Baht (THB)',
                'BND' => 'Brunei Dollar (BND)',
                'MOP' => 'Macau Patacas (MOP)',
                'TTD' => 'Trinidad &amp; Tobago Dollars (TTD)',
                'MYR' => 'Malaysian Ringgits (MYR)',
                'TRY' => 'Turkish Lira (TRY)',
                'CHP' => 'Chilean Pesos (CHP)',
                'UAE' => 'Mexican Pesos NMP (UAE)',
                'DHS' => 'Dirhams (DHS)',
                'CNY' => 'Chinese Renminbi (CNY)',
                'DKK' => 'Denmark Krone (DKK)',
                'NZD' => 'New Zealand Dollars (NZD)',
                'VEF' => 'Venezuela Bolivar (VEF)',
        );
    }

    /**
     * Returns options for COD type selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getCODTypeOptions()
    {
        return array(
            'ANY'               => 'Any funds',
            'GUARANTEED_FUNDS'  => 'Guaranteed funds',
            'CASH'              => 'Cash',
        );
    }

    /**
     * Returns options for Charge type selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getChargeTypeOptions()
    {
        return array(
            'FIXED_AMOUNT'                              => 'Fixed amount',
            'PERCENTAGE_OF_NET_FREIGHT'                 => '% of base',
            'PERCENTAGE_OF_NET_CHARGE'                  => '% of net',
            'PERCENTAGE_OF_NET_CHARGE_EXCLUDING_TAXES'  => '% of net (excluding taxes)',
        );
    }

    /**
     * Returns options for Dangerous Goods/Accessibility selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getDangerousGoodsOptions()
    {
        return array(
            'ACCESSIBLE'               => 'Accessible dangerous goods',
            'INACCESSIBLE'             => 'Inaccessible dangerous goods',
        );
    }

    /**
     * Returns options for Signature option selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getSignatureOptions()
    {
        return array(
            'NO_SIGNATURE_REQUIRED'    => 'No signature required',
            'INDIRECT'                 => 'Indirect signature required',
            'DIRECT'                   => 'Direct signature required',
            'ADULT'                    => 'Adult signature required',
        );
    }

    /**
     * Returns options for Smartpost Indicia option selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getSmartpostIndiciaOptions()
    {
        return array(
            'MEDIA_MAIL'                        => 'MEDIA_MAIL',
            'PARCEL_RETURN'                     => 'PARCEL_RETURN',
            'PARCEL_SELECT'                     => 'PARCEL_SELECT',
            'PRESORTED_BOUND_PRINTED_MATTER'    => 'PRESORTED_BOUND_PRINTED_MATTER',
            'PRESORTED_STANDARD'                => 'PRESORTED_STANDARD',
        );
    }

    /**
     * Returns options for Endorsement type option selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getSmartpostEndorsementTypeOptions()
    {
        return array(
            'ADDRESS_CORRECTION'                 => 'ADDRESS_CORRECTION',
            'CARRIER_LEAVE_IF_NO_RESPONSE'       => 'CARRIER_LEAVE_IF_NO_RESPONSE',
            'CHANGE_SERVICE'                     => 'CHANGE_SERVICE',
            'FORWARDING_SERVICE'                 => 'FORWARDING_SERVICE',
            'RETURN_SERVICE'                     => 'RETURN_SERVICE',
        );
    }

    /**
     * Returns options for Endorsement type option selector
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getSmartpostHubidOptions()
    {
        return array(
                '5303' => 'Atlanta ATGA (5303)',
                '5281' => 'Charlotte CHNC (5281)',
                '5602' => 'Chicago CIIL (5602)',
                '5929' => 'Chino COCA (5929)',
                '5751' => 'Dallas DLTX (5751)',
                '5802' => 'Denver DNCO (5802)',
                '5481' => 'Detroit DTMI (5481)',
                '5087' => 'Edison EDNJ (5087)',
                '5431' => 'Grove City GCOH (5431)',
                '5771' => 'Houston HOTX (5771)',
                '5465' => 'Indianapolis ININ (5465)',
                '5648' => 'Kansas City KCKS (5648)',
                '5902' => 'Los Angeles LACA (5902)',
                '5254' => 'Martinsburg MAWV (5254)',
                '5379' => 'Memphis METN (5379)',
                '5552' => 'Minneapolis MPMN (5552)',
                '5531' => 'New Berlin NBWI (5531)',
                '5110' => 'Newburgh NENY (5110)',
                '5015' => 'Northborough NOMA (5015)',
                '5327' => 'Orlando ORFL (5327)',
                '5194' => 'Philadelphia PHPA (5194)',
                '5854' => 'Phoenix PHAZ (5854)',
                '5150' => 'Pittsburgh PTPA (5150)',
                '5958' => 'Sacramento SACA (5958)',
                '5843' => 'Salt Lake City SCUT (5843)',
                '5983' => 'Seattle SEWA (5983)',
                '5631' => 'St. Louis STMO (5631)',
        );
    }

    /**
     * Check if 'Cash on delivery (FedEx)' payment method enabled
     *
     * @return boolean
     */
    public function isFedExCODPaymentEnabled()
    {
        return \XLite\Module\CDev\FedEx\Model\Shipping\Processor\FEDEX::isCODPaymentEnabled();
    }

    /**
     * Get shipping processor
     *
     * @return object
     */
    protected function getProcessor()
    {
        return new \XLite\Module\CDev\FedEx\Model\Shipping\Processor\FEDEX();
    }

    /**
     * Get schema of an array for test rates routine
     *
     * @return array
     */
    protected function getTestRatesSchema()
    {
        $schema = parent::getTestRatesSchema();

        foreach (array('srcAddress', 'dstAddress') as $k) {
            unset($schema[$k]['city']);
            unset($schema[$k]['state']);
        }

        return $schema;
    }

    /**
     * Get input data to calculate test rates
     *
     * @param array $schema  Input data schema
     * @param array &$errors Array of fields which are not set
     *
     * @return array
     */
    protected function getTestRatesData(array $schema, &$errors)
    {
        $data = parent::getTestRatesData($schema, $errors);

        $package = array(
            'weight'   => $data['weight'],
            'subtotal' => $data['subtotal'],
        );

        $data['packages'] = array($package);

        unset($data['weight']);
        unset($data['subtotal']);

        return $data;
    }
}
