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
 * @copyright Copyright (c) 2011-2013 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 */

namespace XLite\Module\CDev\GoSocial\View\Button;

/**
 * Tweet button
 *
 * @ListChild (list="buttons.share", weight="200")
 */
class Tweet extends \XLite\View\AView
{
    /**
     * Allowed languages
     *
     * @var array
     */
    protected $languages = array('nl', 'en', 'fr', 'de', 'id', 'it', 'ja', 'ko', 'pt', 'ru', 'es', 'tr');

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoSocial/button/tweet.tpl';
    }

    /**
     * Define button attributes
     *
     * @return array
     */
    protected function defineButtonAttributes()
    {
        $url = urlencode(\XLite::getInstance()->getShopURL($this->getURL()));
        $list = array(
            'url'      => $url,
            'counturl' => $url,
        );

        if (!\XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_show_count) {
            $list['count'] = 'none';
        }

        if (\XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_via) {
            $list['via'] = \XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_via;
        }

        if ($this->getTitle()) {
            $list['text'] = $this->getTitle();
        }

        if (\XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_recommend) {
            $list['related'] = \XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_recommend;
        }

        if (\XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_hashtag) {
            $list['hashtags'] = \XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_hashtag;
        }

        $language = \XLite\Core\Session::getInstance()->getLanguage()->getCode();

        $list['lang'] = in_array($language, $this->languages) ? $language : 'en';

        return $list;
    }

    /**
     * Get button attributes hash string
     *
     * @return string
     */
    protected function getButtonAttributes()
    {
        $result = array();
        foreach ($this->defineButtonAttributes() as $name => $value) {
            $result[] = $name . '=' . $value;
        }
        
        return implode('&amp;', $result);
    }
    
    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Config::getInstance()->CDev->GoSocial->tweet_use;
    }
}
