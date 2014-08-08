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
class Review extends \XLite\Controller\Customer\ACustomer
{
    /**
     * review
     *
     * @var \XLite\Module\XC\Reviews\Model\Review
     */
    protected $review;

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return ($this->getReview() && $this->getReview()->isPersistent())
            ? 'Edit your review'
            : 'Add your own review';
    }

    /**
     * Get return URL
     *
     * @return string
     */
    public function getReturnURL()
    {
        $url = parent::getReturnURL();

        if (\XLite\Core\Request::getInstance()->action) {
            $target = $this->getReturnTarget();
            $params = array('product_id' => $this->getProductId());

            $widget = \XLite\Core\Request::getInstance()->widget;
            if ($widget) {
                $params['widget'] = $widget;
            }

            $url = $this->buildURL($target, '', $params);
        }

        return $url;
    }

    /**
     * Return current product Id
     *
     * @return integer
     */
    public function getProductId($getFromReview = true)
    {
        $productId = \XLite\Core\Request::getInstance()->product_id;

        if (empty($productId) && $getFromReview) {
            $review = $this->getReview();

            if ($review) {
                $productId = $review->getProduct()->getProductId();
            }
        }

        return $productId;
    }

    /**
     * Return review
     *
     * @return \XLite\Module\XC\Reviews\Model\Review
     */
    public function getReview()
    {
        $id = $this->getId();
        if ($id) {
            $review = \XLite\Core\Database::getRepo('XLite\Module\XC\Reviews\Model\Review')->find($id);
        } else {
            $review = new \XLite\Module\XC\Reviews\Model\Review;

            $review->setEmail($this->getProfileField('email'));
            $review->setReviewerName($this->getProfileField('reviewerName'));
            $review->setRating(\XLite\Module\XC\Reviews\Model\Review::MAX_RATING);
        }

        return $review;
    }

    /**
     * Return review Id
     *
     * @return integer
     */
    public function getId()
    {
        $id = \XLite\Core\Request::getInstance()->id;

        if (empty($id)) {
            $product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getProductId(false));
            $profile = $this->getProfile();
            $findByIp = false;
            $review = $product->getReviewAddedByUser($profile, $findByIp);

            if ($review) {
                $id = $review->getId();
            }
        }

        return $id;
    }

    /**
     * Return target to return
     *
     * @return string
     */
    public function getReturnTarget()
    {
        return \XLite\Core\Request::getInstance()->return_target;
    }

    /**
     * Return current category Id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return \XLite\Core\Request::getInstance()->category_id;
    }

    /**
     * Return current profile
     *
     * @return \XLite\Model\Profile
     */
    public function getProfile()
    {
        return \XLite\Core\Auth::getInstance()->getProfile() ? :
            null;
    }

    /**
     * Return field value from current profile
     *
     * @return string
     */
    public function getProfileField($field)
    {
        $value = '';
        $auth = \XLite\Core\Auth::getInstance();
        if ($auth->isLogged()) {
            switch ($field) {
                case 'reviewerName':
                    if (0 < $auth->getProfile()->getAddresses()->count()) {
                        $value = $auth->getProfile()->getAddresses()->first()->getName();
                    }
                    break;

                case 'email':
                    $value = $auth->getProfile()->getLogin();
                    break;

                default:
            }
        }

        return $value;
    }

    /**
     * Alias
     *
     * @return \XLite\Module\XC\Reviews\Model\Review
     */
    protected function getEntity()
    {
        return $this->getReview();
    }

    /**
     * Get posted data
     *
     * @return array
     */
    protected function getRequestData()
    {
        $data = \XLite\Core\Request::getInstance()->getData();

        return $data;
    }

    /**
     * Update review ids saved in session
     * used for connection between anonymous user and his reviews
     *
     * @return boolean
     */
    protected function updateReviewIds(\XLite\Module\XC\Reviews\Model\Review $entity)
    {
        if (!$this->getProfile()) {

            $reviewIds = \XLite\Core\Session::getInstance()->reviewIds;

            if (!is_array($reviewIds)) {
                $reviewIds = array();
            }

            if ($entity->getId()) {
                array_push($reviewIds, $entity->getId());
            }

            \XLite\Core\Session::getInstance()->reviewIds = array_unique($reviewIds);
        }

        return true;
    }

    /**
     * Rate product
     *
     * @return void
     */
    protected function doActionRate()
    {
        $this->doActionModify();

        $this->setPureAction(true);
    }

    /**
     * Modify model
     *
     * @return void
     */
    protected function doActionModify()
    {
        if ($this->getId()) {
            $this->doActionUpdate();

        } else {
            $this->doActionCreate();
        }
    }

    /**
     * Create new model
     *
     * @return void
     */
    protected function doActionCreate()
    {
        $data = $this->getRequestData();

        $review = new \XLite\Module\XC\Reviews\Model\Review();

        $review->map($data);
        $review->setProfile($this->getProfile());
        $review->setIp(ip2long($_SERVER['REMOTE_ADDR']));

        if (!$review->getEmail()) {
            $review->setEmail($this->getProfileField('email'));
        }

        if (!$review->getReviewerName()) {
            $review->setReviewerName($this->getProfileField('reviewerName'));
        }

        $status = (false == \XLite\Core\Config::getInstance()->XC->Reviews->disablePendingReviews || !$review->getReview())
            ? \XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED
            : \XLite\Module\XC\Reviews\Model\Review::STATUS_PENDING;

        $review->setStatus($status);

        $product = \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getProductId());
        $review->setProduct($product);
        $product->addReviews($review);

        \XLite\Core\Database::getEM()->flush();

        $message = 'Thank your for sharing your opinion with us!';

        if (!$review->getReview()) {
            $message = 'Your product rating is saved. Thank you!';
        }

        $this->updateReviewIds($review);

        \XLite\Core\TopMessage::addInfo(
            static::t($message)
        );
    }

    /**
     * Update model
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $review = $this->getReview();
        $review->setStatus(\XLite\Module\XC\Reviews\Model\Review::STATUS_PENDING);
        $data = $this->getRequestData();
        $review->map($data);

        \XLite\Core\Database::getEM()->flush();

        $this->updateReviewIds($review);

        \XLite\Core\TopMessage::addInfo(
            static::t('Your review has been updated. Thank your for sharing your opinion with us!')
        );
    }
}
