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

namespace XLite\Module\CDev\VolumeDiscounts\View\ItemsList;

/**
 * Volume discounts items list
 */
class VolumeDiscounts extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Discount keys
     *
     * @var   array
     */
    protected $discountKeys = array();

    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/CDev/VolumeDiscounts/discounts/list/style.css';

        return $list;
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'subtotalRangeBegin' => array(
                static::COLUMN_NAME => \XLite\Core\Translation::lbl('Subtotal'),
                static::COLUMN_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\SubtotalRangeBegin',
                static::COLUMN_CREATE_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\SubtotalRangeBegin',
                static::COLUMN_ORDERBY  => 100,
            ),
            'value' => array(
                static::COLUMN_NAME => \XLite\Core\Translation::lbl('Discount'),
                static::COLUMN_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\DiscountValue',
                static::COLUMN_CREATE_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\DiscountValue',
                static::COLUMN_ORDERBY  => 200,
            ),
            'type' => array(
                static::COLUMN_NAME => '',
                static::COLUMN_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\DiscountType',
                static::COLUMN_CREATE_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\DiscountType',
                static::COLUMN_ORDERBY  => 300,
            ),
            'membership' => array(
                static::COLUMN_NAME => \XLite\Core\Translation::lbl('Membership'),
                static::COLUMN_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\Membership',
                static::COLUMN_CREATE_CLASS => 'XLite\Module\CDev\VolumeDiscounts\View\FormField\Membership',
                static::COLUMN_ORDERBY  => 400,
            ),
        );
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('volume_discounts');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'Add discount';
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return false;
    }

    /**
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * Inline creation mechanism position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array('volumeDiscounts');
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' volume-discounts';
    }

    /**
     * Post-validate new entity
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function prevalidateNewEntity(\XLite\Model\AEntity $entity)
    {
        $result = parent::prevalidateNewEntity($entity);

        if ($result && $entity->getRepository()->findOneSimilarDiscount($entity)) {
            $this->errorMessages[] = static::t('Could not add the discount because another discount already exists for the specified subtotal range and membership level');
            $result = false;

        } else {
            $this->discountKeys[] = $entity->getFingerprint();
        }

        return $result;
    }

    /**
     * Pre-validate entities
     *
     * @return boolean
     */
    protected function prevalidateEntities()
    {
        $result = parent::prevalidateEntities();

        if ($result && count(array_unique($this->discountKeys)) != count($this->discountKeys)) {
            $this->errorMessages[] = static::t('Could not update the discount because another discount already exists for the specified subtotal range and membership level');
            $result = false;
        }

        return $result;
    }

    /**
     * Pre-validate entity
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function prevalidateEntity(\XLite\Model\AEntity $entity)
    {
        $result = parent::prevalidateEntity($entity);

        if ($result) {
            $this->discountKeys[] = $entity->getFingerprint();
        }

        return $result;
    }

    // {{{ Data

    /**
     * Return discounts list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $cnd->{\XLite\Module\CDev\VolumeDiscounts\Model\Repo\VolumeDiscount::P_ORDER_BY_MEMBERSHIP} = array('membership.membership_id', 'ASC');
        $cnd->{\XLite\Module\CDev\VolumeDiscounts\Model\Repo\VolumeDiscount::P_ORDER_BY_SUBTOTAL} = array('v.subtotalRangeBegin', 'ASC');


        return \XLite\Core\Database::getRepo('XLite\Module\CDev\VolumeDiscounts\Model\VolumeDiscount')
            ->search($cnd, $countOnly);
    }

    // }}}
}
