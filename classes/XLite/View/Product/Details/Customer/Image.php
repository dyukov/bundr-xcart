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
 * Image
 *
 * @ListChild (list="product.details.page.image.photo", weight="10")
 * @ListChild (list="product.details.quicklook.image", weight="10")
 */
class Image extends \XLite\View\Product\Details\Customer\ACustomer
{
    /**
     * Widget params names
     */

    // Cloud zoom layer maximum width
    const PARAM_ZOOM_MAX_WIDTH = 'zoomMaxWidth';

    // Zoom coefficient
    const PARAM_K_ZOOM = 'kZoom';

    // Relative horizontal position of the zoom box
    const PARAM_ZOOM_ADJUST_X_PD = 'zoomAdjustXPD';
    const PARAM_ZOOM_ADJUST_X_QL = 'zoomAdjustXQL';

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
     * Product has any image to ZOOM
     *
     * @var boolean
     */
    protected $isZoom;


    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();

        $list['js'][] = 'cloud-zoom/cloud-zoom.js';
        $list['css'][] = 'cloud-zoom/cloud-zoom.css';

        return $list;
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = 'product/details/controller.js';

        return $list;
    }

    /**
     * Return a relative horizontal position of the zoom box
     * depending on whether it is a quicklook popup, or not
     *
     * @return integer
     */
    public function getZoomAdjustX()
    {
        return strpos($this->viewListName, 'quicklook')
            ? $this->getParam(self::PARAM_ZOOM_ADJUST_X_QL)
            : $this->getParam(self::PARAM_ZOOM_ADJUST_X_PD);
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/parts/image-regular.tpl';
    }

    /**
     * Return current template
     *
     * @return string
     */
    protected function getTemplate()
    {
        return ($this->hasZoomImage()) ? $this->getZoomTemplate() : $this->getDefaultTemplate();
    }

    /**
     * Zoom template for quicklook and product widgets
     *
     * @return string
     */
    protected function getZoomTemplate()
    {
        return $this->getDir() . (static::QUICKLOOK_PAGE === $this->viewListName ? '/parts/image-zoom-quicklook.tpl' : '/parts/image-zoom.tpl');
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_ZOOM_MAX_WIDTH   => new \XLite\Model\WidgetParam\Int('Cloud zoom layer maximum width, px', 460),
            self::PARAM_K_ZOOM           => new \XLite\Model\WidgetParam\Float('Minimal zoom coefficient', 1.3),
            self::PARAM_ZOOM_ADJUST_X_PD => new \XLite\Model\WidgetParam\Int('Relative horizontal position of the zoom box on the Product details page', 97),
            self::PARAM_ZOOM_ADJUST_X_QL => new \XLite\Model\WidgetParam\Int('Relative horizontal position of the zoom box in the Quick look box', 32),
        );
    }

    /**
     * Check if the product has any image to ZOOM
     *
     * @return boolean
     */
    protected function hasZoomImage()
    {
        if (!isset($this->isZoom)) {

            $this->isZoom = false;

            if ($this->getProduct()->hasImage()) {

                foreach ($this->getProduct()->getImages() as $img) {

                    if ($img->getWidth() > $this->getParam(self::PARAM_K_ZOOM) * $this->getWidgetMaxWidth()) {
                        $this->isZoom = true;
                        break;
                    }
                }
            }
        }

        return $this->isZoom;
    }

    /**
     * Get zoom image
     *
     * @return string
     */
    protected function getZoomImageURL()
    {
        return $this->getProduct()->getImage()->getURL();
    }

    /**
     * Get zoom layer width
     *
     * @return integer
     */
    protected function getZoomWidth()
    {
        return min($this->getProduct()->getImage()->getWidth(), $this->getParam(self::PARAM_ZOOM_MAX_WIDTH));
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
        $maxHeight = 0;
        if ($this->getProduct()->hasImage()) {
            foreach ($this->getProduct()->getImages() as $img) {
                $maxHeight = max(
                    $maxHeight,
                    ($img->getWidth() > $this->getWidgetMaxWidth()
                        ? $img->getHeight() * $this->getWidgetMaxWidth() / $img->getWidth()
                        : $img->getHeight())
                );
            }
        } else {
            $maxHeight = \XLite\Core\Config::getInstance()->General->product_page_image_height;
        }

        return static::QUICKLOOK_PAGE == $this->viewListName
            ? static::QUICKLOOK_IMAGE_HEIGHT
            : min(
                \XLite\Core\Config::getInstance()->General->product_page_image_height,
                $maxHeight
            );
    }

    /**
     * Return data to send to JS
     *
     * @return array
     */
    protected function getJSData()
    {
        return array(
            'kZoom' => $this->getParam(self::PARAM_K_ZOOM),
        );
    }
}
