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

namespace XLite\Module\CDev\XPaymentsConnector\Controller\Customer;

/**
 * Callback 
 *
 */
class Callback extends \XLite\Controller\Customer\Callback implements \XLite\Base\IDecorator
{

    /**
     * Allow check cart action
     *
     * @return string
     */
    public function getAction()
    {
        return 'check_cart' == \XLite\Core\Request::getInstance()->action
            ? \XLite\Core\Request::getInstance()->action
            : parent::getAction();
    }

    /**
     * Send current cart details back to X-Payments.   
     *
     * @return void
     */
    protected function doActionCheckCart()
    {
        $refId = \XLite\Core\Request::getInstance()->refId;
        
        $transaction = $this->detectTransaction();

        \XLite\Logger::getInstance()->logCustom(
            'xp-connector',
            'Check cart action. RefId: ' . var_export($refId, true) . PHP_EOL
                . 'Transaction: ' . ($transaction ? $transaction->getTransactionId() : 'n/a') . PHP_EOL
                . 'Order: ' . ($transaction ? $transaction->getOrder()->getOrderId() : 'n/a')
        );

        if ($transaction) {
            $cart = $transaction->getOrder();

            $response = array(
                'status' => 'cart-changed',
                'ref_id' => $refId,
            );

            $clientXpayments = \XLite\Module\CDev\XPaymentsConnector\Core\XPaymentsClient::getInstance();

            // Prepare cart
            $preparedCart = $clientXpayments->prepareCart($cart, $refId);

            if ($cart && $preparedCart) {
                $response['cart'] = $preparedCart;
            }

            // Convert array to XML
            $xml = $clientXpayments->convertHashToXml($response);

            if ($xml) {
                $xml = $clientXpayments->encryptXml($xml);

                if ($xml) {
                    print ($xml);
                    die (0);
                }
            }
        }
    }

}
