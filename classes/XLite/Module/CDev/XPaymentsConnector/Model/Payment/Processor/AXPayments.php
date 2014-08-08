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

namespace XLite\Module\CDev\XPaymentsConnector\Model\Payment\Processor;

/**
 * XPayments payment processor
 *
 */
abstract class AXPayments extends \XLite\Model\Payment\Base\WebBased
{
    /**
     * Payment statuses
     */
    const STATUS_NEW            = 1;
    const STATUS_AUTH           = 2;
    const STATUS_DECLINED       = 3;
    const STATUS_CHARGED        = 4;
    const STATUS_REFUND         = 5;
    const STATUS_REFUND_PART    = 6;

    /**
     * Payment processor classes 
     */
    const METHOD_SAVED_CARD = 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\SavedCard';
    const METHOD_XPAYMENTS  = 'Module\CDev\XPaymentsConnector\Model\Payment\Processor\XPayments';

    /**
     * Log file name for callback requests
     */
    const LOG_FILE_NAME = 'xp-connector-callback';

    /**
     * X-Payments client
     *
     * @var \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient
     */
    protected $client;

    /**
     * This is not Saved Card payment method
     *
     * @return boolean
     */
    abstract protected function isSavedCardsPaymentMethod();

    /**
     * Payment method has settings into Module settings section
     *
     * @return boolean
     */
    public function hasModuleSettings()
    {
        return true;
    }

    /**
     * Get operation types
     *
     * @return array
     */
    public function getOperationTypes()
    {
        $types = array(
            static::OPERATION_SALE,
            static::OPERATION_AUTH,
            static::OPERATION_CAPTURE,
            static::OPERATION_CAPTURE_PART,
            static::OPERATION_CAPTURE_MULTI,
            static::OPERATION_VOID,
            static::OPERATION_VOID_PART,
            static::OPERATION_VOID_MULTI,
            static::OPERATION_REFUND,
            static::OPERATION_REFUND_PART,
            static::OPERATION_REFUND_MULTI,
        );

        foreach ($types as $k => $v) {
            if (!$this->transaction->getPaymentMethod()->getSetting($v)) {
                unset($types[$k]);
            }
        }

        return $types;
    }

    /**
     * Check - payment method is configured or not
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isConfigured(\XLite\Model\Payment\Method $method)
    {
        return parent::isConfigured($method)
            && \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance()->isModuleConfigured();
    }

    /**
     * Get payment method configuration page URL
     *
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return string
     */
    public function getConfigurationURL(\XLite\Model\Payment\Method $method)
    {
        return ($this->getModule() && $this->getModule()->getModuleId())
            ? \XLite\Core\Converter::buildURL(
                'xpc',
                '',
                array('section' => 'payment_methods')
            )
            : parent::getConfigurationURL($method);
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
     * Process callback
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     *
     * @return void
     */
    public function processCallback(\XLite\Model\Payment\Transaction $transaction)
    {
        $request = \XLite\Core\Request::getInstance();

        list($status, $updateData) = $this->client->decryptXml($request->updateData);
        if ($status) {
            $updateData = $this->client->convertXmlToHash($updateData);
            $updateData = $updateData['data'];

            \XLite\Logger::getInstance()->logCustom(
                'xp-connector',
                'Callback data: ' . var_export($updateData, true)
            );

            if (
                $request->txnId
                && $updateData
                && isset($updateData['status'])
            ) {
                $status = $this->getTransactionStatus($updateData);
                if ($status) {
                    $transaction->setStatus($status);
                    $this->registerBackendTransaction($transaction, $updateData);

                    \XLite\Core\Database::getEM()->flush();
                }
            }
        }
    }

    /**
     * Get callback request owner transaction or null
     *
     * @return \XLite\Model\Payment\Transaction
     */
    public function getCallbackOwnerTransaction()
    {
        $request = \XLite\Core\Request::getInstance();

        $requestLogData = PHP_EOL 
            . 'Callback Backreference: '
            . var_export(\XLite\Core\Request::getInstance()->xpcBackReference, true) . PHP_EOL
            . 'Callback txnId: '
            . var_export(\XLite\Core\Request::getInstance()->txnId, true) . PHP_EOL; 

        $result = null;

        if ($this->checkIpAddress() && $request->xpcBackReference) {

            // Search transaction by back reference from request
            $transaction = $this->searchTransactionByBackReference($request->xpcBackReference);
            $requestLogData .= 'Transaction: ' . ($transaction ? $transaction->getTransactionId() : 'n/a') . PHP_EOL;

            if (!$request->updateData && !$this->isSavedCardsPaymentMethod()) {

                // This is a 'check cart' callback request
                \XLite\Logger::getInstance()->logCustom(
                    static::LOG_FILE_NAME,
                    'Check cart callback request received' . $requestLogData
                );

                $result = $transaction;

            } else {

                // Decrypt update data
                list($status, $updateData) = $this->client->decryptXml($request->updateData);
                if ($status) {
                    $updateData = $this->client->convertXmlToHash($updateData);
                    $updateData = $updateData['data'];
                    $requestLogData .= 'Parent ID received: ' . var_export($updateData['parentId'], true) . PHP_EOL;

                } else {
                    $requestLogData .= 'Decrypt XML error!';
                }

                if ($updateData && !isset($updateData['parentId'])) {

                    // Parent ID is not received
                    // This is calback of X-Payments payment method
                    if (!$this->isSavedCardsPaymentMethod()) {
                        \XLite\Logger::getInstance()->logCustom(
                            static::LOG_FILE_NAME,
                            'Card present payment method callback request' . $requestLogData
                        );
                        $result = $transaction;
                    }

                } elseif ($this->isSavedCardsPaymentMethod() && $transaction) {

                    // Search Saved Card transaction in profile
                    $result = $this->searchSavedCardTransaction($transaction, $requestLogData, $request);

                } elseif ($transaction && !$this->isSavedCardTransaction($transaction)) {

                    // Create new order for subscription/recharge created on X-Payments side
                    // by the original card present transaction passed by backreference
                    $result = $this->createChildTransaction($transaction, $updateData);
                    \XLite\Logger::getInstance()->logCustom(
                        static::LOG_FILE_NAME,
                        'Create new order callback request' . $requestLogData
                    );

                } else {
                    \XLite\Logger::getInstance()->logCustom(
                        static::LOG_FILE_NAME,
                        'Can not procesed callback' . $requestLogData
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get return request owner transaction or null
     *
     * @return \XLite\Model\Payment\Transaction|void
     */
    public function getReturnOwnerTransaction()
    {
        return \XLite\Core\Request::getInstance()->xpcBackReference
            ? $this->searchTransactionByBackReference(\XLite\Core\Request::getInstance()->xpcBackReference)
            : null;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function __construct()
    {
        $this->client = new \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient;
    }

    /**
     * Get transaction status by action
     *
     * @param array $data Data
     *
     * @return string
     */
    protected function getTransactionStatus(array $data)
    {
        switch (intval($data['status'])) {
            case static::STATUS_NEW:
                $transactionStatus = \XLite\Model\Payment\Transaction::STATUS_INITIALIZED;
                break;

            case static::STATUS_AUTH:
            case static::STATUS_CHARGED: 
                $transactionStatus = \XLite\Model\Payment\Transaction::STATUS_SUCCESS;
                break;

            case static::STATUS_DECLINED:
                $transactionStatus = (0 == $data['authorized'])
                    ? \XLite\Model\Payment\Transaction::STATUS_FAILED
                    : \XLite\Model\Payment\Transaction::STATUS_VOID;
                break;

            case static::STATUS_REFUND:
            case static::STATUS_REFUND_PART:
            default: 
                $transactionStatus = \XLite\Model\Payment\Transaction::STATUS_CANCELED;
                break;
        }
    
        return $transactionStatus;
    }

    /**
     * Get transaction refunded amount 
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     * @param array                            $data        Data
     *
     * @return void
     */
    protected function getRefundedAmount(\XLite\Model\Payment\Transaction $transaction, array $data)
    {
        $amount = 0;
        $types = array(
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND,
            \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_PART,
        );

        if (in_array($data['status'], array(static::STATUS_REFUND, static::STATUS_REFUND_PART))) {

            foreach ($transaction->getBackendTransactions() as $bt) {
                if ($bt->isCompleted() && in_array($bt->getType(), $types)) {
                    $amount += $bt->getValue();
                }
            }

            $amount = $data['refundedAmount'] - $amount;
        }

        return $amount;
    }

    /**
     * Register backend transaction 
     *
     * @param \XLite\Model\Payment\Transaction $transaction Callback-owner transaction
     * @param array                            $data        Data
     *
     * @return void
     */
    protected function registerBackendTransaction(\XLite\Model\Payment\Transaction $transaction, array $data)
    {
        $type = null;
        $value = null;

        switch ($data['status']) {
            case static::STATUS_NEW:
                $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE;
                break;

            case static::STATUS_AUTH:
                $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH;
                break;

            case static::STATUS_DECLINED:
                if (0 == $data['authorized'] && 0 == $data['chargedAmount']) {
                    $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_DECLINE;

                } elseif ($data['amount'] == $data['voidedAmount']) {
                    $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID;

                } else {

                    $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_VOID_PART;
                    $value = $data['voidedAmount'];
                }

                break;

            case static::STATUS_CHARGED:
                if ($data['amount'] == $data['chargedAmount']) {
                    $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE;
                } else {
                    $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_CAPTURE_PART;
                    $value = $data['capturedAmount'];
                }
                break;

            case static::STATUS_REFUND:
                $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND;
                $value = $this->getRefundedAmount($transaction, $data);

                break;

            case static::STATUS_REFUND_PART:
                $type = \XLite\Model\Payment\BackendTransaction::TRAN_TYPE_REFUND_PART;
                $value = $this->getRefundedAmount($transaction, $data);
                
                break;


            default:

        }

        if ($type) {
            $transaction->setType($type);
            $backendTransaction = $transaction->createBackendTransaction($type);
            $backendTransaction->setStatus(\XLite\Model\Payment\BackendTransaction::STATUS_SUCCESS);
            if ($value) {
                $backendTransaction->setValue($value);
            }
            $backendTransaction->setDataCell('xpc_message', $data['message']);
            $backendTransaction->registerTransactionInOrderHistory('callback');
        }
    }

    /**
     * Search saved card transaction 
     * 
     * @param \XLite\Model\Payment\Transaction $transaction    Transaction
     * @param string                           $requestLogData Request log
     * @param \XLite\Core\Request              $request        Request
     *  
     * @return mixed
     */
    protected function searchSavedCardTransaction(\XLite\Model\Payment\Transaction $transaction, $requestLogData, \XLite\Core\Request $request)
    {
        $result = null;

        $openProfileTransaction = $this->findOpenProfileTransaction($transaction);

        if ($openProfileTransaction) {

            // This is calback for open SavedCard transaction 
            \XLite\Logger::getInstance()->logCustom(
                static::LOG_FILE_NAME,
                'Open SavedCard transaction callback request' . $requestLogData
            );
            $result = $openProfileTransaction;

        } else {

            // Search transaction by txnId which is X-Payments' paymentId
            $initialTransaction = $this->searchTransactionByTxnId($request->txnId);

            if ($initialTransaction) {

                // Return initial transaction found bt txnId 
                // Applies for customers' transactions and recharges from X-Payments
                \XLite\Logger::getInstance()->logCustom(
                    static::LOG_FILE_NAME,
                    'Secondary saved card transaction callback request' . $requestLogData
                );
                $result = $initialTransaction;
            }
        }

        return $result;
    }

    /**
     * Find trannsaction by X-Payments back refernece to X-Cart 
     *
     * @param string $xpcBackReference X-Payment connector Backend reference
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function searchTransactionByBackReference($xpcBackReference)
    {
        $transactionData = \XLite\Core\Database::getRepo('XLite\Model\Payment\TransactionData')
            ->findOneBy(array('value' => $xpcBackReference, 'name' => 'xpcBackReference'));

        return $transactionData
            ? $transactionData->getTransaction()
            : null;
    }

    /**
     * Find trannsaction by X-Payments paymentId (stored as xpc_txnid) 
     *
     * @param integer $xpcTxnId XPC transaction id
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function searchTransactionByTxnId($xpcTxnId)
    {
        $transactionData = \XLite\Core\Database::getRepo('XLite\Model\Payment\TransactionData')
            ->findOneBy(array('value' => $xpcTxnId, 'name' => 'xpc_txnid'));

        return $transactionData
            ? $transactionData->getTransaction()
            : null;
    }


    /**
     * Get Saved Card payment method
     *
     * @return array
     */
    protected function getSavedCardsPaymentMethod()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
            ->findOneBy(array('class' => self::METHOD_SAVED_CARD));
    }

    /**
     * Is transaction payment method saved card or not 
     *
     * @param \XLite\Model\Payment\Transaction $transaction Transaction
     *
     * @return boolean
     */
    protected function isSavedCardTransaction(\XLite\Model\Payment\Transaction $transaction)
    {
        return static::METHOD_SAVED_CARD == $transaction->getPaymentMethod()->getClass(); 
    }
    

    /**
     * Create transaction by parent one and data passed from X-Payments
     *
     * @param \XLite\Model\Payment\Transaction $parentTransaction Parent transaction
     * @param array                            $updateData        Update data passed from X-PAyments
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function createChildTransaction($parentTransaction, array $updateData)
    {
        $cart = \XLite\Model\Cart::getInstance(false);

        $cart->setTotal($updateData['amount']);
        $cart->setDate(time());
        $cart->setOrderNumber(\XLite\Core\Database::getRepo('XLite\Model\Order')->findNextOrderNumber());
        $cart->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_PAID);

        $cart->setProfile($parentTransaction->getOrder()->getProfile());

        $cart->setPaymentMethod($this->getSavedCardsPaymentMethod(), $updateData['amount']);

        $cart->markAsOrder();

        $transaction = $cart->getFirstOpenPaymentTransaction();

        if ($transaction) {
            $creditCardData = $parentTransaction->getCard();

            $transaction->setDataCell(
                'xpc_txnid',
                \XLite\Core\Request::getInstance()->txnId,
                'X-Payments transaction id'
            );
            $transaction->setDataCell('xpc_message', $updateData['message'], 'X-Payments response');

            $transaction->setXpcDetails($creditCardData['card_number'], $creditCardData['card_type']);
        }

        return $transaction;
    }

    /**
     * Find open transaction in customer's profile 
     *
     * @param \XLite\Model\Payment\Transaction $parentTransaction Parent transaction
     *
     * @return \XLite\Model\Payment\Transaction
     */
    protected function findOpenProfileTransaction(\XLite\Model\Payment\Transaction $parentTransaction)
    {
        $openTransaction = null;

        $login = $parentTransaction->getOrder()->getProfile()->getLogin();

        $cnd = new \XLite\Core\CommonCell();

        $class = 'XLite\Module\CDev\XPaymentsConnector\Model\Payment\XpcTransactionData';

        $cnd->{\XLite\Module\CDev\XPaymentsConnector\Model\Repo\Payment\XpcTransactionData::SEARCH_LOGIN} = $login;

        $profileTransactions = \XLite\Core\Database::getRepo($class)->search($cnd);

        foreach ($profileTransactions as $transactionData) {

            $transaction = $transactionData->getTransaction();

            if (
                $this->isSavedCardTransaction($transaction)
                && ($transaction->isPending() || $transaction->isInProgress())
            ) {
                $openTransaction = $transaction;
                break;
            }
        }

        return $openTransaction;
    }


    /**
     * Check IP addres of callback request
     *
     * @return boolean
     */
    protected function checkIpAddress()
    {
        $allowedIpAddresses = \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_allowed_ip_addresses;
        $result = !$allowedIpAddresses || false !== substr($allowedIpAddresses, $_SERVER['REMOTE_ADDR']);

        if (!$result) {
            \XLite\Logger::getInstance()->logCustom(
                self::LOG_FILE_NAME,
                'Callback request from IP address of "' . $_SERVER['REMOTE_ADDR']
                . '" cannot be recognized as X-Payments one, since it came from unallowed IP address'
            );
        }

        return $result;
    }

}
