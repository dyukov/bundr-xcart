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

namespace XLite\View\ItemsList\Model\Translation;

/**
 * Languages list
 */
class Languages extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/languages/controller.js';

        return $list;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Language';
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\Language\Admin';
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
                static::COLUMN_NAME     => static::t('Language'),
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/languages/cell.name.tpl',
                static::COLUMN_ORDERBY  => 100,
            ),
            'code' => array(
                static::COLUMN_NAME => static::t('Code'),
                static::COLUMN_ORDERBY  => 200,
            ),
            'defaultCustomer' => array(
                static::COLUMN_NAME  => static::t('Default for customers'),
                static::COLUMN_CLASS => '\XLite\View\FormField\Inline\Input\Radio\Radio',
                static::COLUMN_EDIT_ONLY => true,
                static::COLUMN_PARAMS => array(
                    'fieldName' => 'defaultCustomer',
                ),
                static::COLUMN_ORDERBY  => 300,
            ),
            'defaultAdmin' => array(
                static::COLUMN_NAME => static::t('Default for admins'),
                static::COLUMN_CLASS => '\XLite\View\FormField\Inline\Input\Radio\Radio',
                static::COLUMN_EDIT_ONLY => true,
                static::COLUMN_PARAMS => array(
                    'fieldName' => 'defaultAdmin',
                ),
                static::COLUMN_ORDERBY  => 400,
            ),
            'labels_count' => array(
                static::COLUMN_NAME => static::t('Labels'),
                static::COLUMN_TEMPLATE => $this->getDir() . '/' . $this->getPageBodyDir() . '/languages/cell.labels.tpl',
                static::COLUMN_LINK => 'language',
                static::COLUMN_HEAD_HELP => $this->getColumnLabelsCountHelp(),
                static::COLUMN_ORDERBY  => 500,
            ),
        );
    }

    /**
     * Labels count column head help text
     *
     * @return string
     */
    protected function getColumnLabelsCountHelp()
    {
        return static::t('Displays the number of labels translated to the language');
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array('languages');
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' languages';
    }

    /**
     * Return languages list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        $languages = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findAddedLanguages();

        return $countOnly ? count($languages) : $languages;
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
     * Mark list as removable
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
    }

    /**
     * Return true if entity is used as a default for admin or customer interfaces
     *
     * @param \XLite\Model\Language $entity Language object
     *
     * @return boolean
     */
    protected function isUsedAsDefault($entity)
    {
        return $entity->getDefaultCustomer() || $entity->getDefaultAdmin();
    }

    /**
     * Mark list item as default
     *
     * @return boolean
     */
    protected function isDefault()
    {
        return false;
    }

    /**
     * Mark list as selectable
     *
     * @return boolean
     */
    protected function isSelectable()
    {
        return false;
    }

    /**
     * Get column CSS class for specific entity
     *
     * @param array                 $column Column data
     * @param \XLite\Model\Language $entity Language object
     *
     * @return string
     */
    protected function getColumnClass(array $column, \XLite\Model\AEntity $entity = null)
    {
        $class = parent::getColumnClass($column, $entity);

        if (in_array($column[static::COLUMN_CODE], array('defaultCustomer', 'defaultAdmin')) && !$entity->getEnabled()) {
            $class .= ' disabled';
        }

        return $class;
    }

    /**
     * Remove language entity
     *
     * @param \XLite\Model\Language $entity Language object
     *
     * @return boolean
     */
    protected function removeEntity(\XLite\Model\AEntity $entity)
    {
        return $this->isAllowEntityRemove($entity) && $entity->setAdded(false) && $entity->removeTranslations();
    }

    /**
     * Disable removing English language (as all texts are hardcoded in English)
     *
     * @param \XLite\Model\Language $entity Language object
     *
     * @return boolean
     */
    protected function isAllowEntityRemove(\XLite\Model\AEntity $entity)
    {
        return 'en' != $entity->getCode() && !$entity->getValidModule();
    }

    /**
     * Get count of labels for specific language
     *
     * @param \XLite\Model\Language $entity Language object
     *
     * @return boolean
     */
    protected function getLabelsCount(\XLite\Model\AEntity $entity)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\LanguageLabel')->countByCode($entity->getCode());
    }

    /**
     * Add right actions
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = parent::getRightActions();

        array_unshift($list, 'items_list/model/table/languages/action.csv.tpl');

        return $list;
    }

    /**
     * Define line class as list of names
     *
     * @param integer              $index  Line index
     * @param \XLite\Model\AEntity $entity Line model
     *
     * @return array
     */
    protected function defineLineClass($index, \XLite\Model\AEntity $entity)
    {
        $classes = parent::defineLineClass($index, $entity);

        if (!$entity->getEnabled()) {
            $classes[] = 'lock';
        }

        return $classes;
    }

    /**
     * Get specific language help message
     *
     * @param \XLite\Model\AEntity $entity Language object
     *
     * @return string
     */
    protected function getLanguageHelpMessage(\XLite\Model\AEntity $entity)
    {
        $message = null;

        if ($entity->getValidModule()) {
            $moduleClass = \Includes\Utils\ModulesManager::getClassNameByModuleName($entity->getModule());
            $moduleName = sprintf('%s (%s)', $moduleClass::getModuleName(), $moduleClass::getAuthorName());
            $message = static::t('This language is added by module and cannot be removed.', array('module' => $moduleName));

        } elseif ('en' == $entity->getCode()) {
            $message = 'English language cannot be removed as it is primary language for all texts.';
        }

        return $message;
    }

    /**
     * Get remove message
     *
     * @param integer $count Count
     *
     * @return string
     */
    protected function getRemoveMessage($count)
    {
        return \XLite\Core\Translation::lbl('X languages have been removed', array('count' => $count));
    }
}
