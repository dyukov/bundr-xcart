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

namespace XLite\View\FormField\Inline\Base;

/**
 * Single-field
 */
abstract class Single extends \XLite\View\FormField\Inline\AInline
{
    /**
     * Define form field
     *
     * @return string
     */
    abstract protected function defineFieldClass();

    /**
     * Define fields
     *
     * @return array
     */
    protected function defineFields()
    {
        return array(
            $this->getParam(static::PARAM_FIELD_NAME) => array(
                static::FIELD_NAME  => $this->getParam(static::PARAM_FIELD_NAME),
                static::FIELD_CLASS => $this->defineFieldClass(),
            ),
        );
    }

    /**
     * Get entity value
     *
     * @return mixed
     */
    protected function getEntityValue()
    {
        $method = 'get' . ucfirst($this->getParam(static::PARAM_FIELD_NAME));

        // $method assembled from 'get' + field short name
        return $this->getEntity()->$method();
    }

    /**
     * Get field value from entity
     *
     * @param array $field Field
     *
     * @return mixed
     */
    protected function getFieldEntityValue(array $field)
    {
        return $this->getEntityValue();
    }

    /**
     * Get single field 
     * 
     * @return array
     */
    protected function getSingleField()
    {
        $list = $this->getFields();

        return array_shift($list);
    }

    /**
     * Get single field as widget
     *
     * @return \XLite\View\FormField\AFormField
     */
    protected function getSingleFieldAsWidget()
    {
        $field = $this->getSingleField();

        return $field['widget'];
    }

}

