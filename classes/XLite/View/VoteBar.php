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
 * Vote bar widget
 */
class VoteBar extends \XLite\View\AView
{
    /**
     * Widget param names
     */
    const PARAM_RATE        = 'rate';
    const PARAM_MAX         = 'max';
    const PARAM_IS_EDITABLE = 'is_editable';
    const PARAM_FIELD_NAME  = 'field_name';

    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'vote_bar/vote_bar.css';

        return $list;
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'vote_bar/vote_bar.tpl';
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
            self::PARAM_RATE        => new \XLite\Model\WidgetParam\Float('', 0),
            self::PARAM_MAX         => new \XLite\Model\WidgetParam\Int('', 5),
            self::PARAM_IS_EDITABLE => new \XLite\Model\WidgetParam\Bool('', false),
            self::PARAM_FIELD_NAME  => new \XLite\Model\WidgetParam\String('', 'rating'),
        );
    }

    /**
     * Get field name
     *
     * @return string
     */
    protected function getFieldName()
    {
        return $this->getParam(self::PARAM_FIELD_NAME);
    }

    /**
     * Get rating
     *
     * @return float
     */
    protected function getRating()
    {
        return $this->getParam(self::PARAM_RATE);
    }

    /**
     * Get percent
     *
     * @return integer
     */
    protected function getPercent()
    {
        return intval($this->getParam(self::PARAM_RATE) * 100 / $this->getParam(self::PARAM_MAX));
    }

    /**
     * Get number or stars
     *
     * @return integer
     */
    protected function getStarsCount()
    {
        return range(1, $this->getParam(self::PARAM_MAX), 1);
    }

    /**
     * Get number or stars
     *
     * @return integer
     */
    protected function isEditable()
    {
        return $this->getParam(self::PARAM_IS_EDITABLE);
    }
}
