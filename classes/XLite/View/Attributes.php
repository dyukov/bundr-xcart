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
 * Attributes page view
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Attributes extends \XLite\View\AView
{
    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), array('attributes'));
    }

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'attribute/style.css';
        $list[] = 'attributes/style.css';
        $list[] = 'form_field/inline/style.css';
        $list[] = 'form_field/inline/input/text/position/move.css';
        $list[] = 'form_field/inline/input/text/position.css';
        $list[] = 'form_field/form_field.css';
        $list[] = 'form_field/input/text/position.css';
        $list[] = 'form_field/input/checkbox/switcher.css';
        $list[] = 'items_list/items_list.css';
        $list[] = 'items_list/model/style.css';
        $list[] = 'items_list/model/table/style.css';
        $list[] = 'form_field/inline/input/text.css';

        return $list;
    }

    /**
     * Get a list of JavaScript files required to display the widget properly
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'form_field/inline/controller.js';
        $list[] = 'form_field/inline/input/text/position/move.js';
        $list[] = 'form_field/js/text.js';
        $list[] = 'form_field/input/text/integer.js';
        $list[] = 'form_field/input/checkbox/switcher.js';
        $list[] = 'button/js/remove.js';
        $list[] = 'items_list/items_list.js';
        $list[] = 'items_list/model/table/controller.js';
        $list[] = 'attributes/script.js';
        $list[] = 'form_field/inline/input/text.js';

        return $list;
    }

    /**
     * Register files from common repository
     *
     * @return array
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();
        $list[static::RESOURCE_JS][] = 'js/jquery.textarea-expander.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'attributes/body.tpl';
    }

    /**
     * Check - search box is visible or not
     *
     * @return boolean
     */
    protected function isSearchVisible()
    {
        return 0 < \XLite\Core\Database::getRepo('XLite\Model\Attribute')->count();
    }

    /**
     * Check - list box is visible or not
     *
     * @return boolean
     */
    protected function isListVisible()
    {
        return $this->getAttributesCount()
            || count($this->getAttributeGroups());
    }

}
