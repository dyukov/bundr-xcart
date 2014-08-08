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
 * Labels list
 */
class Labels extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Widget param names
     */
    const PARAM_SUBSTRING  = 'substring';
    const PARAM_CODE       = 'code';

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();
        $list[] = $this->getDir() . '/' . $this->getPageBodyDir() . '/labels/controller.js';

        return $list;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\LanguageLabel';
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
                static::COLUMN_NAME      => static::t('Label'),
                static::COLUMN_TEMPLATE  => $this->getDir() . '/' . $this->getPageBodyDir() . '/labels/cell.name.tpl',
                static::COLUMN_ORDERBY  => 100,
            ),
        );
    }

    /**
     * Get list name suffixes
     *
     * @return array
     */
    protected function getListNameSuffixes()
    {
        return array('labels');
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' labels';
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
     * Mark list as selectable
     *
     * @return boolean
     */
    protected function isSelectable()
    {
        return false;
    }

    /**
     * Get default language object
     *
     * @return \XLite\Model\Language
     */
    protected function getDefaultLanguageObject()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(static::getDefaultLanguage());
    }

    /**
     * Get translation language object
     *
     * @return \XLite\Model\Language
     */
    protected function getTranslationLanguageObject()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode($this->getLanguageCode());
    }

    /**
     * Get translation language code
     *
     * @return string
     */
    protected function getLanguageCode()
    {
        return $this->getParam(static::PARAM_CODE)
            ?: \XLite\Core\Request::getInstance()->code ?: static::getDefaultLanguage();
    }

    /**
     * Get label translation to the default language
     *
     * @param \XLite\Model\LanguageLabel $entity
     *
     * @return string
     */
    protected function getLabelDefaultValue($entity)
    {
        $result = $entity->getTranslation(static::getDefaultLanguage())->label;

        if (!$result && $this->isTranslatedLanguageSelected()) {
            $result = $entity->getName();
        }

        return $result;
    }

    /**
     * Get label translation to the selected language (by code passed as a page argument)
     *
     * @param \XLite\Model\LanguageLabel $entity
     *
     * @return string
     */
    protected function getLabelTranslatedValue($entity)
    {
        return $entity->getTranslation($this->getLanguageCode())->label;
    }

    /**
     * Return true if default language and selected language are different
     *
     * @return boolean
     */
    protected function isTranslatedLanguageSelected()
    {
        return static::getDefaultLanguage() != $this->getLanguageCode();
    }

    // {{{ Search

    /**
     * Return search parameters
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            \XLite\Model\Repo\Order::SEARCH_SUBSTRING  => static::PARAM_SUBSTRING,
            static::PARAM_CODE                         => static::PARAM_CODE,
        );
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
            static::PARAM_SUBSTRING  => new \XLite\Model\WidgetParam\String('Substring', ''),
            static::PARAM_CODE       => new \XLite\Model\WidgetParam\String('Language code', ''),
        );
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams = array_merge($this->requestParams, static::getSearchParams());
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
            $result->$modelParam = $this->getParam($requestParam);
        }

        $codes = array(static::getDefaultLanguage(), $this->getLanguageCode());
        $result->{\XLite\Model\Repo\LanguageLabel::SEARCH_CODES} = array_unique($codes);

        if (\XLite\Core\Session::getInstance()->added_labels) {
            $result->{\XLite\Model\Repo\LanguageLabel::ORDER_FIRST_BY_IDS} = \XLite\Core\Session::getInstance()->added_labels;
        }

        $result->{\XLite\Model\Repo\LanguageLabel::P_ORDER_BY} = array('l.name', 'ASC');

        return $result;
    }

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     */
    protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false)
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\LanguageLabel')->search($cnd, $countOnly);
    }

    /**
     * Add right actions
     *
     * @return array
     */
    protected function getRightActions()
    {
        $list = parent::getRightActions();

        if ($this->isSeveralLanguagesExists()) {
            array_unshift($list, 'items_list/model/table/labels/action.edit.tpl');
        }

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

        if (
            \XLite\Core\Session::getInstance()->added_labels
            && in_array($entity->getLabelId(), \XLite\Core\Session::getInstance()->added_labels)
        ) {
            $classes[] = 'just-added';
        }

        return $classes;
    }

    /**
     * Get languages list for label translations availability
     *
     * @param \XLite\Model\LanguageLabel $entity
     *
     * @return array
     */
    protected function getLanguageMarks($entity)
    {
        $result = array();

        $languages = \XLite\Core\Database::getRepo('XLite\Model\Language')->findAddedLanguages();

        $translations = $entity->getTranslations();

        foreach ($languages as $l) {
            $code = $l->getCode();
            $found = false;
            foreach ($translations as $t) {
                if ($t->getCode() == $code) {
                    $found = true;
                    break;
                }
            }
            $result[strtoupper($code)] = array(
                'status' => $found ? 'set' : 'unset',
            );

        }

        return $result;
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
        return \XLite\Core\Translation::lbl('X language labels have been removed', array('count' => $count));
    }

    /**
     * Disable editing of the default label translation
     *
     * @return boolean
     */
    protected function isDefaultLanguageNonEditable()
    {
        return $this->isTranslatedLanguageSelected();
    }

    /**
     * Return true if two or more added languages exists
     *
     * @return boolean
     */
    protected function isSeveralLanguagesExists()
    {
        $languages = \XLite\Core\Database::getRepo('XLite\Model\Language')->findAddedLanguages();

        return 1 < count($languages) || 'en' != $this->getLanguageCode();
    }
}
