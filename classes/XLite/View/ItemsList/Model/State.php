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
 * States items list
 */
class State extends \XLite\View\ItemsList\Model\Table
{
    /**
     * Search parameter name
     */
    const PARAM_COUNTRY_CODE = 'country_code';


    /**
     * Get a list of CSS files required to display the widget properly
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = 'states/css/style.css';

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
            'state' => array (
                static::COLUMN_NAME   => \XLite\Core\Translation::lbl('State'),
                static::COLUMN_CLASS  => '\XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS => array('required' => true),
                static::COLUMN_ORDERBY  => 100,
            ),
            'code' => array (
                static::COLUMN_NAME      => \XLite\Core\Translation::lbl('Code'),
                static::COLUMN_CLASS  => '\XLite\View\FormField\Inline\Input\Text',
                static::COLUMN_PARAMS => array('required' => true),
                static::COLUMN_ORDERBY  => 200,
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
        return '\XLite\Model\State';
    }

    /**
     * Return true if widget can be displayed
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return parent::isVisible() && $this->getValidCountry();
    }

    /**
     * Check if country is valid and return country object
     *
     * @return \XLite\Model\Country
     */
    protected function getValidCountry()
    {
        $countryCode = $this->getCountryCode();

        $country = \XLite\Core\Database::getRepo('XLite\Model\Country')->find($countryCode);

        return $country;
    }

    /**
     * Mark list as selectable
     *
     * @return boolean
     */
    protected function isSelectable()
    {
        return true;
    }

    /**
     * Create entity
     *
     * @return \XLite\Model\AEntity
     */
    protected function createEntity()
    {
        $entity = null;

        $country = $this->getValidCountry();

        if (!$country) {
            \XLite\Core\TopMessage::addError(
                'State cannot be created with unknown country code X',
                array('code' => $this->getCountryCode())
            );

        } else {
            $entity = parent::createEntity();
            $entity->setCountry($country);
        }

        return $entity;
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        $country = $this->getValidCountry();

        return 'Add state for ' . $country->getCountry();
    }

    /**
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_TOP;
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
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isSwitchable()
    {
        return false;
    }

    /**
     * Mark list as switchable (enable / disable)
     *
     * @return boolean
     */
    protected function isRemoved()
    {
        return true;
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
        return \XLite\Core\Translation::lbl('X states have been removed', array('count' => $count));
    }

    /**
     * Get create message
     *
     * @param integer $count Count
     *
     * @return string
     */
    protected function getCreateMessage($count)
    {
        return \XLite\Core\Translation::lbl('X states have been successfully created', array('count' => $count));
    }

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' states';
    }

    /**
     * Get pager parameters
     *
     * @return array
     */
    protected function getPagerParams()
    {
        $params = parent::getPagerParams();

        $params[\XLite\View\Pager\APager::PARAM_ITEMS_PER_PAGE] = 50;

        return $params;
    }

    /**
     * Get current country code
     *
     * @return string
     */
    protected function getCountryCode()
    {
        return \XLite::getController()->getCountryCode();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    static public function getSearchParams()
    {
        return array(
            \XLite\Model\Repo\State::P_COUNTRY_CODE  => static::PARAM_COUNTRY_CODE,
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
            static::PARAM_COUNTRY_CODE  => new \XLite\Model\WidgetParam\String('Country', ''),
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

        if (empty($result->{\XLite\Model\Repo\State::P_COUNTRY_CODE})) {
            $result->{\XLite\Model\Repo\State::P_COUNTRY_CODE} = $this->getCountryCode();
        }

        $result->{\XLite\Model\Repo\State::P_ORDER_BY} = array('s.state', 'ASC');

        return $result;
    }

    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\State\Admin\Search';
    }

}
