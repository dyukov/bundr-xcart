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

namespace XLite\View\FormField\Select;

/**
 * Category selector
 */
class Category extends \XLite\View\FormField\Select\ASelect
{
    const INDENT_STRING     = '-';
    const INDENT_MULTIPLIER = 3;

    /**
     * Widget param names
     */
    const PARAM_DISPLAY_ANY_CATEGORY = 'displayAnyCategory';

    /**
     * Define widget params
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            static::PARAM_DISPLAY_ANY_CATEGORY => new \XLite\Model\WidgetParam\Bool('Display \'Any category\' row', false),
        );
    }

    /**
     * getOptions
     *
     * @return array
     */
    protected function getOptions()
    {
        $list = parent::getOptions();

        if ($this->getParam(static::PARAM_DISPLAY_ANY_CATEGORY)) {
            $list = array(static::t('Any category')) + $list;
        }

        return $list;
    }

    /**
     * Return default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $list = array();

        foreach (\XLite\Core\Database::getRepo('\XLite\Model\Category')->getPlanListForTree() as $category) {
            $name = $this->getCategoryName($category) ?: static::t('n/a');
            $list[$category['category_id']] = $this->getIndentationString($category) . $name;
        }

        return $list;
    }

    /**
     * Return indentation string for displaying category depth level
     *
     * @param array $category Category data
     *
     * @return string
     */
    protected function getIndentationString(array $category)
    {
        return str_repeat(static::INDENT_STRING, $category['depth'] * static::INDENT_MULTIPLIER);
    }

    /**
     * Return translated category name
     *
     * :KLUDGE: it's the hack to prevent execution of superflous queries
     *
     * @param array $category Category data
     *
     * @return string
     */
    protected function getCategoryName(array $category)
    {
        $name = null;

        $query = \XLite\Core\Translation::getLanguageQuery(\XLite\Core\Session::getInstance()->getLanguage()->getCode());
        foreach ($query as $code) {
            $data = \Includes\Utils\ArrayManager::searchInArraysArray(
                $category['translations'],
                'code',
                $code
            );
            if (!empty($data)) {
                $name = $data['name'];
                break;
            }
        }

        return $name;
    }

}
