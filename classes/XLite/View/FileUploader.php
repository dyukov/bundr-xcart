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
 * File uploader
 */
class FileUploader extends \XLite\View\AView
{
    /**
     * Widget param names
     */
    const PARAM_OBJECT       = 'object';
    const PARAM_OBJECT_ID    = 'objectId';
    const PARAM_MESSAGE      = 'message';
    const PARAM_MAX_WIDTH    = 'maxWidth';
    const PARAM_MAX_HEIGHT   = 'maxHeight';
    const PARAM_IS_IMAGE     = 'isImage';
    const PARAM_IS_TEMPORARY = 'isTemporary';
    const PARAM_NAME         = 'fieldName';
    const PARAM_MULTIPLE     = 'multiple';
    const PARAM_POSITION     = 'position';

    /**
     * Get a list of JS files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/controller.js';

        return $list;
    }

    /**
     * Get a list of CSS files required to display the widget properly
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
     * Return field value
     *
     * @return mixed
     */
    protected function getObject()
    {
        return $this->getParam(static::PARAM_OBJECT);
    }

    /**
     * Return field value
     *
     * @return mixed
     */
    protected function getObjectId()
    {
        $result = intval($this->getParam(static::PARAM_OBJECT_ID));
        if (
            !$result
            && !$this->isTemporary()
            && $this->hasFile()
        ) {
            $result = $this->getObject()->getId();
        }

        return $result;
    }

    /**
     * Return message
     *
     * @return string
     */
    protected function getMessage()
    {
        return $this->getParam(static::PARAM_MESSAGE);
    }

    /**
     * Checking widget is multiple or not
     *
     * @return boolean
     */
    protected function isMultiple()
    {
        return $this->getParam(static::PARAM_MULTIPLE);
    }

    /**
     * Return position
     *
     * @return integer
     */
    protected function getPosition()
    {
        return $this->getParam(static::PARAM_POSITION) || !$this->hasFile()
            ? $this->getParam(static::PARAM_POSITION)
            : $this->getObject()->getOrderby();
    }

    /**
     * Return max width
     *
     * @return integer
     */
    protected function getMaxWidth()
    {
        return $this->getParam(static::PARAM_MAX_WIDTH);
    }

    /**
     * Return max height
     *
     * @return integer
     */
    protected function getMaxHeight()
    {
        return $this->getParam(static::PARAM_MAX_HEIGHT);
    }

    /**
     * Return field name
     *
     * @return string
     */
    protected function getName()
    {
        $name = $this->getParam(static::PARAM_NAME);
        if ($this->getParam(static::PARAM_MULTIPLE)) {
            $index = $this->getParam(static::PARAM_OBJECT_ID);
            if (!$index) {
                if ($this->getObject()) {
                    $index = $this->getObject()->getId();
                    if($this->getParam(static::PARAM_IS_TEMPORARY)) {
                        $index = '-' .$index;
                    }
                }
            }
            $name .= '[' . $index . ']';
        }

        return $name;
    }

    /**
     * Return preview
     *
     * @return string
     */
    protected function getPreview()
    {
        if ($this->getMessage()) {
            $result = '<i class="icon fa warning fa-exclamation-triangle"></i>';

        } else if ($this->isImage() && $this->hasFile()) {
            $viewer = new \XLite\View\Image(
                array(
                    'image'       => $this->getObject(),
                    'maxWidth'    => $this->getParam(static::PARAM_MAX_WIDTH),
                    'maxHeight'   => $this->getParam(static::PARAM_MAX_HEIGHT),
                    'alt'         => '',
                    'centerImage' => true
                )
            );

            $result = '<div class="preview">'
                . $viewer->getContent()
                . '</div>';

        } elseif ($this->isImage()) {
            $result = '<i class="icon fa fa-camera"></i>';
        }

        return $result;
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
            static::PARAM_NAME         => new \XLite\Model\WidgetParam\String('Name', 'file'),
            static::PARAM_OBJECT       => new \XLite\Model\WidgetParam\Object('Object', null),
            static::PARAM_OBJECT_ID    => new \XLite\Model\WidgetParam\Int('Object Id', 0),
            static::PARAM_MESSAGE      => new \XLite\Model\WidgetParam\String('Message', ''),
            static::PARAM_MAX_WIDTH    => new \XLite\Model\WidgetParam\Int('Max. width', 120),
            static::PARAM_MAX_HEIGHT   => new \XLite\Model\WidgetParam\Int('Max. height', 120),
            static::PARAM_IS_IMAGE     => new \XLite\Model\WidgetParam\Bool('Is image', false),
            static::PARAM_IS_TEMPORARY => new \XLite\Model\WidgetParam\Bool('Is temporary', false),
            static::PARAM_MULTIPLE     => new \XLite\Model\WidgetParam\Bool('Multiple', false),
            static::PARAM_POSITION     => new \XLite\Model\WidgetParam\Int('Position', 0),
        );
    }

    /**
     * Check widget has file or not
     *
     * @return boolean
     */
    protected function hasFile()
    {
        $object = $this->getObject();

        return $object && $object->getId();
    }

    /**
     * Check object is image or not
     *
     * @return boolean
     */
    protected function isImage()
    {
        $object = $this->getObject();

        return $this->getParam(static::PARAM_IS_IMAGE)
            || ($object && $object->isImage());
    }

    /**
     * Check object is temporary or not
     *
     * @return boolean
     */
    protected function isTemporary()
    {
        return $this->hasFile() && $this->getParam(static::PARAM_IS_TEMPORARY);
    }

    /**
     * Check widget has multiple selector or not
     *
     * @return boolean
     */
    protected function hasMultipleSelector()
    {
        return $this->isMultiple() && !$this->hasFile() && !$this->getMessage();
    }

    /**
     * Return link
     *
     * @return string
     */
    protected function getLink()
    {
        return $this->hasView() ? $this->getObject()->getFrontURL() : '#';
    }

    /**
     * Check widget has view or not
     *
     * @return boolean
     */
    protected function hasView()
    {
        return !$this->getMessage()
            && $this->hasFile()
            && $this->isImage();
    }

    /**
     * Check widget has alt or not
     *
     * @return boolean
     */
    protected function hasAlt()
    {
        return $this->getParam(static::PARAM_IS_IMAGE)
            && $this->hasFile()
            && method_exists($this->getObject(), 'getAlt');
    }

    /**
     *Return icon style
     *
     * @return string
     */
    protected function getIconStyle()
    {
        $result = 'fa ';
        $result .= ($this->getMessage() || $this->hasFile()) ? 'fa-bars' : 'fa-plus';

        return $result;
    }

    /**
     *Return div style
     *
     * @return string
     */
    protected function getDivStyle()
    {
        $result = 'dropdown file-uploader';

        if ($this->isMultiple() && !$this->hasMultipleSelector()) {
            $result .= ' item';
        }

        if ($this->getMessage() || $this->hasFile()) {
            $result .= ' solid';
        }

        return $result;
    }

    /**
     * Return templates directory name
     *
     * @return string
     */
    protected function getDir()
    {
        return 'file_uploader';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }
}
