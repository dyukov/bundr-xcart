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

namespace XLite\View\Product\Details\Admin;

/**
 * Product attributes 
 */
class Attributes extends \XLite\View\Product\Details\AAttributes
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        
        $list[] = 'product/attribute/style.css';
        $list[] = 'product/attributes/style.css';

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
        $list[] = 'product/attributes/script.js';

        return $list;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'product/attributes/body.tpl';
    }

    /**
     * Get attributes list
     *
     * @param boolean $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getAttributesList($countOnly = false)
    {
        $data = parent::getAttributesList($countOnly);

        if ($countOnly) {
            $result = $data;

        } else {
            $result = array();
            foreach ($data as $attribute) {
                $result[$attribute->getId()] = array(
                    'name' => $this->getWidget(
                        array(
                            'fieldName'   => 'name',
                            'entity'      => $attribute,
                            'fieldParams' => array('required' => true),
                        ),
                        '\XLite\View\FormField\Inline\Input\Text'
                    ),
                    'value' => $this->getWidget(
                        array(
                            'attribute' => $attribute,
                        ),
                        $attribute::getWidgetClass($attribute->getType())
                    ),
                );
            }
        }

        return $result;
    }
}
