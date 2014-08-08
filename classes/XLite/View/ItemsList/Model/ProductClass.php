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

namespace XLite\View\ItemsList\Model;

/**
 * Product classes items list
 */
class ProductClass extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'product_classes/style.css';

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
                static::COLUMN_CLASS        => 'XLite\View\FormField\Inline\Input\Text\ProductClass',
                static::COLUMN_PARAMS       => array('required' => true),
                static::COLUMN_MAIN         => true,
                static::COLUMN_ORDERBY      => 100,
            ),
            'attributes' => array(
                static::COLUMN_TEMPLATE      => 'product_classes/parts/edit_attributes.tpl',
                static::COLUMN_HEAD_TEMPLATE => 'product_classes/parts/edit_attributes.tpl',
                static::COLUMN_ORDERBY       => 200,
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
        return 'XLite\Model\ProductClass';
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('product_class');
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'New product class';
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
     * Mark list as sortable
     *
     * @return integer
     */
    protected function getSortableType()
    {
        return static::SORT_TYPE_MOVE;
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' product_classes';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\ItemsList\ProductClass';
    }


    // {{{ Search

    /**
     * Return search parameters.
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array();
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     */
    protected function getSearchCondition()
    {
        $result = parent::getSearchCondition();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }

        return $result;
    }

    // }}}

    /**
     * Return attributes count.
     *
     * @param mixed $entity Model
     *
     * @return integer
     */
    protected function getAttributesCount($entity)
    {
        if ($entity && $entity->isPersistent()) {
            $result = $entity->getAttributesCount();

        } else {
            $cnd = new \XLite\Core\CommonCell;
            $cnd->productClass = null;
            $cnd->product = null;
            $result = \XLite\Core\Database::getRepo('\XLite\Model\Attribute')->search($cnd, true);
        }

        return $result;
    }

    /**
     * Return edit url.
     *
     * @param mixed $entity Model
     *
     * @return string
     */
    protected function getEditURL($entity)
    {
        return $entity && $entity->getId()
            ? $this->buildURL('attributes', '', array('product_class_id' => $entity->getId()))
            : $this->buildURL('attributes');
    }

    /*
     * Get empty list template
     *
     * @return string
     */
    protected function getEmptyListTemplate()
    {
        return 'product_classes/empty.tpl';
    }

    /**
     * Return entities list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        if ($countOnly) {
            $result = 1 + parent::getData($cnd, $countOnly);

        } else {
            $class = new \XLite\Model\ProductClass;
            $class->setName(static::t('Global attributes'));

            $result = array_merge(array($class), parent::getData($cnd, $countOnly));
        }

        return $result;
    }

}
