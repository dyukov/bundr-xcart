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

namespace XLite\Module\XC\Reviews\View;

/**
 * Average product rating widget
 *
 */
class AverageRating extends \XLite\View\AView
{
    /**
     * Widget params names
     */
    const PARAM_PRODUCT = 'product';

    /**
     * Ratings distortion
     *
     * @var array
     */
    protected $ratings;

    /**
     * Average product rating
     *
     * @var integer
     */
    protected $averageRating;

    /**
     * Reviews count for product
     *
     * @var integer
     */
    protected $reviewsCount;

    /**
     * Votes count for product
     *
     * @var integer
     */
    protected $votesCount;

    /**
     * Maximum available product rating
     *
     * @var integer
     */
    protected $maxRatingValue;

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/XC/Reviews/average_rating/style.css';
        $list[] = 'modules/XC/Reviews/vote_bar/vote_bar.css';
        $list[] = 'modules/XC/Reviews/form_field/input/rating/rating.css';

        return $list;
    }

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/XC/Reviews/average_rating/rating.js';

        return $list;
    }

    /**
     * Return TRUE if customer can rate product
     *
     * @return boolean
     */
    public function isAllowedRateProduct()
    {
        $result = true;

        $whoCanLeaveFeedback = \XLite\Core\Config::getInstance()->XC->Reviews->whoCanLeaveFeedback;

        $auth = \XLite\Core\Auth::getInstance();

        if (\XLite\Module\XC\Reviews\Model\Review::REGISTERED_CUSTOMERS == $whoCanLeaveFeedback) {
            $result = ($auth->getProfile()) ? true : false;
        }

        if ($result && $this->isProductRatedByUser()) {
            $result = false;
        }

        if ($result && $this->isPurchasedCustomerOnlyAbleLeaveFeedback() && $auth->getProfile() && !$this->isUserPurchasedProduct()) {
            $result = false;
        }

        return $result;
    }

    /**
     * Return product ID
     *
     * @return integer
     */
    public function getRatedProductId()
    {
        $product = $this->getRatedProduct();

        return $product ? $product->getProductId() : null;
    }

    /**
     * Return TRUE if customer already rated product
     *
     * @return boolean
     */
    public function isProductRatedByUser()
    {
        return $this->getRatedProduct()->isRatedByUser(\XLite\Core\Auth::getInstance()->getProfile());
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
            ->countItemsPurchasedByCustomer($this->getRatedProductId(), \XLite\Core\Auth::getInstance()->getProfile());
    }

    /**
     * Define if the current page is detailed product page
     *
     * @return boolean
     */
    public function isDetailedProductInfo()
    {
        return ('product' == \XLite\Core\Request::getInstance()->target);
    }

    /**
     * Return ratings distortion
     *
     * @return array
     */
    public function getRatings()
    {
        if (!isset($this->ratings)) {
            $this->ratings = $this->getRatedProduct()->getRatings();
        }

        return $this->ratings;
    }

    /**
     * Return average rating for the current product
     *
     * @return integer
     */
    public function getAverageRating()
    {
        if (!isset($this->averageRating)) {
            $this->averageRating = $this->getRatedProduct()->getAverageRating();
        }

        return $this->averageRating;
    }

    /**
     * Return reviews count for the current product
     *
     * @return integer
     */
    public function getReviewsCount()
    {
        if (!isset($this->reviewsCount)) {
            $this->reviewsCount = $this->getRatedProduct()->getReviewsCount();
        }

        return $this->reviewsCount;
    }

    /**
     * Return votes count for the current product
     *
     * @return integer
     */
    public function getVotesCount()
    {
        if (!isset($this->votesCount)) {
            $this->votesCount = $this->getRatedProduct()->getVotesCount();
        }

        return $this->votesCount;
    }

    /**
     * Return max available rating value
     *
     * @return integer
     */
    public function getMaxRatingValue()
    {
        if (!isset($this->maxRatingValue)) {
            $this->maxRatingValue = $this->getRatedProduct()->getMaxRatingValue();
        }

        return $this->maxRatingValue;
    }

    /**
     * Define whether product was rated somewhere or not
     *
     * @return boolean
     */
    public function isVisibleAverageRating()
    {
        return 0 < $this->getAverageRating();
    }

    /**
     * Define whether to display the rating on the page
     *
     * @return boolean
     */
    public function isVisibleAverageRatingOnPage()
    {
       return true;
    }

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_PRODUCT => new \XLite\Model\WidgetParam\Object('Product', null, false, 'XLite\Model\Product'),
        );
    }

    /**
     * Return title
     *
     * @return string
     */
    protected function getHead()
    {
        return null;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'modules/XC/Reviews';
    }

    /**
     * Return default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/Reviews/average_rating/rating.tpl';
    }

    /**
     * Return product
     *
     * @return \XLite\Model\Product
     */
    protected function getRatedProduct()
    {
        return $this->getParam(static::PARAM_PRODUCT) ? :
            \XLite\Core\Database::getRepo('XLite\Model\Product')->find(\XLite\Core\Request::getInstance()->product_id);
    }
}
