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

namespace XLite\View\Product\Details\Customer;

/**
 * Gallery
 *
 * @ListChild (list="product.details.page.image", weight="20")
 * @ListChild (list="product.details.quicklook.image", weight="20")
 */
class Gallery extends \XLite\View\Product\Details\Customer\ACustomer
{
    /**
     * Quicklook list name
     */
    const QUICKLOOK_PAGE = 'product.details.quicklook.image';

    /**
     * Width and height values of the quicklook images
     */
    const QUICKLOOK_IMAGE_WIDTH  = 300;
    const QUICKLOOK_IMAGE_HEIGHT = 300;

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();
        $list['js'][] = 'js/jquery.colorbox-min.js';
        $list['css'][] = 'css/colorbox.css';

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/parts/gallery.css';

        return $list;
    }

    /**
     * Return the max image width depending on whether it is a quicklook popup, or not
     *
     * @return integer
     */
    protected function getWidgetMaxWidth()
    {
        return static::QUICKLOOK_PAGE == $this->viewListName
            ? static::QUICKLOOK_IMAGE_WIDTH
            : \XLite\Core\Config::getInstance()->General->product_page_image_width;
    }

    /**
     * Get product image container max height
     *
     * @return boolean
     */
    protected function getWidgetMaxHeight()
    {
        return static::QUICKLOOK_PAGE == $this->viewListName
            ? static::QUICKLOOK_IMAGE_HEIGHT
            : \XLite\Core\Config::getInstance()->General->product_page_image_height;
    }

    /**
     * Get image alternative text
     *
     * @param \XLite\Model\Base\Image $image Image
     * @param integer                 $i     Image index
     *
     * @return string
     */
    public function getAlt(\XLite\Model\Base\Image $image, $i)
    {
        return $image->getAlt() ?: \XLite\Core\Translation::lbl('Image X', array('index' => $i));
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/parts/gallery.tpl';
    }

    /**
     * Get LightBox library images directory
     *
     * @return string
     */
    protected function getLightBoxImagesDir()
    {
        return \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'images/lightbox',
            \XLite\Core\Layout::WEB_PATH_OUTPUT_URL
        );
    }

    /**
     * Check visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && ($this->getProduct()->countImages() > 0);
    }

    /**
     * Get list item class attribute
     *
     * @param integer $i Detailed image index
     *
     * @return string
     */
    protected function getListItemClassAttribute($i)
    {
        $class = $this->getListItemClass($i);

        return $class ? 'class="' . $class . '"' : '';
    }

    /**
     * Get list item class name
     *
     * @param integer $i Detailed image index
     *
     * @return string
     */
    protected function getListItemClass($i)
    {
        return 0 == $i ? 'selected' : '';
    }

    /**
     * Get image URL (middle-size)
     *
     * @param \XLite\Model\Base\Image $image  Image
     * @param integer                 $width  Width limit OPTIONAL
     * @param integer                 $height Height limit OPTIONAL
     *
     * @return string
     */
    protected function getMiddleImageURL(\XLite\Model\Base\Image $image, $width = null, $height = null)
    {
        $result = $image->getResizedURL($width, $height);

        return $result[2];
    }
}
