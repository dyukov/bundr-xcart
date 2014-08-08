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

namespace XLite\View\ItemsList\Model;

/**
 * Abstract admin model-based items list (table)
 */
abstract class Table extends \XLite\View\ItemsList\Model\AModel
{
    const COLUMN_NAME          = 'name';
    const COLUMN_TEMPLATE      = 'template';
    const COLUMN_HEAD_TEMPLATE = 'headTemplate';
    const COLUMN_HEAD_HELP     = 'headHelp';
    const COLUMN_SUBHEADER     = 'subheader';
    const COLUMN_CLASS         = 'class';
    const COLUMN_CODE          = 'code';
    const COLUMN_LINK          = 'link';
    const COLUMN_METHOD_SUFFIX = 'methodSuffix';
    const COLUMN_CREATE_CLASS  = 'createClass';
    const COLUMN_MAIN          = 'main';
    const COLUMN_SERVICE       = 'service';
    const COLUMN_PARAMS        = 'params';
    const COLUMN_SORT          = 'sort';
    const COLUMN_SEARCH_WIDGET = 'searchWidget';
    const COLUMN_NO_WRAP       = 'noWrap';
    const COLUMN_EDIT_ONLY     = 'editOnly';
    const COLUMN_SELECTOR      = 'columnSelector';
    const COLUMN_REMOVE        = 'columnRemove';
    const COLUMN_ORDERBY       = 'orderBy';

    /**
     * Columns (local cache)
     *
     * @var array
     */
    protected $columns;

    /**
     * Main column index
     *
     * @var integer
     */
    protected $mainColumn;

    /**
     * Define columns structure
     *
     * @return array
     */
    abstract protected function defineColumns();

    /**
     * Sorting helper for uasort
     *
     * @param array $column1
     * @param array $column2
     *
     * @return boolean
     */
    public function sortColumnsByOrder($column1, $column2)
    {
        $column1[static::COLUMN_ORDERBY] = isset($column1[static::COLUMN_ORDERBY]) ? $column1[static::COLUMN_ORDERBY] : 0;
        $column2[static::COLUMN_ORDERBY] = isset($column2[static::COLUMN_ORDERBY]) ? $column2[static::COLUMN_ORDERBY] : 0;

        return $column1[static::COLUMN_ORDERBY] > $column2[static::COLUMN_ORDERBY];
    }

    /**
     * The columns are ordered according the static::COLUMN_ORDERBY values
     *
     * @return array
     */
    protected function prepareColumns()
    {
        $columns = $this->defineColumns();
        uasort($columns, array($this, 'sortColumnsByOrder'));

        return $columns;
    }

    /**
     * Get a list of CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/style.css';

        if (static::SORT_TYPE_MOVE === $this->getSortableType()) {
            $list = array_merge(
                $list,
                $this->getWidget(array(), $this->getMovePositionWidgetClassName())->getCSSFiles(),
                $this->getWidget(array(), $this->getOrderByWidgetClassName())->getCSSFiles()
            );
        }

        return $list;
    }

    /**
     * Get a list of JavaScript files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/controller.js';

        if (static::SORT_TYPE_MOVE === $this->getSortableType()) {
            $list = array_merge(
                $list,
                $this->getWidget(array(), $this->getMovePositionWidgetClassName())->getJSFiles(),
                $this->getWidget(array(), $this->getOrderByWidgetClassName())->getJSFiles()
            );
        }

        return $list;
    }

    /**
     * Check - pager box is visible or not
     *
     * @return boolean
     */
    protected function isPagerVisible()
    {
        return parent::isPagerVisible()
            && $this->getPager()->isVisible();
    }

    /**
     * Get preprocessed columns structire
     *
     * @return array
     */
    protected function getColumns()
    {
        if (!isset($this->columns)) {
            $this->columns = array();

            if ($this->getLeftActions()) {
                $this->columns[] = array(
                    static::COLUMN_CODE     => 'actions left',
                    static::COLUMN_NAME     => '',
                    static::COLUMN_TEMPLATE => 'items_list/model/table/left_actions.tpl',
                    static::COLUMN_SERVICE  => true,
                    static::COLUMN_HEAD_TEMPLATE => static::SORT_TYPE_INPUT === $this->getSortType()
                        ? 'items_list/model/table/parts/pos_input.tpl'
                        : (static::SORT_TYPE_MOVE === $this->getSortType()
                            ? 'items_list/model/table/parts/pos_move.tpl'
                            : ''
                        ),
                    static::COLUMN_SELECTOR => $this->isSelectable(),
                );
            }

            foreach ($this->prepareColumns() as $idx => $column) {
                $column[static::COLUMN_CODE] = $idx;
                $column[static::COLUMN_METHOD_SUFFIX] = \XLite\Core\Converter::convertToCamelCase($column[static::COLUMN_CODE]);
                if (!isset($column[static::COLUMN_TEMPLATE]) && !isset($column[static::COLUMN_CLASS])) {
                    $column[static::COLUMN_TEMPLATE] = 'items_list/model/table/field.tpl';
                }
                $column[static::COLUMN_PARAMS] = isset($column[static::COLUMN_PARAMS]) ? $column[static::COLUMN_PARAMS] : array();
                $this->columns[] = $column;
            }

            if ($this->getRightActions()) {
                $this->columns[] = array(
                    static::COLUMN_CODE     => 'actions right',
                    static::COLUMN_NAME     => '',
                    static::COLUMN_TEMPLATE => 'items_list/model/table/right_actions.tpl',
                    static::COLUMN_SERVICE  => true,
                    static::COLUMN_REMOVE   => $this->isRemoved(),
                );
            }
        }

        return $this->columns;
    }

    /**
     * Returnd columns count
     *
     * @return integer
     */
    protected function getColumnsCount()
    {
        return count($this->getColumns());
    }

    /**
     * Check - table header is visible or not
     *
     * @return boolean
     */
    protected function isTableHeaderVisible()
    {
        $result = false;
        foreach ($this->getColumns() as $column) {
            if (!empty($column['name'])) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Get main column
     *
     * @return array
     */
    protected function getMainColumn()
    {
        if (!isset($this->mainColumn)) {
            $result = null;
            $first = null;

            foreach ($this->getColumns() as $i => $column) {
                if (!isset($column[static::COLUMN_SERVICE]) || !$column[static::COLUMN_SERVICE]) {
                    if (!isset($first)) {
                        $first = $i;
                    }
                    if (isset($column[static::COLUMN_MAIN]) && $column[static::COLUMN_MAIN]) {
                        $result = $i;
                        break;
                    }
                }
            }

            $this->mainColumn = isset($result) ? $result : $first;
        }

        $columns = $this->getColumns();

        return isset($columns[$this->mainColumn]) ? $columns[$this->mainColumn] : null;
    }

    /**
     * Check - specified column is main or not
     *
     * @param array $column Column
     *
     * @return void
     */
    protected function isMainColumn(array $column)
    {
        $main = $this->getMainColumn();

        return $main && $column[static::COLUMN_CODE] == $main[static::COLUMN_CODE];
    }

    /**
     * Get column value
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model
     *
     * @return mixed
     */
    protected function getColumnValue(array $column, \XLite\Model\AEntity $entity)
    {
        $suffix = $column[static::COLUMN_METHOD_SUFFIX];

        // Getter
        $method = 'get' . $suffix . 'ColumnValue';
        $value = method_exists($this, $method)
            ? $this->$method($entity)
            : $entity->{$column[static::COLUMN_CODE]};

        // Preprocessing
        $method = 'preprocess' . \XLite\Core\Converter::convertToCamelCase($column[static::COLUMN_CODE]);
        if (method_exists($this, $method)) {

            // $method assembled frm 'preprocess' + field name
            $value = $this->$method($value, $column, $entity);
        }

        return $value;
    }

    /**
     * Get field objects list (only inline-based form fields)
     *
     * @return array
     */
    protected function getFieldObjects()
    {
        $list = array();

        foreach ($this->getColumns() as $column) {
            $name = $column[static::COLUMN_CODE];
            if (
                isset($column[static::COLUMN_CLASS])
                && is_subclass_of($column[static::COLUMN_CLASS], 'XLite\View\FormField\Inline\AInline')
            ) {
                $params = isset($column[static::COLUMN_PARAMS]) ? $column[static::COLUMN_PARAMS] : array();
                $list[] = array(
                    'class'      => $column[static::COLUMN_CLASS],
                    'parameters' => array('fieldName' => $name, 'fieldParams' => $params),
                );
            }
        }

        if ($this->isSwitchable()) {
            $cell = $this->getSwitcherField();
            $list[] = array(
                'class'      => $cell['class'],
                'parameters' => array('fieldName' => $cell['name'], 'fieldParams' => $cell['params']),
            );
        }

        if (static::SORT_TYPE_NONE != $this->getSortType()) {
            $cell = $this->getSortField();
            $list[] = array(
                'class'      => $cell['class'],
                'parameters' => array('fieldName' => $cell['name'], 'fieldParams' => $cell['params']),
            );
        }

        foreach ($list as $i => $class) {
            $list[$i] = new $class['class']($class['parameters']);
        }

        return $list;
    }

    /**
     * Get switcher field
     *
     * @return array
     */
    protected function getSwitcherField()
    {
        return array(
            'class'  => 'XLite\View\FormField\Inline\Input\Checkbox\Switcher\Enabled',
            'name'   => 'enabled',
            'params' => array(),
        );
    }

    /**
     * Get sort field
     *
     * @return array
     */
    protected function getSortField()
    {
        return static::SORT_TYPE_INPUT == $this->getSortType()
            ? array(
                'class'  => $this->getOrderByWidgetClassName(),
                'name'   => 'position',
                'params' => array(),
            )
            :
            array(
                'class'  => $this->getMovePositionWidgetClassName(),
                'name'   => 'position',
                'params' => array(),
            );
    }

    /**
     * Defines the position MOVE widget class name
     *
     * @return string
     */
    protected function getMovePositionWidgetClassName()
    {
        return 'XLite\View\FormField\Inline\Input\Text\Position\Move';
    }

    /**
     * Defines the position OrderBy widget class name
     *
     * @return string
     */
    protected function getOrderByWidgetClassName()
    {
        return 'XLite\View\FormField\Inline\Input\Text\Position\OrderBy';
    }

    /**
     * Get create field classes
     *
     * @return void
     */
    protected function getCreateFieldClasses()
    {
        $list = array();

        foreach ($this->getColumns() as $column) {
            $name = $column[static::COLUMN_CODE];
            $class = null;
            if (
                isset($column[static::COLUMN_CREATE_CLASS])
                && is_subclass_of($column[static::COLUMN_CREATE_CLASS], 'XLite\View\FormField\Inline\AInline')
            ) {
                $class = $column[static::COLUMN_CREATE_CLASS];

            } elseif (
                isset($column[static::COLUMN_CLASS])
                && is_subclass_of($column[static::COLUMN_CLASS], 'XLite\View\FormField\Inline\AInline')
            ) {
                $class = $column[static::COLUMN_CLASS];
            }

            if ($class) {
                $params = isset($column[static::COLUMN_PARAMS]) ? $column[static::COLUMN_PARAMS] : array();
                $list[] = array(
                    'class'      => $class,
                    'parameters' => array(
                        \XLite\View\FormField\Inline\AInline::PARAM_FIELD_NAME   => $name,
                        \XLite\View\FormField\Inline\AInline::PARAM_FIELD_PARAMS => $params,
                        \XLite\View\FormField\Inline\AInline::PARAM_EDIT_ONLY    => true,
                    ),
                );
            }
        }

        foreach ($list as $i => $class) {
            $list[$i] = new $class['class']($class['parameters']);
        }

        return $list;
    }

    /**
     * Get create line columns
     *
     * @return array
     */
    protected function getCreateColumns()
    {
        $columns = array();

        if ($this->getLeftActions()) {
            $columns[] = array(
                static::COLUMN_CODE     => 'actions left',
                static::COLUMN_NAME     => '',
                static::COLUMN_SERVICE  => true,
                static::COLUMN_TEMPLATE => 'items_list/model/table/parts/empty_left.tpl',
            );
        }

        foreach ($this->defineColumns() as $idx => $column) {
            if (
                (isset($column[static::COLUMN_CREATE_CLASS]) && $column[static::COLUMN_CREATE_CLASS])
                || (isset($column[static::COLUMN_CLASS]) && $column[static::COLUMN_CLASS])
            ) {
                $column[static::COLUMN_CODE] = $idx;
                $column[static::COLUMN_METHOD_SUFFIX] = \XLite\Core\Converter::convertToCamelCase($column[static::COLUMN_CODE]);
                if (!isset($column[static::COLUMN_CREATE_CLASS]) || !$column[static::COLUMN_CREATE_CLASS]) {
                    $column[static::COLUMN_CREATE_CLASS] = $column[static::COLUMN_CLASS];
                }
                $columns[] = $column;

            } else {
                $columns[] = array(
                    static::COLUMN_CODE => $idx,
                    static::COLUMN_TEMPLATE => 'items_list/model/table/empty.tpl',
                );
            }
        }

        if ($this->getRightActions()) {
            $columns[] = array(
                static::COLUMN_CODE     => 'actions right',
                static::COLUMN_NAME     => '',
                static::COLUMN_SERVICE  => true,
                static::COLUMN_TEMPLATE => $this->isRemoved()
                    ? 'items_list/model/table/parts/remove_create.tpl'
                    : 'items_list/model/table/parts/empty_right.tpl',
            );
        }

        return $columns;
    }

    /**
     * List has top creation box
     *
     * @return boolean
     */
    protected function isTopInlineCreation()
    {
        return static::CREATE_INLINE_TOP === $this->isInlineCreation();
    }

    /**
     * List has bottom creation box
     *
     * @return boolean
     */
    protected function isBottomInlineCreation()
    {
        return static::CREATE_INLINE_BOTTOM === $this->isInlineCreation();
    }

    /**
     * Return class name for the list pager
     *
     * @return string
     */
    protected function getPagerClass()
    {
        return 'XLite\View\Pager\Admin\Model\Table';
    }

    /**
     * Get cell list name part
     *
     * @param string $type   Cell type
     * @param array  $column Column
     *
     * @return string
     */
    protected function getCellListNamePart($type, array $column)
    {
        return $type . '.' . str_replace(' ', '.', $column[static::COLUMN_CODE]);
    }

    // {{{ Content helpers

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        $class = parent::getContainerClass()
            . ' items-list-table'
            . ($this->isTableHeaderVisible() ? ' no-thead' : '');

        return trim($class);
    }

    /**
     * Get head class
     *
     * @param array $column Column
     *
     * @return string
     */
    protected function getHeadClass(array $column)
    {
        return $column[static::COLUMN_CODE];
    }

    /**
     * Get column cell class
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model OPTIONAL
     *
     * @return string
     */
    protected function getColumnClass(array $column, \XLite\Model\AEntity $entity = null)
    {
        return 'cell '
            . $column[static::COLUMN_CODE]
            . ($this->hasColumnAttention($column, $entity) ? ' attention' : '')
            . ($this->isMainColumn($column) ? ' main' : '')
            . (empty($column[static::COLUMN_NO_WRAP]) ? '' : ' no-wrap');
    }

    /**
     * Check - has specified column attention or not
     *
     * @param array                $column Column
     * @param \XLite\Model\AEntity $entity Model OPTIONAL
     *
     * @return boolean
     */
    protected function hasColumnAttention(array $column, \XLite\Model\AEntity $entity = null)
    {
        return false;
    }

    /**
     * Get action cell class
     *
     * @param integer $i        Cell index
     * @param string  $template Template
     *
     * @return string
     */
    protected function getActionCellClass($i, $template)
    {
        return 'action' . (0 < $i ? ' next' : '');
    }

    // }}}

    // {{{ Top / bottom behaviors

    /**
     * Get top actions
     *
     * @return array
     */
    protected function getTopActions()
    {
        $actions = array();

        if (
            static::CREATE_INLINE_TOP == $this->isCreation()
            && $this->getCreateURL()
            && static::CREATE_INLINE_NONE == $this->isInlineCreation()
        ) {
            $actions[] = 'items_list/model/table/parts/create.tpl';

        } elseif (static::CREATE_INLINE_TOP == $this->isInlineCreation()) {
            $actions[] = 'items_list/model/table/parts/create_inline.tpl';
        }

        return $actions;
    }

    /**
     * Get bottom actions
     *
     * @return array
     */
    protected function getBottomActions()
    {
        $actions = array();

        if (
            static::CREATE_INLINE_BOTTOM == $this->isCreation()
            && $this->getCreateURL()
            && static::CREATE_INLINE_NONE == $this->isInlineCreation()
        ) {
            $actions[] = 'items_list/model/table/parts/create.tpl';

        } elseif (static::CREATE_INLINE_BOTTOM == $this->isInlineCreation()) {
            $actions[] = 'items_list/model/table/parts/create_inline.tpl';
        }

        return $actions;
    }

    // }}}

    // {{{ Line bahaviors

    /**
     * Return sort type
     *
     * @return integer
     */
    protected function getSortType()
    {
        return (static::SORT_TYPE_MOVE === $this->getSortableType() && 1 < $this->getPager()->getPagesCount())
            ? static::SORT_TYPE_INPUT
            : $this->getSortableType();
    }

    /**
     * Get left actions tempaltes
     *
     * @return array
     */
    protected function getLeftActions()
    {
        $list = array();

        if (static::SORT_TYPE_MOVE === $this->getSortType()) {
            $list[] = $this->getMoveActionTemplate();

        } elseif (static::SORT_TYPE_INPUT === $this->getSortType()) {
            $list[] = $this->getPositionActionTemplate();
        }

        if ($this->isSelectable()) {
            $list[] = $this->getSelectorActionTemplate();
        }

        if ($this->isSwitchable()) {
            $list[] = $this->getSwitcherActionTemplate();
        }

        if ($this->isDefault()) {
            $list[] = $this->getDefaultActionTemplate();
        }

        return $list;
    }

    /**
     * Template for position action definition
     *
     * @return string
     */
    protected function getPositionActionTemplate()
    {
        return 'items_list/model/table/parts/position.tpl';
    }

    /**
     * Template for move action definition
     *
     * @return string
     */
    protected function getMoveActionTemplate()
    {
        return 'items_list/model/table/parts/move.tpl';
    }

    /**
     * Template for selector action definition
     *
     * @return string
     */
    protected function getSelectorActionTemplate()
    {
        return 'items_list/model/table/parts/selector.tpl';
    }

    /**
     * Template for switcher action definition
     *
     * @return string
     */
    protected function getSwitcherActionTemplate()
    {
        return 'items_list/model/table/parts/switcher.tpl';
    }

    /**
     * Template for default action definition
     *
     * @return string
     */
    protected function getDefaultActionTemplate()
    {
        return 'items_list/model/table/parts/default.tpl';
    }

    /**
     * Template for default action definition
     *
     * @return string
     */
    protected function getRemoveActionTemplate()
    {
        return 'items_list/model/table/parts/remove.tpl';
    }

    /**
     * Get right actions templates
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = array();

        if ($this->isRemoved()) {
            $list[] = $this->getRemoveActionTemplate();
        }

        return $list;
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
        return $entity->isPersistent();
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
        return (bool)$this->getSwitcherField();
    }

    // }}}

    // {{{ Inherited methods

    /**
     * Check - body tempalte is visible or not
     *
     * @return boolean
     */
    protected function isPageBodyVisible()
    {
        return parent::isPageBodyVisible() || $this->isHeadSearchVisible();
    }

    /**
     * Check - table header is visible or not
     *
     * @return boolean
     */
    protected function isHeaderVisible()
    {
        return 0 < count($this->getTopActions());
    }

    /**
     * isFooterVisible
     *
     * @return boolean
     */
    protected function isFooterVisible()
    {
        return 0 < count($this->getBottomActions());
    }

    /**
     * Return file name for body template
     *
     * @return string
     */
    protected function getBodyTemplate()
    {
        return 'model/table.tpl';
    }

    /**
     * Return dir which contains the page body template
     *
     * @return string
     */
    protected function getPageBodyDir()
    {
        return parent::getPageBodyDir() . '/table';
    }

    /**
     * Remove entity
     *
     * @param \XLite\Model\AEntity $entity Entity
     *
     * @return boolean
     */
    protected function removeEntity(\XLite\Model\AEntity $entity)
    {
        return $this->isAllowEntityRemove($entity) && parent::removeEntity($entity);
    }

    // }}}

    // {{{ Head sort

    /**
     * Check - specified column is sorted or not
     *
     * @param array $column COlumn
     *
     * @return boolean
     */
    protected function isColumnSorted(array $column)
    {
        $field = $this->getSortBy();

        return !empty($column[static::COLUMN_SORT]) && $field == $column[static::COLUMN_SORT];
    }

    /**
     * Get next sort direction
     *
     * @param array $column Column
     *
     * @return string
     */
    protected function getSortDirectionNext(array $column)
    {
        if ($this->isColumnSorted($column)) {
            $direction = static::SORT_ORDER_DESC == $this->getSortOrder() ? static::SORT_ORDER_ASC : static::SORT_ORDER_DESC;

        } else {
            $direction = $this->getSortOrder() ?: static::SORT_ORDER_DESC;
        }

        return $direction;
    }

    /**
     * Get sort link class
     *
     * @param array $column Column
     *
     * @return string
     */
    protected function getSortLinkClass(array $column)
    {
        $classes = 'sort';
        if ($this->isColumnSorted($column)) {
            $classes .= ' current-sort ' . $this->getSortOrder() . '-direction';
        }

        return $classes;
    }

    // }}}

    // {{{ Head search

    /**
     * Check - search-in-head mechanism is available or not
     *
     * @return boolean
     */
    protected function isHeadSearchVisible()
    {
        $found = false;

        foreach ($this->getColumns() as $column) {
            if ($this->isSearchColumn($column)) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * Check - specified column has search widget or not
     *
     * @param array $column Column info
     *
     * @return boolean
     */
    protected function isSearchColumn(array $column)
    {
        return !empty($column[static::COLUMN_SEARCH_WIDGET]);
    }


    /**
     * Get search cell class
     *
     * @param array $column ____param_comment____
     *
     * @return void
     */
    protected function getSearchCellClass(array $column)
    {
        return 'search-cell ' . $column[static::COLUMN_CODE] . ' '
            . ($this->isSearchColumn($column) ? 'filled' : 'empty');
    }

    // }}}

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
        return !empty($column[static::COLUMN_TEMPLATE]);
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
        return !isset($column[static::COLUMN_TEMPLATE]);
    }

    /**
     * Check if the column must be a link.
     * It is used if the column field is displayed via
     *
     * @param array                $column
     * @param \XLite\Model\AEntity $entity
     *
     * @return boolean
     */
    protected function isLink(array $column, \XLite\Model\AEntity $entity)
    {
        return isset($column[static::COLUMN_LINK]);
    }
}

