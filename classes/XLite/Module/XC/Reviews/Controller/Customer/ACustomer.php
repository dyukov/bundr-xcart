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

namespace XLite\Module\XC\Reviews\Controller\Customer;

/**
 * Review modify controller
 *
 */
class ACustomer extends \XLite\Controller\Customer\ACustomer implements \XLite\Base\IDecorator
{
    /**
     * Return TRUE if customer already reviewed product
     *
     * @return boolean
     */
    public function isProductReviewedByUser()
    {
        $product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getProductId());

        return $product->isReviewedByUser($this->getProfile());
    }

    /**
     * Return TRUE if customer can add review for product
     *
     * @return boolean
     */
    public function isAllowedAddReview()
    {
        $result = true;

        if ($this->isRegisteredUserOnlyAbleLeaveFeedback()) {
            if (!$this->getProfile()) {
                $result = false;
            }
        }

        if ($result && $this->isProductReviewedByUser()) {
            $result = false;
        }

        if ($result && $this->isPurchasedCustomerOnlyAbleLeaveFeedback() && (!$this->getProfile() || !$this->isUserPurchasedProduct())) {
            $result = false;
        }

        return $result;
    }

    /**
     * Return message instead of 'Add review' button if customer is not allowed to add review
     *
     * @return string
     */
    public function getAddReviewMessage()
    {
        $message = null;

        if ($this->isRegisteredUserOnlyAbleLeaveFeedback() && !$this->getProfile()) {
            $message = 'Please sign in to add review';
        }

        if (empty($message) && $this->isProductReviewedByUser()) {
            $message = 'You have already reviewed this product';
        }

        if (empty($message) && $this->isPurchasedCustomerOnlyAbleLeaveFeedback()) {
            $message = 'Only customers who purchased this product can leave feedback on this product';
        }

        return static::t($message);
    }

    /**
     * Return TRUE if registered customers only can leave feedback
     *
     * @return boolean
     */
    public function isRegisteredUserOnlyAbleLeaveFeedback()
    {
        $whoCanLeaveFeedback = \XLite\Core\Config::getInstance()->XC->Reviews->whoCanLeaveFeedback;

        return (\XLite\Module\XC\Reviews\Model\Review::REGISTERED_CUSTOMERS == $whoCanLeaveFeedback);
    }

    /**
     * Return TRUE if only customers who purchased this product can leave feedback
     *
     * @return boolean
     */
    public function isPurchasedCustomerOnlyAbleLeaveFeedback()
    {
        $whoCanLeaveFeedback = \XLite\Core\Config::getInstance()->XC->Reviews->whoCanLeaveFeedback;

        return (\XLite\Module\XC\Reviews\Model\Review::PURCHASED_CUSTOMERS == $whoCanLeaveFeedback);
    }

    /**
     * Return true if customer purchased the specified product
     *
     * @param integer $productId Product ID
     *
     * @return boolean
     */
    protected function isUserPurchasedProduct()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\OrderItem')
            ->countItemsPurchasedByCustomer($this->getProductId(), $this->getProfile());
    }
}
