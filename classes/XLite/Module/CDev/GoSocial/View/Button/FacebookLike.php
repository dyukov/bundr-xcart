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

namespace XLite\Module\CDev\GoSocial\View\Button;

/**
 * Facebook Like button
 *
 * @ListChild (list="buttons.share", weight="100")
 */
class FacebookLike extends \XLite\View\AView
{
    /**
     * Widget parameters
     */
    const PARAM_WIDTH  = 'width';

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/CDev/GoSocial/button/js/facebook_like.js';

        return $list;
    }

    /**
     * Get width
     *
     * @return integer
     */
    protected function getWidth()
    {
        return $this->getParam(self::PARAM_WIDTH);
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoSocial/button/facebook_like.tpl';
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
            self::PARAM_WIDTH => new \XLite\Model\WidgetParam\Int('Width', $this->getDefaultWidth()),
        );
    }

    /**
     * Get defaul width
     *
     * @return integer
     */
    protected function getDefaultWidth()
    {
        switch (\XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_layout) {
            case 'button_count':
                $width = 90;
                break;

            case 'box_count':
                $width = 55;
                break;

            case 'standard':
                $width = 450;
                break;

            default:
                $width = 0;
        }

        return $width;
    }

    /**
     * Get button attributes
     *
     * @return array
     */
    protected function getButtonAttributes()
    {
        $attributes = array(
            'send'        => \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_send_button ? 'true' : 'false',
            'layout'      => \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_layout,
            'colorscheme' => \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_colorscheme,
            'show-faces'  => \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_show_faces ? 'true' : 'false',
            'action'      => \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_verb,
        );

        if (0 < $this->getWidth()) {
            $attributes['width'] = $this->getWidth();
        }

        return $attributes;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_like_use
            && \XLite\Core\Config::getInstance()->CDev->GoSocial->fb_app_id;
    }
}
