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

namespace XLite\View\ModulesManager;

/**
 * Banner
 */
class Banner extends \XLite\View\ModulesManager\AModulesManager
{
    /**
     * Widget CSS files
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
     * Return templates dir
     *
     * @return string
     */
    protected function getDir()
    {
        return parent::getDir() . '/banner';
    }

    /**
     * Retrive all banners collection.
     * The first one banner is considered
     *
     * @return array
     */
    protected function retriveBannerCollection()
    {
        return \XLite\Core\Marketplace::getInstance()->getAllBanners();
    }

    /**
     * Return banner URL
     *
     * @return array
     */
    protected function getMainBanner()
    {
        $banners = $this->retriveBannerCollection();

        return $banners[0];
    }

    /**
     * Retrive the banners collection
     *
     * @return array
     */
    protected function getBannersCollection()
    {
        $banners = $this->retriveBannerCollection();
        unset($banners[0]);

        return $banners;
    }

    /**
     * Retrive banner specific URL
     *
     * @param array $banner
     *
     * @return string
     */
    protected function getBannerURL($banner)
    {
        list($author, $module) = explode('-', $banner['banner_module']);
        list($start, $limit) = $this->getWidget(array(), '\XLite\View\Pager\Admin\Module\Install')->getLimitCondition()->limit;

        $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getPageId($author, $module, $limit);

        return $this->buildURL(
            'addons_list_marketplace',
            '',
            array(
                'clearCnd'      => 1,
                'clearSearch'   => 1,
                'pageId'        => $pageId,
                \XLite\View\ItemsList\AItemsList::PARAM_SORT_BY => \XLite\View\ItemsList\Module\AModule::SORT_OPT_ALPHA
            )) . '#' . $module;
    }

    /**
     * Retrive banner image
     *
     * @param array $banner
     *
     * @return string
     */
    protected function getBannerImg($banner)
    {
        return $banner['banner_img'];
    }

    /**
     * Retrive the main banner URL
     *
     * @return string
     */
    protected function getMainBannerURL()
    {
        return $this->getBannerURL($this->getMainBanner());
    }

    /**
     * Retrive the main banner image
     *
     * @return string
     */
    protected function getMainBannerImg()
    {
        return $this->getBannerImg($this->getMainBanner());
    }

    /**
     * Defines the search string for the landing page
     *
     * @return string
     */
    protected function getSubstring()
    {
        return '';
    }

    /**
     * Defines the tags data structure
     *
     * @return array
     */
    protected function getTagsData()
    {
        $result = array();

        foreach (\XLite\Core\Marketplace::getInstance()->getAllTags() as $tag) {
            $result[$tag] = $this->buildURL(
                'addons_list_marketplace',
                '',
                array(
                    'tag'         => $tag,
                    'clearPager'  => '1',
                    'clearSearch' => 1,
                )
            );
        }

        return $result;
    }
}
