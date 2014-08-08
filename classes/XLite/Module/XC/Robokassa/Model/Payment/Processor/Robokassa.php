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

namespace XLite\Module\XC\Robokassa\Model\Payment\Processor;

use Includes\Utils\URLManager;
use XLite\Core\Config;
use XLite\Core\Converter;
use XLite\Core\Database;
use XLite\Core\Request;
use XLite\Core\Translation;
use XLite\Model\Payment\Method;
use XLite\Model\Payment\Transaction;
use XLite\Model\Order;

/**
 * Robokassa payment processor
 */
class Robokassa extends \XLite\Model\Payment\Base\WebBased
{
    /**
     * Form URL for "test" mode
     */
    const FORM_URL_TEST = 'http://test.robokassa.ru/Index.aspx';

    /**
     * Form URL for "live" mode
     */
    const FORM_URL_LIVE = 'https://merchant.roboxchange.com/Index.aspx';

    /**
     * POST URL for "test" mode
     */
    const POST_FORM_URL_TEST = 'http://test.robokassa.ru/WebService/Service.asmx/OpState';

    /**
     * POST URL for "live" mode
     */
    const POST_FORM_URL_LIVE = 'https://merchant.roboxchange.com/WebService/Service.asmx/OpState';

    /**
     * Allowed currencies codes
     *
     * @var   array
     */
    protected $allowedCurrencies = array('RUB');

    /**
     * stateCode - operation current state code. Possible values:
     * 5 - initiated, payment is not received by the service
     * 10 - payment was not received, operation canceled
     * 50 - payment received, payment is transferred to the merchant account
     * 60 - payment was returned to payer after it was received
     * 80 - operation execution is suspended
     * 100 - operation completed successfully
     *
     * @var array
     */
    protected $stateCodes = array(
        5   => Transaction::STATUS_INITIALIZED,
        10  => Transaction::STATUS_CANCELED,
        50  => Transaction::STATUS_INPROGRESS,
        60  => Transaction::STATUS_FAILED,
        80  => Transaction::STATUS_PENDING,
        100 => Transaction::STATUS_SUCCESS,
    );

    /**
     * Get settings widget or template
     *
     * @return string Widget class name or template path
     */
    public function getSettingsWidget()
    {
        return 'modules/XC/Robokassa/config.tpl';
    }

    /**
     * Check - payment method is configured or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(Method $method)
    {
        return parent::isConfigured($method)
            && $method->getSetting('login')
            && $method->getSetting('password1')
            && $method->getSetting('password2');
    }

    /**
     * Payment method has settings into Module settings section
     *
     * @return boolean
     */
    public function hasModuleSettings()
    {
        return false;
    }

    /**
     * Get payment method admin zone icon URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getAdminIconURL(Method $method)
    {
        return true;
    }


    /**
     * Get callback URL for Robokassa payment method
     *
     * @return string
     */
    public function getRobokassaCallbackURL()
    {
        return URLManager::getShopURL(
            \Includes\Utils\Converter::buildURL('callback', 'callback', array(), \XLite::CART_SELF)
        );
    }

    /**
     * Get Success URL for Robokassa payment method
     *
     * @return string
     */
    public function getRobokassaSuccessURL()
    {
        return URLManager::getShopURL(
            \Includes\Utils\Converter::buildURL('payment_return', '', array('status' => 'success'), \XLite::CART_SELF)
        );
    }

    /**
     * Get Fail URL for Robokassa payment method
     *
     * @return string
     */
    public function getRobokassaFailURL()
    {
        return URLManager::getShopURL(
            \Includes\Utils\Converter::buildURL('payment_return', '', array('status' => 'fail'), \XLite::CART_SELF)
        );
    }

    /**
     * Detect transaction
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function getReturnOwnerTransaction()
    {
        $request = Request::getInstance();

        return Database::getRepo('XLite\Model\Payment\Transaction')
            ->findOneBy(array('transaction_id' => $request->Shp_ord));
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

        $request = Request::getInstance();

        if ($this->transaction->getStatus() == $transaction::STATUS_INPROGRESS) {

            if ('fail' == $request->status) {
                $this->transaction->setStatus($transaction::STATUS_FAILED);

            } elseif ('success' == $request->status) {
                $this->transaction->setStatus($transaction::STATUS_QUEUED);
            }
        }
    }

    /**
     * Get callback owner transaction or null
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function getCallbackOwnerTransaction()
    {
        $request = Request::getInstance();

        return Database::getRepo('XLite\Model\Payment\Transaction')
            ->findOneBy(array('transaction_id' => $request->Shp_ord));
    }


    /**
     * Process callback
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     *
     * @return void
     */
    public function processCallback(Transaction $transaction)
    {
        parent::processCallback($transaction);

        $request = Request::getInstance();

        $signature = strtoupper(
            md5(
                $request->OutSum
                . ':' . $request->InvId
                . ':' . $this->getSetting('password2')
                . ':Shp_ord=' . $request->Shp_ord
            )
        );

        $transactionNote = 'InvId: ' . $request->InvId
            . '; Shp_ord: ' . $request->InvId
            . '; SignatureValue: ' . $request->SignatureValue
            . '; OutSum: ' . $request->OutSum
            . ';' . PHP_EOL;

        if ($signature != $request->SignatureValue) {
            $transactionNote .= static::t('Transaction failed. Reason: Wrong sign');

            $this->transaction->setNote($transactionNote);
            $this->transaction->setStatus($transaction::STATUS_FAILED);

            $result = 'Wrong sign' . PHP_EOL;

        } else {
            $this->getTransactionState($transaction);

            $result = 'OK' . $request->InvId . PHP_EOL;
        }

        header('Content-Type: text/xml');
        header('Content-Length: ' . strlen($result));

        echo ($result);
    }

    /**
     * Convert transaction params to the string
     *
     * @param string $info Transaction params
     *
     * @return string
     */
    protected function convertTransactionInfoToString($info)
    {
        $transactionNote = '';

        if (!empty($info)) {
            foreach ($info as $k => $v) {
                if (!empty($v)) {
                    $transactionNote .= $k . ': ' . $v . '; ';
                }
            }
        }

        return rtrim(rtrim($transactionNote), ';');
    }

    /**
     * Check transaction state
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    protected function getTransactionState(Transaction $transaction)
    {
        $error = array();

        $request = Request::getInstance();

        $signature = md5($this->getSetting('login') . ':' . $request->InvId . ':' . $this->getSetting('password2'));

        $data = array (
            'MerchantLogin' => $this->getSetting('login'),
            'InvoiceID'     => $request->InvId,
            'Signature'     => $signature,
        );

        if ($this->isTestModeEnabled()) {
            $data['StateCode'] = 100;
        }

        $postURL = $this->getPostFormURL();

        $xmlRequest = new \XLite\Core\HTTP\Request($postURL);
        $xmlRequest->body = $data;
        $xmlRequest->verb = 'POST';

        $response = $xmlRequest->sendRequest();

        if ($response->body) {
            $xml = \XLite\Core\XML::getInstance();

            $xmlParsed = $xml->parse($response->body, $error);

            $transactionNote = $this->transaction->getNote();

            if ($error) {
                $transactionNote .= static::t('Code') . ': ' . $error['code'] . '; '
                    . static::t('Description') . ': ' . $error['string'] . '; '
                    . static::t('Line') . ': ' . $error['line'] . ';' . PHP_EOL
                    . static::t('Result') . ': ' . $response->body;

                $this->transaction->setStatus($transaction::STATUS_FAILED);

            } else {
                $resultCode = $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Result/Code/0/#');

                if ('0' !== $resultCode) {
                    $resultDesc = $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Result/Description/0/#');

                    $transactionNote .= static::t('Code') . ': ' . $resultCode . '; '
                        . static::t('Result') . ': ' . $resultDesc;

                    $this->transaction->setStatus($transaction::STATUS_FAILED);

                } else {
                    $stateCode = $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/State/Code/0/#');

                    $info = array(
                        'IncCurrLabel'              => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/IncCurrLabel/0/#'),
                        'IncSum'                    => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/IncSum/0/#'),
                        'IncAccount'                => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/IncAccount/0/#'),
                        'PaymentMethodCode'         => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/PaymentMethod/Code/0/#'),
                        'PaymentMethodDescription'  => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/PaymentMethod/Description/0/#'),
                        'OutCurrLabel'              => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/OutCurrLabel/0/#'),
                        'OutSum'                    => $xml->getArrayByPath($xmlParsed, 'OperationStateResponse/Info/OutSum/0/#'),
                    );

                    if (
                        0 < $stateCode
                        && in_array($stateCode, array_keys($this->stateCodes))
                    ) {
                        $this->transaction->setStatus($this->stateCodes[$stateCode]);
                    }

                    $transactionNote .= static::t('Code') . ': ' . $stateCode . '; ';

                    if (!empty($info)) {
                        $transactionNote .= $this->convertTransactionInfoToString($info);
                    }
                }

                $this->transaction->setNote($transactionNote);
            }
        }
    }

    /**
     * Return TRUE if the test mode is ON
     *
     * @return boolean
     */
    protected function isTestModeEnabled()
    {
        return $this->getSetting('mode') == 'test';
    }

    /**
     * Get payment form URL
     *
     * @return string
     */
    protected function getFormURL()
    {
        return $this->isTestModeEnabled()
            ? static::FORM_URL_TEST
            : static::FORM_URL_LIVE;
    }

    /**
     * Get POST form URL (for checking of the transaction status)
     *
     * @return string
     */
    protected function getPostFormURL()
    {
        return $this->isTestModeEnabled()
            ? static::POST_FORM_URL_TEST
            : static::POST_FORM_URL_LIVE;
    }

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        $transactionId = $this->transaction->getTransactionId();
        $orderNum = $this->getOrder()->getOrderNumber();

        $successUrl = \XLite::getInstance()->getShopURL(
            Converter::buildURL('payment_return', '', array('status' => 'success', 'txnId' => $transactionId)),
            Config::getInstance()->Security->customer_security
        );

        $failUrl = \XLite::getInstance()->getShopURL(
            Converter::buildURL('payment_return', '', array('status' => 'fail', 'txnId' => $transactionId)),
            Config::getInstance()->Security->customer_security
        );

        $callbackUrl = \XLite::getInstance()->getShopURL(
            Converter::buildURL('callback', 'callback', array()),
            Config::getInstance()->Security->customer_security
        );

        $paymentDesc = substr(Translation::lbl('Order X', array('id' => $orderNum)), 0, 100);

        $login = $this->getSetting('login');
        $amount = $this->transaction->getValue();
        $password1 = $this->getSetting('password1');

        $signature  = md5(
            $login
            . ':' . $amount
            . ':' . $orderNum
            . ':' . $password1
            . ':' . 'Shp_ord=' . $transactionId
        );

        return array(
            'MrchLogin'         => $login,
            'OutSum'            => $amount,
            'InvId'             => $orderNum,
            'Desc'              => $paymentDesc,
            'SignatureValue'    => $signature,
            'Shp_ord'           => $transactionId,
            'IncCurrLabel'      => $this->getSetting('currency'),
            'Culture'           => $this->getSetting('language'),
        );
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
}
