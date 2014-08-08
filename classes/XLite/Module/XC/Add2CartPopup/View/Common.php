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

namespace XLite\Module\XC\Add2CartPopup\View;

/**
 * Common widget extention.
 * This widget is used only to link additional css and js files to the page
 *
 * @ListChild (list="layout.main")
 */
class Common extends \XLite\View\AView
{
    /**
     * Add JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list = array_merge(
            \XLite\Module\XC\Add2CartPopup\Core\Add2CartPopup::getResourcesFiles(static::RESOURCE_JS),
            $list
        );

        $list[] = 'modules/XC/Add2CartPopup/js/add2cart_popup.js';

        return $list;
    }

    /**
     * Add CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list = array_merge(
            \XLite\Module\XC\Add2CartPopup\Core\Add2CartPopup::getResourcesFiles(static::RESOURCE_CSS),
            $list
        );

        $list[] = 'modules/XC/Add2CartPopup/css/style.css';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/Add2CartPopup/common.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && \XLite\Module\XC\Add2CartPopup\Core\Add2CartPopup::isAdd2CartPopupEnabled();
    }
}
