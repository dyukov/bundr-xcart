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

namespace XLite\Module\XC\Reviews\Controller\Admin;

/**
 * Review modify controller
 *
 */
class Review extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return review id from request
     *
     * @return integer
     */
    public function getId()
    {
        return intval(\XLite\Core\Request::getInstance()->id);
    }

    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->getReview() && $this->getReview()->isPersistent()) {
            $label = $this->getReview()->isApproved() ? 'Edit review' : 'Approve review';
        } else {
            $label = 'Add review';
        }

        return \XLite\Core\Translation::getInstance()->lbl($label);
    }

    /**
     * Return the current product title
     *
     * @return string
     */
    public function getProductTitle()
    {
        return $this->getProduct()
            ? $this->getProduct()->getName()
            : null;
    }

    /**
     * Return target product Id
     *
     * @return integer
     */
    public function getRequestTargetProductId()
    {
        return intval(\XLite\Core\Request::getInstance()->target_product_id);
    }

    /**
     * Return target product title
     *
     * @return string
     */
    public function getRequestTargetProductTitle()
    {
        $product = \XLite\Core\Database::getRepo('\XLite\Model\Product')->find($this->getRequestTargetProductId());

        return $product
            ? $product->getName()
            : null;
    }

    /**
     * Return current review profile Id
     *
     * @return integer
     */
    public function getProfileId()
    {
        return $this->getProfile()
            ? $this->getProfile()->getProfileId()
            : null;
    }

    /**
     * Alias
     *
     * @return \XLite\Module\XC\Reviews\Model\Review
     */
    public function getReview()
    {
        $result = \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')->find($this->getId());

        return $result ? : new \XLite\Module\XC\Reviews\Model\Review();
    }

    /**
     * Return current product
     *
     * @return \XLite\Model\Product
     */
    public function getProduct()
    {
        return ($this->getReview())
            ? $this->getReview()->getProduct()
            : null;
    }

    /**
     * Return current product Id
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->getProduct()
            ? $this->getProduct()->getProductId()
            : null;
    }

    /**
     * Set return URL
     *
     * @return void
     */
    public function setReturnURL($url = '')
    {
        $targetProductId = $this->getRequestTargetProductId();

        $url = $targetProductId
            ? \XLite\Core\Converter::buildURL(
                'product',
                '',
                array('product_id' => $targetProductId, 'page' => 'product_reviews')
            )
            : \XLite\Core\Converter::buildURL('reviews');

        parent::setReturnUrl($url);
    }

    /**
     * Return product Id from request
     *
     * @return integer
     */
    protected function getRequestProductId()
    {
        $targetProductId = $this->getRequestTargetProductId();

        return $targetProductId ? : \XLite\Core\Request::getInstance()->product_id;
    }

    /**
     * Return product from request
     *
     * @return \XLite\Model\Product
     */
    protected function getRequestProduct()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Product')->find($this->getRequestProductId());
    }

    /**
     * Create new model
     *
     * @return void
     */
    protected function doActionCreate()
    {
        $data = \XLite\Core\Request::getInstance()->getData();
        $profile = $data['profile'] === '' ? false : $data['profile'];

        unset($data['profile']);

        $review = new \XLite\Module\XC\Reviews\Model\Review();
        $review->map($data);
        $review->setStatus(\XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED);

        if ($profile && intval($profile) > 0) {
            $this->updateProfile($review, intval($profile));
        }

        $product = $this->getRequestProduct()
            ?:
            (\XLite\Core\Database::getRepo('XLite\Model\Product')->find($data['product_title']) ?: $this->findProductBySubstring($data['product_title_text']));

        if ($product) {
            $review->setProduct($product);
            $product->addReviews($review);

            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo(
               static::t('Review has been created')
            );
        } else {
            \XLite\Core\TopMessage::addError(
                \XLite\Core\Translation::lbl('Review has not been created since product is not found', array('product' => $data['product_title']))
            );
        }

        $this->setReturnUrl();
        $this->setHardRedirect();
    }

    /**
     * Update model
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $review = \XLite\Core\Database::getRepo('XLite\Module\XC\Reviews\Model\Review')->find($this->getId());

        $data = \XLite\Core\Request::getInstance()->getData();
        $profile = $data['profile'] === '' ? false : $data['profile'];

        unset($data['profile']);

        $review->map($data);

        if ($profile && intval($profile) > 0) {
            $this->updateProfile($review, intval($profile));
        }

        $product = $this->getRequestProduct()
            ?:
            (\XLite\Core\Database::getRepo('XLite\Model\Product')->find($data['product_title']) ?: $this->findProductBySubstring($data['product_title_text']));

        if ($product) {
            $review->setProduct($product);
            $product->addReviews($review);

            \XLite\Core\Database::getEM()->flush();
        } else {
            \XLite\Core\TopMessage::addError(
                \XLite\Core\Translation::lbl('Review has not been updated since product is not found', array('product' => $data['product_title']))
            );
        }

        $this->setReturnUrl();
        $this->setHardRedirect();
    }

    /**
     * Modify model
     *
     * @return void
     */
    protected function doActionModify()
    {
        ($this->getModelForm()->getModelObject()->isPersistent())
            ? $this->doActionUpdate()
            : $this->doActionCreate();
    }

    /**
     * Approve review
     *
     * @return void
     */
    protected function doActionApprove()
    {
        \XLite\Core\Request::getInstance()->status = \XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED;
        $this->doActionUpdate();
    }

    /**
     * doActionDelete
     *
     * @return void
     */
    protected function doActionDelete()
    {
        $review = $this->getReview();

        if (isset($review)) {
            \XLite\Core\Database::getEM()->remove($review);
            \XLite\Core\Database::getEM()->flush();

            \XLite\Core\TopMessage::addInfo(
                static::t('Review has been deleted')
            );

            $this->setReturnURL();
        }
    }

    /**
     * Update the reviewer name, email and profile
     *
     * @param \XLite\Module\XC\Reviews\Model\Review $review    Review model object
     * @param integer                               $profileId Profile identificator
     *
     * @return void
     */
    protected function updateProfile(\XLite\Module\XC\Reviews\Model\Review $review, $profileId)
    {
        $profile = \XLite\Core\Database::getRepo('XLite\Model\Profile')->find($profileId);

        if ($profile) {
            $review->setProfile($profile);
            $review->setReviewerName($profile->getName());
            $review->setEmail($profile->getLogin());
        }
    }

    /**
     * Get model form class
     *
     * @return string
     */
    protected function getModelFormClass()
    {
        return 'XLite\Module\XC\Reviews\View\Model\Review';
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

    /*
     * Find product by substring
     * Function is used if product is not selected in autocomplete selector
     *
     * @param string $substring Product name or SKU
     *
     * @return \XLite\Model\Product
     */
    protected function findProductBySubstring($substring)
    {
        $cnd = new \XLite\Core\CommonCell();
        $cnd->{\XLite\Model\Repo\Product::P_SUBSTRING} = $substring;
        $cnd->{\XLite\Model\Repo\Product::P_BY_SKU} = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_BY_TITLE} = 'Y';
        $cnd->{\XLite\Model\Repo\Product::P_BY_DESCR} = null;

        $products = \XLite\Core\Database::getRepo('\XLite\Model\Product')->search($cnd);

        return $products ? $products[0] : null;
    }
}
