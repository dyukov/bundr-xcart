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
 * Image
 */
class Image extends \XLite\View\AView
{
    /**
     * Widget arguments names
     */
    const PARAM_IMAGE             = 'image';
    const PARAM_ALT               = 'alt';
    const PARAM_MAX_WIDTH         = 'maxWidth';
    const PARAM_MAX_HEIGHT        = 'maxHeight';
    const PARAM_CENTER_IMAGE      = 'centerImage';
    const PARAM_VERTICAL_ALIGN    = 'verticalAlign';
    const PARAM_USE_CACHE         = 'useCache';
    const PARAM_USE_DEFAULT_IMAGE = 'useDefaultImage';


    /**
     * Vertical align types
     */
    const VERTICAL_ALIGN_TOP    = 'top';
    const VERTICAL_ALIGN_MIDDLE = 'middle';
    const VERTICAL_ALIGN_BOTTOM = 'bottom';

    /**
     * Default image (no image) dimensions
     */
    const DEFAULT_IMAGE_WIDTH  = 300;
    const DEFAULT_IMAGE_HEIGHT = 300;

    /**
     * Allowed properties names
     *
     * @var array
     */
    protected $allowedProperties = array(
        'className'   => 'class',
        'id'          => 'id',
        'onclick'     => 'onclick',
        'style'       => 'style',
        'onmousemove' => 'onmousemove',
        'onmouseup'   => 'onmouseup',
        'onmousedown' => 'onmousedown',
        'onmouseover' => 'onmouseover',
        'onmouseout'  => 'onmouseout',
    );

    /**
     * Additioanl properties
     *
     * @var array
     */
    protected $properties = array();

    /**
     * Resized thumbnail URL
     *
     * @var string
     */
    protected $resizedURL = null;


    /**
     * Set widget parameters
     *
     * @param array $params Widget parameters
     *
     * @return void
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        // Save additional parameters
        foreach ($params as $name => $value) {
            if (isset($this->allowedProperties[$name])) {
                $this->properties[$this->allowedProperties[$name]] = $value;
            }
        }
    }

    /**
     * Get image URL
     *
     * @return string
     */
    public function getURL()
    {
        $url = null;

        if ($this->getParam(self::PARAM_IMAGE) && $this->getParam(self::PARAM_IMAGE)->isExists()) {
            // Specified image
            $url = $this->getParam(self::PARAM_USE_CACHE)
                ? $this->resizedURL
                : $this->getParam(self::PARAM_IMAGE)->getFrontURL();
        }

        if (!$url && $this->getParam(self::PARAM_USE_DEFAULT_IMAGE)) {
            // Default image
            $url = \XLite::getInstance()->getOptions(array('images', 'default_image'));

            if (!\XLite\Core\Converter::isURL($url)) {
                $url = \XLite\Core\Layout::getInstance()->getResourceWebPath(
                    $url,
                    \XLite\Core\Layout::WEB_PATH_OUTPUT_URL
                );
            }
        }

        return $this->prepareURL($url);
    }

    /**
     * Get image alternative text
     *
     * @return void
     */
    public function getAlt()
    {
        return $this->getParam(self::PARAM_ALT);
    }

    /**
     * Get properties
     *
     * @return void
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Remove the protocol from the url definition
     *
     * @param string $url
     *
     * @return string
     */
    protected function prepareURL($url)
    {
        return str_replace(array('http://', 'https://'), '//', $url);
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'common/image.tpl';
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
            self::PARAM_IMAGE             => new \XLite\Model\WidgetParam\Object('Image', null, false, '\XLite\Model\Base\Image'),
            self::PARAM_ALT               => new \XLite\Model\WidgetParam\String('Alt. text', '', false),
            self::PARAM_MAX_WIDTH         => new \XLite\Model\WidgetParam\Int('Max. width', 0),
            self::PARAM_MAX_HEIGHT        => new \XLite\Model\WidgetParam\Int('Max. height', 0),
            self::PARAM_CENTER_IMAGE      => new \XLite\Model\WidgetParam\Checkbox('Center the image after resizing', true),
            self::PARAM_VERTICAL_ALIGN    => new \XLite\Model\WidgetParam\String('Vertical align', self::VERTICAL_ALIGN_MIDDLE),
            self::PARAM_USE_CACHE         => new \XLite\Model\WidgetParam\Bool('Use cache', 1),
            self::PARAM_USE_DEFAULT_IMAGE => new \XLite\Model\WidgetParam\Bool('Use default image', 1),
        );
    }

    /**
     * checkImage
     *
     * @return boolean
     */
    protected function checkImage()
    {
        return $this->getParam(self::PARAM_IMAGE)
            && $this->getParam(self::PARAM_IMAGE)->isExists();
    }

    /**
     * checkDefaultImage
     *
     * @return boolean
     */
    protected function checkDefaultImage()
    {
        return $this->getParam(self::PARAM_USE_DEFAULT_IMAGE)
            && \XLite::getInstance()->getOptions(array('images', 'default_image'));
    }

    /**
     * Check widget visibility
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $result = parent::isVisible();

        if ($result) {

            if ($this->checkImage()) {
                $this->processImage();

            } elseif ($this->checkDefaultImage()) {
                $this->processDefaultImage();

            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Return a CSS style centering the image vertically and horizontally
     *
     * @return string
     */
    protected function setImageMargin()
    {
        $vertical = ($this->getParam(self::PARAM_MAX_HEIGHT) - $this->properties['height']) / 2;

        switch ($this->getParam(self::PARAM_VERTICAL_ALIGN)) {
            case self::VERTICAL_ALIGN_TOP:
                $top    = 0;
                $bottom = 0;
                break;

            case self::VERTICAL_ALIGN_BOTTOM:
                $top    = $this->getParam(self::PARAM_MAX_HEIGHT) - $this->properties['height'];
                $bottom = 0;
                break;

            default:
                $top    = max(0, ceil($vertical));
                $bottom = max(0, floor($vertical));
        }

        if (0 < $top || 0 < $bottom) {
            $this->addInlineStyle('margin: 0 auto;margin-bottom:' . $bottom . 'px;' . 'margin-top:' . $top . 'px;');
        }
    }

    /**
     * Add CSS styles to the value of "style" attribute of the image tag
     *
     * @param string $style CSS styles to be added to the end of "style" attribute
     *
     * @return void
     */
    protected function addInlineStyle($style)
    {
        if (!isset($this->properties['style'])) {
            $this->properties['style'] = $style;

        } else {
            $this->properties['style'] .= ' ' . $style;
        }
    }

    /**
     * Preprocess image
     * TODO: replace getResizedThumbnailURL to getResizedURL
     *
     * @return void
     */
    protected function processImage()
    {
        $maxw = max(0, $this->getParam(self::PARAM_MAX_WIDTH));
        $maxh = max(0, $this->getParam(self::PARAM_MAX_HEIGHT));

        $funcName = method_exists($this->getParam(self::PARAM_IMAGE), 'getResizedURL')
            ? 'getResizedURL'
            : 'getResizedThumbnailURL';

        // $funcName - getResizedURL or getResizedThumbnailURL
        list(
            $this->properties['width'],
            $this->properties['height'],
            $this->resizedURL
        ) = $this->getParam(self::PARAM_IMAGE)->$funcName($maxw, $maxh);

        // Center the image vertically and horizontally
        if ($this->getParam(self::PARAM_CENTER_IMAGE)) {
            $this->setImageMargin();
        }
    }

    /**
     * Preprocess default image
     *
     * @return void
     */
    protected function processDefaultImage()
    {
        list($this->properties['width'], $this->properties['height']) = \XLite\Core\ImageOperator::getCroppedDimensions(
            static::DEFAULT_IMAGE_WIDTH,
            static::DEFAULT_IMAGE_HEIGHT,
            max(0, $this->getParam(self::PARAM_MAX_WIDTH)),
            max(0, $this->getParam(self::PARAM_MAX_HEIGHT))
        );

        // Center the image vertically and horizontally
        if ($this->getParam(self::PARAM_CENTER_IMAGE)) {
            $this->setImageMargin();
        }
    }
}
