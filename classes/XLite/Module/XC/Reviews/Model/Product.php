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

namespace XLite\Module\XC\Reviews\Model;

/**
 * Product
 *
 */
class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Product reviews
     *
     * @var \Doctrine\Common\Collections\Collection
     *
     * @OneToMany (targetEntity="XLite\Module\XC\Reviews\Model\Review", mappedBy="product", cascade={"all"})
     * @OrderBy   ({"additionDate" = "DESC"})
     */
    protected $reviews;

    /**
     * Return count of votes
     *
     * @return integer
     */
    public function getVotesCount()
    {
        $cnd = $this->getConditions();
        $countOnly = true;

        return \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')->search($cnd, $countOnly);
    }

    /**
     * Return product reviews count
     *
     * @return integer
     */
    public function getReviewsCount()
    {
        $cnd = $this->getConditions();
        $cnd->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_TYPE}
            = \XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_TYPE_REVIEWS_ONLY;
        $countOnly = true;

        return \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')->search($cnd, $countOnly);
    }

    /**
     * Return product average rating
     *
     * @return float
     */
    public function getAverageRating()
    {
        $cnd = $this->getConditions();

        $totalRating = \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')->searchTotalRating($cnd);

        $votesCount = $this->getVotesCount();

        $averageRating = (0 < $votesCount)
            ? number_format($totalRating / $votesCount, 2)
            : 0;

        return $averageRating;
    }

    /**
     * Return ratings distortion
     *
     * @return array
     */
    public function getRatings()
    {
        $cnd = $this->getConditions();

        $maxRating = $this->getMaxRatingValue();
        $countOnly = true;

        $totalCount = $this->getVotesCount();

        $result = array();

        if (0 < $totalCount) {
            for ($rating = $maxRating; 0 < $rating; $rating--) {
                $cnd->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_RATING} = $rating;
                $count
                    = \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')->search($cnd, $countOnly);

                $percent = ceil(100 * $count / $totalCount);
                $result[] = array(
                    'count'                 => $count,
                    'percent'               => $percent,
                    'rating'                => $rating,
                    'showPercentLastDiv'    => (98 > $percent),
                );
            }
        }

        return $result;
    }

    /**
     * Define whether product was rated somewhere or not
     *
     * @return boolean
     */
    public function isEmptyAverageRating()
    {
        return 0 >= $this->getAverageRating();
    }

    /**
     * Return maximum allowable rating value
     *
     * @return integer
     */
    public function getMaxRatingValue()
    {
        return \XLite\Module\XC\Reviews\Model\Review::MAX_RATING;
    }

    /**
     * Return review added by customer
     *
     * @param \XLite\Model\Profile $profile Profile
     *
     * @return \XLite\Module\XC\Reviews\Model\Review
     */
    public function getReviewAddedByUser(\XLite\Model\Profile $profile = null, $findByIp = true)
    {
        $review = null;

        $data = array(
            'product' => $this,
        );

        if ($profile) {
            // Find by profile of logged in user
            $data['profile'] = $profile;
            $review = \XLite\Core\Database::getRepo('XLite\Module\XC\Reviews\Model\Review')->findOneBy($data);

        } else {
            // Find by review ids added by anonymous customer
            $reviewIds = \XLite\Core\Session::getInstance()->reviewIds;
            if (is_array($reviewIds) && !empty($reviewIds)) {
                $reviews = \XLite\Core\Database::getRepo('\XLite\Module\XC\Reviews\Model\Review')
                    ->findByIds($reviewIds);
                foreach ($reviews as $tempReview) {
                    if ($tempReview->getProduct()->getProductId() == $this->getProductId()) {
                        $review = $tempReview;
                        break;
                    }
                }
            }
        }

        // If any customer has added review during getTtlLimitForReviewFromIp() time then
        // other customers cannot add reviews
        if (!$review && $findByIp) {
            $data = array(
                'product' => $this,
                'ip' => ip2long($_SERVER['REMOTE_ADDR']),
            );
            $reviews = \XLite\Core\Database::getRepo('XLite\Module\XC\Reviews\Model\Review')->findBy($data);

            foreach ($reviews as $tempReview) {
                if ($tempReview->getAdditionDate() >= (LC_START_TIME - $this->getTtlLimitForReviewFromIp())) {
                    $review = $tempReview;
                    break;
                }
            }
        }

        return $review;
    }

    /**
     * Return TRUE if customer already rated product
     *
     * @param \XLite\Model\Profile $profile Profile
     *
     * @return boolean
     */
    public function isRatedByUser(\XLite\Model\Profile $profile = null)
    {
        $review = $this->getReviewAddedByUser($profile);

        return (null != $review);
    }

    /**
     * Return TRUE if customer already addded review for the product
     *
     * @return boolean
     */
    public function isReviewedByUser(\XLite\Model\Profile $profile = null)
    {
        $review = $this->getReviewAddedByUser($profile);

        return (null != $review && $review->getReview());
    }

    /**
     * Get time to live for limiting adding reviews from one ip for certain product
     *
     * @return integer
     */
    protected function getTtlLimitForReviewFromIp()
    {
        return \XLite\Module\XC\Reviews\Model\Review::TTL_LIMIT_FOR_REVIEW_FROM_IP;
    }

    /**
     * Get conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cnd = new \XLite\Core\CommonCell();

        $cnd->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_PRODUCT} = $this;

        if (\XLite\Core\Config::getInstance()->XC->Reviews->disablePendingReviews == true) {
            $cnd->{\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_STATUS}
                = \XLite\Module\XC\Reviews\Model\Review::STATUS_APPROVED;
        }

        return $cnd;
    }
}
