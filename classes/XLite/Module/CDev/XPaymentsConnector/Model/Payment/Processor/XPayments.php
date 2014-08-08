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
 */
class XPayments extends \XLite\Module\CDev\XPaymentsConnector\Model\Payment\Processor\AXPayments
{
    /**
     * Form fields
     *
     * @var array
     */
    protected $formFields;

    /**
     * Get input template
     *
     * @return string|void
     */
    public function getInputTemplate()
    {
        return 'modules/CDev/XPaymentsConnector/checkout/save_card_box.tpl';
    }

    /**
     * Returns the list of settings available for this payment processor
     *
     * @return array
     */
    public function getAvailableSettings()
    {
        return array(
            'name',
            'id',
            'sale',
            'auth',
            'capture',
            'capturePart',
            'captureMulti',
            'void',
            'voidPart',
            'voidMulti',
            'refund',
            'refundPart',
            'refundMulti',
            'getInfo',
            'accept',
            'decline',
            'test',
            'authExp',
            'captMinLimit',
            'captMaxLimit',
            'moduleName',
            'settingsHash',
            'saveCards',
            'canSaveCards',
            'currency',
        );
    }

    /**
     * Process return
     *
     * @param \XLite\Model\Payment\Transaction $transaction Return-owner transaction
     *
     * @return void
     */
    public function processReturn(\XLite\Model\Payment\Transaction $transaction)
    {
        $txnId = \XLite\Core\Request::getInstance()->txnId;
        list($status, $response) = $this->client->requestPaymentInfo($txnId);

        $transactionStatus = $transaction::STATUS_FAILED;

        $this->client->clearInitDataFromSession();

        if ($status) {
            $transaction->setDataCell('xpc_message', $response['message'], 'X-Payments response');

            if ($response['isFraudStatus']) {
                $transaction->setDataCell('xpc_fmf', 'blocked', 'Fraud status');
            }

            if ($response['amount'] != $transaction->getOrder()->getTotal()) {

                // Total wrong
                $transaction->setDataCell('error', 'Hacking attempt!', 'Error');
                $transaction->setDataCell(
                    'errorDescription',
                    'Hacking attempt details',
                    'Total amount doesn\'t match: Order total = '
                    . $transaction->getOrder()->getTotal()
                    . ', X-Payments amount = ' . $response['amount']
                );

            } elseif ($response['currency'] != $transaction->getOrder()->getCurrency()->getCode()) {

                // Currency wrong
                $transaction->setDataCell('error', 'Hacking attempt!', 'Error');
                $transaction->setDataCell(
                    'errorDescription',
                    'Currency code doesn\'t match: Order currency = '
                    . $transaction->getOrder()->getCurrency()->getCode()
                    . ', X-Payments currency = ' . $response['currency'],
                    'Hacking attempt details'
                );

            } else {
                $transactionStatus = $this->getTransactionStatus($response);
            }
        }

        if ($transactionStatus) {
            $transaction->setStatus($transactionStatus);
        }

        $this->transaction = $transaction;
    }

    /**
     * This is not Saved Card payment method
     *
     * @return boolean
     */
    protected function isSavedCardsPaymentMethod()
    {
        return false;
    }

    /**
     * Do initial payment
     *
     * @return string Status code
     */
    protected function doInitialPayment()
    {
        $status = parent::doInitialPayment();
        if (
            static::PROLONGATION == $status
            && true === \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector->xpc_use_iframe
        ) {
            exit ();
        }

        return $status;
    }

    /**
     * Get redirect form URL
     *
     * @return string
     */
    protected function getFormURL()
    {
        $config = \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector;

        return preg_replace('/\/+$/Ss', '', $config->xpc_xpayments_url) . '/payment.php';
    }

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        $this->formFields = $this->client->getFormFields($this->transaction);

        return $this->formFields;
    }

}
