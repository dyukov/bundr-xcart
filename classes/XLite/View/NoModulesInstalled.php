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

namespace XLite\View;

/**
 * No modules installed
 */
class NoModulesInstalled extends \XLite\View\Dialog
{
    protected static $limit = null;


    /**
     * Add widget specific CSS files
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'No promotions modules installed';
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return static::t(
            'To boost your sales try to use Discounts coupons, Sale, Product Advisor, Volume discounts addons. Also you may be interested in all Marketing extensions from our Marketplace',
            $this->getDescriptionData()
        );
    }

    /**
     * Description specific data
     *
     * @return array
     */
    public function getDescriptionData()
    {
        return array(
            'discountCoupons'   => $this->getModuleURL('Coupons', 'CDev'),
            'sale'              => $this->getModuleURL('Sale', 'CDev'),
            'productAdvisor'    => $this->getModuleURL('ProductAdvisor', 'CDev'),
            'volumeDiscounts'   => $this->getModuleURL('VolumeDiscounts', 'CDev'),
            'marketingTag'      => $this->getTagURL('Marketing'),
        );
    }

    /**
     * Module URL for marketplace
     *
     * @param string $name
     * @param string $author
     *
     * @return string
     */
    protected function getModuleURL($name, $author)
    {
        if (is_null(static::$limit)) {
            list($start, static::$limit) = $this->getWidget(array(), '\XLite\View\Pager\Admin\Module\Install')->getLimitCondition()->limit;
        }
        $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getPageId($author, $name, static::$limit);
        return $this->buildURL('addons_list_marketplace', '', array('clearCnd' => '1', 'pageId' => $pageId)) . '#' . $name;
    }

    /**
     * Tag URL for marketplace
     *
     * @param string $tagName
     *
     * @return string
     */
    protected function getTagURL($tagName)
    {
        return $this->buildURL('addons_list_marketplace', '', array('tag' => $tagName));
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'no_modules_installed';
    }

}
