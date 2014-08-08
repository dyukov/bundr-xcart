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
class SavedCard extends \XLite\Module\CDev\XPaymentsConnector\Model\Payment\Processor\AXPayments
{

    /**
     * Get input template
     *
     * @return string|void
     */
    public function getInputTemplate()
    {
        return 'modules/CDev/XPaymentsConnector/checkout/saved_cards.tpl';
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

        if (empty($data['saved_card_id'])) {
            $errors[] = 'Wrong credit card submitted';
        }

        return $errors;
    }

    /**
     * Check - payment processor is applicable for specified order or not
     *
     * @param \XLite\Model\Order          $order  Order
     * @param \XLite\Model\Payment\Method $method Payment method
     *
     * @return boolean
     */
    public function isApplicable(\XLite\Model\Order $order, \XLite\Model\Payment\Method $method)
    {
        return $order->getProfile()
            && $order->getProfile()->getSavedCards()
            && parent::isApplicable($order, $method);
    }

    /**
     * This is not Saved Card payment method
     *
     * @return boolean
     */
    protected function isSavedCardsPaymentMethod()
    {
        return true;
    }

    /**
     * Do initial payment
     *
     * @return string Status code
     */
    protected function doInitialPayment()
    {
        $class = 'XLite\Module\CDev\XPaymentsConnector\Model\Payment\XpcTransactionData';
        $cardId = \XLite\Core\Request::getInstance()->payment['saved_card_id'];
        $xpcTransactionData = \XLite\Core\Database::getRepo($class)
            ->find($cardId);

        $status = static::FAILED;

        if (
            $xpcTransactionData
            && $xpcTransactionData->getTransaction()
            && $xpcTransactionData->getTransaction()->getDataCell('xpc_txnid')
            && $xpcTransactionData->getTransaction()->getDataCell('xpc_txnid')->getValue()
        ) {

            $patentTxnId = $xpcTransactionData->getTransaction()->getDataCell('xpc_txnid')->getValue();

            $this->transaction->setXpcDetails(
                $xpcTransactionData->getCardNumber(),
                $xpcTransactionData->getCardType()
            );

            list($responseStatus, $response) = $this->client->requestPaymentRecharge(
                $patentTxnId,
                $this->transaction,
                'Payment via saved card'
            );

            if ($responseStatus) {

                if (isset($response['transaction_id'])) {
                    $this->transaction->setDataCell('xpc_txnid', $response['transaction_id'], 'X-Payments transaction id');
                }

                if (isset($response['status'])) {

                    if (static::STATUS_AUTH == $response['status']) {
                        $this->transaction->setType(\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_AUTH);
                        $status = static::COMPLETED;

                    } elseif (static::STATUS_CHARGED == $response['status']) {
                        $this->transaction->setType(\XLite\Model\Payment\BackendTransaction::TRAN_TYPE_SALE);
                        $status = static::COMPLETED;

                    }
                }
            }       
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
        return '';
    }

    /**
     * Get redirect form fields list
     *
     * @return array
     */
    protected function getFormFields()
    {
        return array();
    }
}
