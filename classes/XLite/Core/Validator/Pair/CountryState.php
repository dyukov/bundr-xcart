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

namespace XLite\Core\Validator\Pair;

/**
 * Country-state validator
 */
class CountryState extends \XLite\Core\Validator\Pair\APair
{
    /**
     * Validate
     *
     * @param mixed $data Data
     *
     * @return void
     * @throws \XLite\Core\Validator\Exception
     */
    public function validate($data)
    {
        // Check country
        if (!isset($data['country'])) {
            throw $this->throwError('Country is not defined');
        }

        $countryCodeValidator = new \XLite\Core\Validator\Pair\Simple;
        $countryCodeValidator->setName('country');
        $countryCodeValidator->setValidator(
            new \XLite\Core\Validator\String\ObjectId\Country(true)
        );
        $countryCodeValidator->validate($data);

        // Check custom state flag
        $customState = isset($data['is_custom_state']) ? (bool)$data['is_custom_state'] : false;

        // Check state
        if (!isset($data['state'])) {
            throw $this->throwError('State is not defined');
        }

        $stateValidator = new \XLite\Core\Validator\Pair\Simple;
        $stateValidator->setName('state');
        $stateCellValidator = $customState
            ? new \XLite\Core\Validator\String(true)
            : new \XLite\Core\Validator\String\ObjectId\State(true);
        $stateValidator->setValidator($stateCellValidator);
        $stateValidator->validate($data);

        if (!$customState) {
            $data = $this->sanitize($data);
            if ($data['state']->getCountry()->getCode() != $data['country']->getCode()) {
                throw $this->throwError('Country has not specified state');
            }
        }
    }

    /**
     * Sanitize
     *
     * @param mixed $data Daa
     *
     * @return mixed
     */
    public function sanitize($data)
    {
        // Get country
        $countryCodeValidator = new \XLite\Core\Validator\Pair\Simple;
        $countryCodeValidator->setName('country');
        $countryCodeValidator->setValidator(
            new \XLite\Core\Validator\String\ObjectId\Country(true)
        );
        $country = $countryCodeValidator->getValidator()->sanitize($data['country']);

        // Get state
        $customState = isset($data['is_custom_state']) ? (bool)$data['is_custom_state'] : false;

        if ($customState) {
            $state = new \XLite\Model\State;
            $state->setState($data['state']);
            $state->setCountry($country);

        } else {
            $stateValidator = new \XLite\Core\Validator\String\ObjectId\State(true);
            $state = $stateValidator->sanitize($data['state']);
        }

        return array(
            'country' => $country,
            'state'   => $state,
        );
    }
}
