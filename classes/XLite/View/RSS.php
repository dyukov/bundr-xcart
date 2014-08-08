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

namespace XLite\View;

/**
 * RSS
 *
 * @ListChild (list="dashboard-sidebar", weight="400", zone="admin")
 */
class RSS extends \XLite\View\Dialog
{
    /**
     * Max count of feeds
     */
    const MAX_COUNT  = 3;

    /**
     * Feeds
     *
     * @var array
     */
    protected $feeds;

    /**
     * Add widget specific CSS file
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Cache availability
     *
     * @return boolean
     */
    protected function isCacheAvailable()
    {
        return true;
    }

    /**
     * Return widget templates directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'rss';
    }

    /**
     * Return RSS feed Url
     *
     * @return string
     */
    protected function getRSSFeedUrl()
    {
        return 'http://feeds.feedburner.com/qtmsoft';
    }

    /**
     * Return RSS Url
     *
     * @return string
     */
    protected function getRSSUrl()
    {
        return \XLite::getInstallationLng() === 'ru'
            ? 'http://www.x-cart.ru/rss_x_cart_5.xml'
            : 'http://www.x-cart.com/rss_x_cart_5.xml';
    }

    /**
     * Return Blog Url
     *
     * @return string
     */
    protected function getBlogUrl()
    {
        return \XLite::getInstallationLng() === 'ru'
            ? 'http://www.x-cart.ru/blog'
            : 'http://blog.x-cart.com';
    }

    /**
     * Prepare feeds
     *
     * @param string $url Url
     *
     * @return array
     */
    protected function prepareFeeds($url)
    {
        $feed = simplexml_load_file($url);
        $result = array();
        if ($feed && $feed->channel->item) {
            foreach ($feed->channel->item as $story){
                $result[] = array (
                    'title' => $story->title,
                    'desc'  => $story->description,
                    'link'  => $story->link,
                    'date'  => strtotime($story->pubDate),
                );

                if (static::MAX_COUNT <= count($result)) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Return feeds
     *
     * @return array
     */
    protected function getFeeds()
    {
        if (!isset($this->feeds)) {
            $this->feeds = $this->prepareFeeds(
                $this->getRSSUrl()
            );
        }

        return $this->feeds;
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getFeeds();
    }
}
