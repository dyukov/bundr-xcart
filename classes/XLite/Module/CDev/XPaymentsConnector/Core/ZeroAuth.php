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
 * Zero-dollar authorization (card setup)
 *
 */
class ZeroAuth extends \XLite\Base\Singleton
{
    /**
     * Get config
     *
     * @return object
     */
	protected static function getConfig()
	{
		return \XLite\Core\Config::getInstance()->CDev->XPaymentsConnector;
	}

    /**
     * Get X-Payments client 
     *
     * @return object
     */
	protected static function getClient()
	{
		return \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance();
	}

    /**
     * Get payment method for zero-auth (card setup)
     *
     * @return \XLite\Model\Payment\Method
     */
    public function allowZeroAuth()
    {
        return $this->getConfig()->xpc_zero_auth_method_id;
    }

    /**
     * Get payment method for zero-auth (card setup)
     *
     * @return \XLite\Model\Payment\Method
     */
    public function getPaymentMethod()
    {
        $paymentMethod = \XLite\Core\Database::getRepo('XLite\Model\Payment\Method')
			->find($this->getConfig()->xpc_zero_auth_method_id);

        return $paymentMethod;
    }

    /**
     * Get customer profile
     *
     * @return \XLite\Model\Profile
     */
    protected function detectProfile()
    {
        $profile = null;

        if (\XLite\Core\Request::getInstance()->xpcBackReference) {
            $profile = \XLite\Core\Database::getRepo('XLite\Model\Profile')
                ->findOneBy(array('pending_zero_auth' => \XLite\Core\Request::getInstance()->xpcBackReference));
        }

        return $profile;
    }

    /**
     * Prepare cart hash to send to X-Payments
     *
     * @return array
     */
    protected function getPreparedCart(\XLite\Model\Profile $profile)
    {
        $cart = \XLite\Model\Cart::getInstance(false);
        $cart->setTotal($this->getConfig()->xpc_zero_auth_amount);
        $cart->setProfile($profile);

        return $this->getClient()->prepareCart($cart, null, true, true);
    }

    /**
     * Get iframe URL
     *
     * @return string
     */
    public function getIframeUrl(\XLite\Model\Profile $profile, $interface = \XLite::CART_SELF)
    {
        $iframeUrl = false;

        // Prepare cart
        $preparedCart = $this->getPreparedCart($profile);

        if ($preparedCart) {

            $xpcBackReference = md5(time() . 'CardSetup');

            $profile->setPendingZeroAuth($xpcBackReference);
            $profile->setPendingZeroAuthInterface($interface);
            \XLite\Core\Database::getEM()->flush();

            $returnUrl = \XLite::getInstance()->getShopUrl(
                \XLite\Core\Converter::buildUrl(
                    'add_new_card',
                    'return',
                    array('xpcBackReference' => $xpcBackReference),
                    \XLite::CART_SELF
                )
            );

            $callbackUrl = \XLite::getInstance()->getShopUrl(
                \XLite\Core\Converter::buildUrl(
                    'add_new_card',
                    'callback',
                    array('xpcBackReference' => $xpcBackReference),
                    \XLite::CART_SELF
                )
            );

            // Data to send to X-Payments
            $data = array(
                'confId'      => intval($this->getPaymentMethod()->getSetting('id')),
                'refId'       => $xpcBackReference,
                'cart'        => $preparedCart,
                'language'    => 'en',
                'returnUrl'   => $returnUrl,
                'callbackUrl' => $callbackUrl,
            );

            // API v1.3
            if ('1.3' == $this->getConfig()->xpc_api_version) {

                $data += array(
                    'saveCard'    => 'Y',
                    'template'    => 'xc5',
                );
            }

            list($status, $response) = $this->getClient()->getApiRequest(
                'payment',
                'init',
                $data,
                $this->getClient()->getRequestInitSchema()
            );

			if (
				$status
				&& isset($response['token'])
			) {
	            $iframeUrl = $this->getConfig()->xpc_xpayments_url . '/payment.php?target=main&token=' . $response['token'];
			}

        }

        return $iframeUrl;
    }

    /**
     * JS code to redirect back to saved cards page
     *
     * @return string 
     */
    protected function getRediectCode(\XLite\Model\Profile $profile)
    {
        $url = \XLite::getInstance()->getShopUrl(
                \XLite\Core\Converter::buildUrl(
                    'saved_cards', 
                    '', 
                    array('profile_id' => $profile->getProfileId()),
                    $profile->getPendingZeroAuthInterface()
                )
            );

        return '<script type="text/javascript">'
			. 'window.parent.location = "' . $url . '";'
			. '</script>';
    }

    /**
     * Cleanup pending zero-auth data from profile 
     *
     * @return void
     */
    protected function cleanupZeroAuthPendingData(\XLite\Model\Profile $profile)
    {
        $profile->setPendingZeroAuthStatus('');
        $profile->setPendingZeroAuthTxnId('');
        $profile->setPendingZeroAuth('');
        $profile->setPendingZeroAuthInterface('');

        \XLite\Core\Database::getEM()->flush();
    }

    /**
     * Return action
     *
     * @return void
     */
    public function doActionReturn()
    {
        $profile = $this->detectProfile();

        if ($profile) {

            if ('Y' == $profile->getPendingZeroAuthStatus()) {

                $cart = \XLite\Model\Cart::getInstance(false);

                $cart->setTotal($this->getConfig()->xpc_zero_auth_amount);
                $cart->setDate(time());
                $cart->setOrderNumber(\XLite\Core\Database::getRepo('XLite\Model\Order')->findNextOrderNumber());
                $cart->setPaymentStatus(\XLite\Model\Order\Status\Payment::STATUS_PAID);

                $cart->setProfile($profile);

                $cart->setPaymentMethod($this->getPaymentMethod(), $this->getConfig()->xpc_zero_auth_amount);

                $transactions = $cart->getPaymentTransactions();

                if ($transactions) {

                    $cart->markAsOrder();

                    foreach ($transactions as $transaction) {

                        $transaction->setXpcDetails(
                            str_repeat('*', 12) . \XLite\Core\Request::getInstance()->last_4_cc_num,
                            \XLite\Core\Request::getInstance()->card_type
                        );

                        $transaction->getXpcData()->setUseForRecharges('Y');

                        $transaction->setDataCell('xpc_txnid', $profile->getPendingZeroAuthTxnId(), 'X-Payments transaction id', 'C');
                        $transaction->setDataCell('xpcBackReference', \XLite\Core\Request::getInstance()->xpcBackReference, 'X-Payments back reference', 'C');
                    }

                }
                \XLite\Core\Database::getEM()->flush();

                \XLite\Core\TopMessage::addInfo('Card saved');

            } else {

                \XLite\Core\TopMessage::addError('Card was not saved due to payment processor error');

            }

            echo $this->getRediectCode($profile);

            // Cleanup pending zero-auth data
            $this->cleanupZeroAuthPendingData($profile);

            exit;


        } else {

            die('Error occured when saving card. Customer profile not found');
            // Just in case show erroor inside iframe

        }

	}

    /**
     * Callback from X-Payments
     *
     * @return void
     */
    public function doActionCallback()
    {
        $profile = $this->detectProfile();

        $request = \XLite\Core\Request::getInstance();

        if ($profile) {

            list($status, $xml) = $this->getClient()->decryptXml($request->updateData);

            $pendingZeroAuthStatus = 'N';

            if ($status) {
                $data = $this->getClient()->convertXmlToHash($xml);
                $data = $data['data'];

                if (
                    \XLite\Module\CDev\XPaymentsConnector\Model\Payment\Processor\AXPayments::STATUS_AUTH == $data['status']
                    || \XLite\Module\CDev\XPaymentsConnector\Model\Payment\Processor\AXPayments::STATUS_CHARGED == $data['status']
                ) {

                    $pendingZeroAuthStatus = 'Y';
                    $profile->setPendingZeroAuthTxnId($request->txnId);

                }
            }

            // Save pending zero-auth status
            $profile->setPendingZeroAuthStatus($pendingZeroAuthStatus);
            \XLite\Core\Database::getEM()->flush();

        } else {

            $transactionData = \XLite\Core\Database::getRepo('XLite\Model\Payment\TransactionData')
                ->findOneBy(array('value' => $request->xpcBackReference, 'name' => 'xpcBackReference'));

            if ($transactionData) {
                $transaction = $transactionData->getTransaction();

                $processor = $this->getPaymentMethod()->getProcessor();

                $processor->processCallback($transaction);

                $transaction->getOrder()->setPaymentStatusByTransaction($transaction);
                \XLite\Core\Database::getEM()->flush();
            }
        }

        exit;
    }

    /**
     * Check cart callback
     *
     * @return void
     */
    public function doActionCheckCart()
    {
        $profile = $this->detectProfile();

        if ($profile) {

            // Prepare cart
            $preparedCart = $this->getPreparedCart($profile);

            $response = array(
                'status' => 'cart-changed',
                'ref_id' => \XLite\Core\Request::getInstance()->refId,
                'cart'   => $preparedCart,
            );

            // Convert array to XML
            $xml = $this->getClient()->convertHashToXml($response);

            if ($xml) {
                $xml = $this->getClient()->encryptXml($xml);

                if ($xml) {
                    echo $xml;
                }

            }

        }

		exit;
    }


}
