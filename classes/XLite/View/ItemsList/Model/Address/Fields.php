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

namespace XLite\View\ItemsList\Model\Address;

/**
 * Address fields items list
 */
class Fields extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/address_fields/style.css';

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
            'name' => array(
                static::COLUMN_NAME     => static::t('Name'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS   => array('required' => true),
                static::COLUMN_ORDERBY  => 100,
            ),
            'serviceName' => array(
                static::COLUMN_NAME     => static::t('Service name'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Input\AddressFieldsServiceName',
                static::COLUMN_TEMPLATE => 'items_list/model/table/field.tpl',
                static::COLUMN_PARAMS   => array('required' => true),
                static::COLUMN_ORDERBY  => 200,
            ),
            'required' => array(
                static::COLUMN_NAME     => static::t('Required'),
                static::COLUMN_CLASS    => 'XLite\View\FormField\Inline\Input\Checkbox\Switcher\Enabled',
                static::COLUMN_PARAMS   => array(),
                static::COLUMN_ORDERBY  => 300,
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
        return 'XLite\Model\AddressField';
    }

    // {{{ Behaviors

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
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return true;
    }

    /**
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildURL('address_field');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New address field';
    }

    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isInlineCreation()
    {
        return static::CREATE_INLINE_TOP;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' address-fields';
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        $result->{\XLite\Model\Repo\AddressField::CND_WITHOUT_CSTATE} = true;

        return $result;
    }

    /**
     * Return "empty list" catalog
     *
     * @return string
     */
    protected function getEmptyListDir()
    {
        return parent::getEmptyListDir();
    }

    /**
     * Check - remove entity or not
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function isAllowEntityRemove(\XLite\Model\AEntity $entity)
    {
        return parent::isAllowEntityRemove($entity) && $entity->getAdditional();
    }

    /**
     * Check - switch entity or not
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function isAllowEntitySwitch(\XLite\Model\AEntity $entity)
    {
        // Custom state is not allowed to switch off
        return parent::isAllowEntitySwitch($entity) && 'custom_state' !== $entity->getServiceName();
    }

    /**
     * Check if the column template is used for widget displaying
     *
     * @param array                $column
     * @param \XLite\Model\AEntity $entity
     *
     * @return boolean
     */
    protected function isTemplateColumnVisible(array $column, \XLite\Model\AEntity $entity)
    {
        // Right now admin cannot directly edit serviceName values for additional fields
        // and cannot change "Not required" state of "custom_state" field
        // TODO: refactor it
        return 'serviceName' !== $column[static::COLUMN_CODE]
            ? parent::isTemplateColumnVisible($column, $entity)
            : !$entity->getAdditional();
    }


    /**
     * Check if the simple class is used for widget displaying
     *
     * @param array                $column
     * @param \XLite\Model\AEntity $entity
     *
     * @return boolean
     */
    protected function isClassColumnVisible(array $column, \XLite\Model\AEntity $entity)
    {
        // Right now admin cannot directly edit serviceName values for additional fields
        // and cannot change "Not required" state of "custom_state" field
        // TODO: refactor it
        return 'serviceName' !== $column[static::COLUMN_CODE]
            ? (('custom_state' === $entity->getServiceName() && 'required' === $column[static::COLUMN_CODE])
                ? false
                : parent::isClassColumnVisible($column, $entity)
            )
            : $entity->getAdditional();
    }

    /**
     * Update entities
     *
     * @return void
     */
    protected function updateEntities()
    {
        parent::updateEntities();

        $enabled = false;
        $name = 'State';
        foreach ($this->getPageData() as $entity) {
            if ('state_id' == $entity->getServiceName()) {
                $enabled = $entity->getEnabled();
                $name = $entity->getName();
            }
        }

        $entity = \XLite\Core\Database::getRepo('XLite\Model\AddressField')->findOneByServiceName('custom_state');
        if ($entity) {
            $entity->setEnabled($enabled);
            $entity->setName($name);
        }

    }

}
