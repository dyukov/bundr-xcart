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

namespace XLite\View\FormField\Textarea;

/**
 * Abstract class for textarea widget
 */
abstract class ATextarea extends \XLite\View\FormField\Input\AInput
{
    /**
     *  Number of rows in textarea widget (HTML attribute)
     */
    const PARAM_ROWS = 'rows';

    /**
     *  Number of columns in textarea widget (HTML attribute)
     */
    const PARAM_COLS = 'cols';


    /**
     * Return field type
     *
     * @return string
     */
    public function getFieldType()
    {
        return self::FIELD_TYPE_TEXTAREA;
    }

    /**
     * Rows getter
     *
     * @return integer
     */
    public function getRows()
    {
        return $this->getParam(static::PARAM_ROWS);
    }

    /**
     * Columns getter
     *
     * @return integer
     */
    public function getCols()
    {
        return $this->getParam(static::PARAM_COLS);
    }

    /**
     * Return default value of 'rows' HTML attribute.
     *
     * @return integer
     */
    protected function getDefaultRows()
    {
        return 10;
    }

    /**
     * Return default value of 'cols' HTML attribute.
     *
     * @return integer
     */
    protected function getDefaultCols()
    {
        return 50;
    }

    /**
     * getCommonAttributes
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        return parent::getCommonAttributes() + array(
            static::PARAM_ROWS => $this->getRows(),
            static::PARAM_COLS => $this->getCols(),
        );
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
            static::PARAM_ROWS => new \XLite\Model\WidgetParam\Int('Rows', $this->getDefaultRows()),
            static::PARAM_COLS => new \XLite\Model\WidgetParam\Int('Cols', $this->getDefaultCols()),
        );
    }
}
