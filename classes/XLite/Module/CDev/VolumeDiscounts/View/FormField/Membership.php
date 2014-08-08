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

namespace XLite\Module\CDev\VolumeDiscounts\View\FormField;

/**
 * Membership form field
 */
class Membership extends \XLite\View\FormField\Inline\Base\Single
{
    /**
     * Preprocess value before save
     *
     * @param mixed $value Value
     *
     * @return mixed
     */
    protected function preprocessValueBeforeSave($value)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Membership')->find(parent::preprocessValueBeforeSave($value));
    }


    /**
     * Get membership name 
     * 
     * @return string
     */
    protected function getMembershipName()
    {
        return $this->getMembership()
            ? $this->getMembership()->getName()
            : null;
    }

    /**
     * Define field class 
     * 
     * @return string
     */
    protected function defineFieldClass()
    {
        return 'XLite\Module\CDev\VolumeDiscounts\View\FormField\SelectMembership';
    }

    /**
     * Return field template
     *
     * @return string
     */
    protected function getViewTemplate()
    {
        return 'modules/CDev/VolumeDiscounts/form_field/membership_view.tpl';
    }

    /**
     * Get entity value
     *
     * @return mixed
     */
    protected function getEntityValue()
    {
        $value = parent::getEntityValue();

        return $value ? $value->getMembershipId() : null;
    }

    /**
     * Get membership 
     * 
     * @return \XLite\Model\Membership
     */
    protected function getMembership()
    {
        return $this->getEntity()->getMembership();
    }
}

