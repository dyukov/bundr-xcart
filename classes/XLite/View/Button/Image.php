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

namespace XLite\View\Button;


/**
 * Image-based button
 */
class Image extends \XLite\View\Button\Regular
{
    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'button/image.tpl';
    }

    /**
     * Get attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $list = parent::getAttributes();
        return array_merge($list, $this->getImageAttributes());
    }

    /**
     * Defines the specific image attributes
     *
     * @return array
     */
    protected function getImageAttributes()
    {
        $list = array();
        $list['type'] = 'image';
        $list['src'] = \XLite\Core\Layout::getInstance()->getResourceWebPath(
            'images/spacer.gif',
            \XLite\Core\Layout::WEB_PATH_OUTPUT_URL
        );
        $list['value'] = static::t($this->getButtonLabel());
        if (!isset($list['title'])) {
            $list['title'] = $list['value'];
        }
        $list['alt'] = $list['value'];

        return $list;
    }

    /**
     * JavaScript: default JS code to execute
     *
     * @return string
     */
    protected function getDefaultJSCode()
    {
        return parent::getDefaultJSCode() . ' return false;';
    }
}
