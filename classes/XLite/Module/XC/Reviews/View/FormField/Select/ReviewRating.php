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

namespace XLite\Module\XC\Reviews\View\FormField\Select;

/**
 * Rating selection widget
 *
 */
class ReviewRating extends \XLite\View\FormField
{
    /**
     * Widget parameters names
     */
    const PARAM_VALUE           = 'value';
    const PARAM_FIELD_NAME      = 'field';
    const PARAM_ALL_OPTION      = 'allOption';
    const PARAM_PENDING_OPTION  = 'pendingOption';

    /**
     * Get possible rating
     *
     * @return array
     */
    public function getRatingsList()
    {
        return array(
            5 => \XLite\Core\Translation::lbl('X stars_5', array('count' => 5)),
            4 => \XLite\Core\Translation::lbl('X stars_4', array('count' => 4)),
            3 => \XLite\Core\Translation::lbl('X stars_3', array('count' => 3)),
            2 => \XLite\Core\Translation::lbl('X stars_2', array('count' => 2)),
            1 => \XLite\Core\Translation::lbl('X star_1', array('count' => 1)),
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/XC/Reviews/select_review_rating.tpl';
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
            static::PARAM_FIELD_NAME => new \XLite\Model\WidgetParam\String('Field', 'rating', false),
            static::PARAM_VALUE      => new \XLite\Model\WidgetParam\String('Value', '%', false),
            static::PARAM_ALL_OPTION => new \XLite\Model\WidgetParam\Bool('Display All option', false, false),
        );
    }
}
