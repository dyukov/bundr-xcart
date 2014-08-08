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

namespace XLite\View\SearchPanel;

/**
 * Abstract search panel
 */
abstract class ASearchPanel extends \XLite\View\AView
{

    /**
     * Condition cell names
     */
    const CONDITION_CLASS    = 'class';
    const CONDITION_TEMPLATE = 'template';
    const CONDITION_CELL     = 'cell';

    /**
     * Conditions
     *
     * @var   array
     */
    protected $conditions;

    /**
     * Hidden conditions
     *
     * @var   array
     */
    protected $hiddenConditions;

    /**
     * Actions
     *
     * @var   array
     */
    protected $actions;

    /**
     * Get form class
     *
     * @return string
     */
    abstract protected function getFormClass();

    /**
     * Define the items list CSS class with which the search panel must be linked
     * @TODO: restructure them to make possible the multiple search panels and items list appearance on one page
     *
     * @return string
     */
    protected function getLinkedItemsList()
    {
        return '';
    }

    /**
     * Define the specific widget params to send them into the JS code
     *
     * @return array
     */
    protected function getSearchPanelParams()
    {
        return array(
            'linked' => $this->getLinkedItemsList(),
        );
    }

    // {{{ Conditions

    /**
     * Get conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        if (!isset($this->conditions)) {
            $this->conditions = $this->defineConditions();
            $this->conditions = $this->prepareConditions($this->conditions);
        }

        return $this->conditions;
    }

    /**
     * Get hidden conditions
     *
     * @return array
     */
    protected function getHiddenConditions()
    {
        if (!isset($this->hiddenConditions)) {
            $this->hiddenConditions = $this->defineHiddenConditions();
            $this->hiddenConditions = $this->prepareConditions($this->hiddenConditions);
        }

        return $this->hiddenConditions;
    }

    /**
     * Define conditions
     *
     * @return array
     */
    protected function defineConditions()
    {
        return array();
    }

    /**
     * Define hidden conditions
     *
     * @return array
     */
    protected function defineHiddenConditions()
    {
        return array();
    }

    /**
     * Prepare conditions
     *
     * @param array $conditions Conditions
     *
     * @return array
     */
    protected function prepareConditions(array $conditions)
    {
        foreach ($conditions as $name => $condition) {
            if (is_array($condition)) {
                if (!isset($condition[\XLite\View\FormField\AFormField::PARAM_NAME])) {
                    $condition[\XLite\View\FormField\AFormField::PARAM_NAME] = $name;
                }

                if (
                    !isset($condition[\XLite\View\FormField\AFormField::PARAM_VALUE])
                    && method_exists(\XLite::getController(), 'getCondition')
                ) {
                    if (empty($condition[static::CONDITION_CELL])) {
                        $condition[static::CONDITION_CELL] = $name;
                    }
                    $condition[\XLite\View\FormField\AFormField::PARAM_VALUE] = $this->prepareConditionValue($condition);
                }
                $conditions[$name] = $this->getWidgetByParams($condition);
            }
        }

        return $conditions;
    }

    /**
     * Prepare the value of the condition
     *
     * @param array $condition
     *
     * @return mixed
     */
    protected function prepareConditionValue($condition)
    {
        return $this->getCondition($condition[static::CONDITION_CELL]);
    }

    // }}}

    // {{{ Actions

    /**
     * Get actions
     *
     * @return array
     */
    protected function getActions()
    {
        if (!isset($this->actions)) {
            $this->actions = $this->defineActions();
            $this->actions = $this->prepareActions($this->actions);
        }

        return $this->actions;
    }

    /**
     * Define actions
     *
     * @return array
     */
    protected function defineActions()
    {
        return array(
            'submit' => array(
                'class'                                     => '\XLite\View\Button\Submit',
                \XLite\View\Button\AButton::PARAM_LABEL     => static::t('Search'),
                \XLite\View\Button\AButton::PARAM_BTN_SIZE  => \XLite\View\Button\AButton::BTN_SIZE_DEFAULT,
                \XLite\View\Button\AButton::PARAM_BTN_TYPE  => '',
            ),
        );
    }

    /**
     * Prepare actions
     *
     * @param array $actions Actions
     *
     * @return array
     */
    protected function prepareActions(array $actions)
    {
        foreach ($actions as $name => $action) {
            if (is_array($action)) {
                $actions[$name] = $this->getWidgetByParams($action);
            }
        }

        return $actions;
    }

    // }}}

    // {{{ Visual

    /**
     * Via this method the widget registers the CSS files which it uses.
     * During the viewers initialization the CSS files are collecting into the static storage.
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'search_panel/style.css';

        return $list;
    }

    /**
     * Via this method the widget registers the JS files which it uses.
     * During the viewers initialization the JS files are collecting into the static storage.
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'search_panel/controller.js';
        return $list;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && 0 < count($this->getConditions());
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'search_panel/body.tpl';
    }

    /**
     * Get container form class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return implode('-', $this->getViewClassKeys());
    }

    /**
     * Combines the nested list name from the parent list name and a suffix
     *
     * @param string $part Suffix to be added to the parent list name
     *
     * @return string
     */
    protected function getNestedListName($part)
    {
        $keys = $this->getViewClassKeys();

        if ('searchpanel' == $keys[0]) {
            unset($keys[0]);

        } elseif ('searchpanel' == $keys[2]) {
            unset($keys[2]);
        }

        return 'searchPanel.' . implode('.', $keys) . '.' . $part;
    }

    // }}}

}

