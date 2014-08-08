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

namespace XLite\Module\XC\Qiwi\Model\Payment\Processor;

use XLite\Core\Database;
use XLite\Core\Request;
use XLite\Model\Order;
use XLite\Model\Payment\Method;
use XLite\Model\Payment\Transaction;
use XLite\Module\XC\Qiwi\Core\QiwiREST;

/**
 * Qiwi payment processor
 */
class Qiwi extends \XLite\Model\Payment\Base\WebBased
{
    /**
     * Qiwi payment form URL
     */
    const FORM_URL = 'https://w.qiwi.com/order/external/main.action';

    /**
     * Maximum amount that can be payed using Qiwi payment method
     */
    const MAX_AMOUNT_USD = 500.00;
    const MAX_AMOUNT_RUB = 15000.00;

    /**
     * Qiwi phone number field name
     */
    const PHONE_NUMBER_FIELD = 'qiwi_phone_number';

    /**
     * Allowed currencies codes
     *
     * @var   array
     */
    protected $allowedCurrencies = array('RUB', 'USD');

    /**
     * Client 
     * 
     * @var   \XLite\Module\XC\Qiwi\Core\QiwiREST
     */
    protected $client;

    /**
     * Get input template
     *
     * @return string
     */
    public function getInputTemplate()
    {
        return 'modules/XC/Qiwi/input.tpl';
    }

    /**
     * Get input errors
     *
     * @param array $data Input data
     *
     * @return array
     */
    public function getInputErrors(array $data)
    {
        $errors = parent::getInputErrors($data);

        foreach ($this->getInputDataLabels() as $k => $t) {
            if (!isset($data[$k]) || !$data[$k]) {
                $errors[] = \XLite\Core\Translation::lbl('X field is required', array('field' => $t));
            }
        }

        if (empty($data[static::PHONE_NUMBER_FIELD]) || !preg_match('/^\+\d{1,15}$/', $data[static::PHONE_NUMBER_FIELD])) {
            $errors[] = \XLite\Core\Translation::lbl(
                'The phone number must be in international format (e.g. +7-111-111-11-11)'
            );
        }

        return $errors;
    }

    /**
     * Get settings widget or template
     *
     * @return string Widget class name or template path
     */
    public function getSettingsWidget()
    {
        return 'modules/XC/Qiwi/config.tpl';
    }

    /**
     * Check - payment method is configured or not
     *
     * @param Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(Method $method)
    {
        return parent::isConfigured($method)
            && $method->getSetting('shopId')
            && $method->getSetting('pullPassword')
            && $method->getSetting('appId')
            && $method->getSetting('appPassword');
    }

    /**
     * Payment method has settings in module settings section
     *
     * @return boolean
     */
    public function hasModuleSettings()
    {
        return false;
    }

    /**
     * Detect transaction
     *
     * @return Transaction
     */
    public function getReturnOwnerTransaction()
    {
        $txn = null;

        if (!empty(Request::getInstance()->order)) {
            $billId = Request::getInstance()->order;

            $txn = Database::getRepo('XLite\Model\Payment\Transaction')
                ->findOneBy(array('public_id' => $billId));
        }

        return $txn;
    }

    /**
     * Process return
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    public function processReturn(Transaction $transaction)
    {
        parent::processReturn($transaction);

        $qiwi = $this->getRestClient();

        $billId = $transaction->getPublicId();
        $response = $qiwi->getBill($billId);

        if (
            $response
            && 0 == $response->response->result_code
            && $this->transaction->getStatus() == $transaction::STATUS_INPROGRESS
        ) {
            if ('paid' == $response->response->bill->status) {
                $this->transaction->setStatus($transaction::STATUS_SUCCESS);

            } elseif ('waiting' == $response->response->bill->status) {
                $this->transaction->setStatus($transaction::STATUS_PENDING);

            } else {
                $this->transaction->setNote('Transaction failed. Reason: ' . $response->response->bill->status);
                $this->transaction->setStatus($transaction::STATUS_FAILED);
            }
        }
    }

    /**
     * Get callback owner transaction or null
     *
     * @return Transaction
     */
    public function getCallbackOwnerTransaction()
    {
        $request = Request::getInstance();

        return Database::getRepo('XLite\Model\Payment\Transaction')
            ->findOneBy(array('public_id' => $request->bill_id));
    }

    /**
     * Process callback
     *
     * @param Transaction $transaction Callback-owner transaction
     *
     * @return void
     */
    public function processCallback(Transaction $transaction)
    {
        parent::processCallback($transaction);

        header('Content-Type: text/xml');

        $qiwi = $this->getRestClient();

        $authMethod = $this->getSetting('sign') ? QiwiREST::AUTH_METHOD_SIGNATURE : QiwiREST::AUTH_METHOD_BASIC;

        if ($qiwi->isAuthorized($authMethod)) {
            $request = Request::getInstance();

            if (0 == $request->error) {
                if ('paid' == $request->status) {
                    $this->transaction->setStatus($transaction::STATUS_SUCCESS);

                } elseif ('waiting' == $request->status) {
                    $this->transaction->setStatus($transaction::STATUS_PENDING);

                } else {
                    $this->transaction->setNote('Transaction failed. Reason: ' . $request->status);
                    $this->transaction->setStatus($transaction::STATUS_FAILED);
                }

            } else {
                $this->transaction->setNote('Transaction failed. Reason: ' . $request->status);
                $this->transaction->setStatus($transaction::STATUS_FAILED);
            }

            echo '<' . '?xml version="1.0"?' . '> <result><result_code>0</result_code></result>';

        } else {
            echo '<' . '?xml version="1.0"?' . '> <result><result_code>150</result_code></result>';
        }
    }

    /**
     * Check - payment processor is applicable for specified order or not
     *
     * @param Order $order Order
     * @param Method $method Payment method
     *
     * @return boolean
     */
    public function isApplicable(Order $order, Method $method)
    {
        $isApplicable = parent::isApplicable($order, $method);

        if (strtoupper($order->getCurrency()->getCode()) == 'USD') {
            $isApplicable = $isApplicable && $order->getTotal() < static::MAX_AMOUNT_USD;

        } elseif (strtoupper($order->getCurrency()->getCode()) == 'RUB') {
            $isApplicable = $isApplicable && $order->getTotal() < static::MAX_AMOUNT_RUB;
        }

        return $isApplicable;
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(\XLite\Model\Payment\Method $method)
    {
        return true;
    }

    /**
     * Get payment form URL
     *
     * @return string
     */
    protected function getFormURL()
    {
        return static::FORM_URL;
    }

    /**
     * Creates invoice for customer to pay
     *
     * @return array list(Invoice identifier, Status)
     */
    protected function createInvoice()
    {
        $transactionData = $this->transaction->getData()->toArray();
        $phoneNumberField = static::PHONE_NUMBER_FIELD;

        $qiwiPhoneNumber = array_reduce($transactionData, function($acc, $cell) use ($phoneNumberField) {
            return $cell->getName() == $phoneNumberField ? $cell->getValue() : $acc;
        }, '');

        $billId = $this->getSetting('prefix') . $this->transaction->getTransactionId();

        $this->transaction->setPublicId($billId);

        $qiwi = $this->getRestClient();

        $response = $qiwi->createBill(
            $billId,
            $qiwiPhoneNumber,
            round($this->transaction->getValue(), 2),
            $this->getCurrencyCode(),
            date('c', time() + 3600 * $this->getSetting('lifetime')),
            $this->getOrder()->getOrderNumber()
        );

        if (!$response || 0 != $response->response->result_code) {
            $billId = 0;
        }

        Database::getEM()->flush();

        return array(
            $billId,
            $response ? $response->response->result_code : 999,
        );
    }

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        list($billId, $status) = $this->createInvoice();

        if ($billId) {
            $url = \XLite::getInstance()->getShopURL(
                \XLite\Core\Converter::buildURL('payment_return'),
                \XLite\Core\Config::getInstance()->Security->customer_security
            );

            $fields = array(
                'shop'        => $this->getSetting('shopId'),
                'transaction' => $billId,
                'successUrl'  => $url,
                'failUrl'     => $url,
            );

        } else {
            $fields = array();

            $this->transaction->setNote('Transaction failed. Reason: ' . $status);
            $this->transaction->setDataCell(
                'webbased_data_error',
                'Transaction failed. Reason: ' . $status
            );
            $this->transaction->setStatus(Transaction::STATUS_FAILED);
        }

        return $fields;
    }

    /**
     * Get allowed currencies
     *
     * @param Method $method Payment method
     *
     * @return array
     */
    protected function getAllowedCurrencies(Method $method)
    {
        return $this->allowedCurrencies;
    }

    /**
     * Get input data labels list
     *
     * @return array
     */
    protected function getInputDataLabels()
    {
        return array(
            static::PHONE_NUMBER_FIELD => 'Qiwi mobile phone number',
        );
    }

    /**
     * Get input data access levels list
     *
     * @return array
     */
    protected function getInputDataAccessLevels()
    {
        return array(
            static::PHONE_NUMBER_FIELD => \XLite\Model\Payment\TransactionData::ACCESS_CUSTOMER,
        );
    }

    /**
     * Get currency code
     *
     * @return string
     */
    protected function getCurrencyCode()
    {
        return strtoupper($this->getOrder()->getCurrency()->getCode());
    }

    /**
     * Instantiates Qiwi REST API client
     *
     * @return QiwiREST
     */
    protected function getRestClient()
    {
        if (!isset($this->client)) {
            $this->client = new QiwiREST(
                $this->getSetting('shopId'),
                $this->getSetting('appId'),
                $this->getSetting('appPassword'),
                $this->getSetting('pullPassword')
            );
        }

        return $this->client;
    }

    /**
     * Write empty body error message
     *
     * @return void
     */
    protected function writeEmptyBodyErrorMessage()
    {
    }
}
