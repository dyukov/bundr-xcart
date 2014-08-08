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
 * Profiles items list
 */
class Profile extends \XLite\View\ItemsList\Model\Table
{
    /**
     * List of search params for this widget (cache)
     *
     * @var array
     */
    protected $searchParams;

    /**
     * Widget param names
     */
    const PARAM_PATTERN         = 'pattern';
    const PARAM_USER_TYPE       = 'user_type';
    const PARAM_MEMBERSHIP      = 'membership';
    const PARAM_COUNTRY         = 'country';
    const PARAM_STATE           = 'state';
    const PARAM_CUSTOM_STATE    = 'customState';
    const PARAM_ADDRESS         = 'address';
    const PARAM_PHONE           = 'phone';
    const PARAM_DATE_TYPE       = 'date_type';
    const PARAM_DATE_PERIOD     = 'date_period';
    const PARAM_DATE_RANGE      = 'dateRange';

    /**
     * Allowed sort criterions
     */
    const SORT_BY_MODE_LOGIN        = 'p.login';
    const SORT_BY_MODE_NAME         = 'fullname';
    const SORT_BY_MODE_ACCESS_LEVEL = 'p.access_level';
    const SORT_BY_MODE_CREATED      = 'p.added';
    const SORT_BY_MODE_LAST_LOGIN   = 'p.last_login';

    /**
     * Define and set widget attributes; initialize widget
     *
     * @param array $params Widget params OPTIONAL
     *
     * @return void
     */
    public function __construct(array $params = array())
    {
        $this->sortByModes += array(
            static::SORT_BY_MODE_LOGIN          => 'Login/Email',
            static::SORT_BY_MODE_NAME           => 'Name',
            static::SORT_BY_MODE_ACCESS_LEVEL   => 'Access level',
            static::SORT_BY_MODE_CREATED        => 'Created',
            static::SORT_BY_MODE_LAST_LOGIN     => 'Last login',
        );

        parent::__construct($params);
    }

    /**
     * Define columns structure
     *
     * @return array
     */
    protected function defineColumns()
    {
        return array(
            'login' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Login/E-mail'),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_LINK     => 'profile',
                static::COLUMN_SORT     => static::SORT_BY_MODE_LOGIN,
                static::COLUMN_ORDERBY  => 100,
            ),
            'name' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Name'),
                static::COLUMN_NO_WRAP  => true,
                static::COLUMN_LINK     => 'address_book',
                static::COLUMN_MAIN     => true,
                static::COLUMN_SORT     => static::SORT_BY_MODE_NAME,
                static::COLUMN_ORDERBY  => 200,
            ),
            'access_level' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Access level'),
                static::COLUMN_SORT     => static::SORT_BY_MODE_ACCESS_LEVEL,
                static::COLUMN_ORDERBY  => 300,
            ),
            'orders_count' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Orders'),
                static::COLUMN_TEMPLATE => 'profiles/parts/cell/orders.tpl',
                static::COLUMN_ORDERBY  => 400,
            ),
            'added' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Created'),
                static::COLUMN_SORT     => static::SORT_BY_MODE_CREATED,
                static::COLUMN_ORDERBY  => 500,
            ),
            'last_login' => array(
                static::COLUMN_NAME     => \XLite\Core\Translation::lbl('Last login'),
                static::COLUMN_SORT     => static::SORT_BY_MODE_LAST_LOGIN,
                static::COLUMN_ORDERBY  => 600,
            ),
        );
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     */
    protected function getSortByModeDefault()
    {
        return static::SORT_BY_MODE_LAST_LOGIN;
    }

    /**
     * getSortOrderModeDefault
     *
     * @return string
     */
    protected function getSortOrderModeDefault()
    {
        return static::SORT_ORDER_DESC;
    }

    /**
     * Define repository name
     *
     * @return string
     */
    protected function defineRepositoryName()
    {
        return 'XLite\Model\Profile';
    }

    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'profiles/style.css';

        return $list;
    }

    /**
     * Get create entity URL
     *
     * @return string
     */
    protected function getCreateURL()
    {
        return \XLite\Core\Converter::buildUrl('profile', null, array('mode' => 'register'));
    }

    /**
     * Get create button label
     *
     * @return string
     */
    protected function getCreateButtonLabel()
    {
        return 'Add user';
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
     * Creation button position
     *
     * @return integer
     */
    protected function isCreation()
    {
        return static::CREATE_INLINE_TOP;
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
        // Admin user cannot remove own account
        return parent::isAllowEntityRemove($entity)
            && \XLite\Core\Auth::getInstance()->getProfile()->getProfileId() !== $entity->getProfileId();
    }

    // }}}

    /**
     * Get container class
     *
     * @return string
     */
    protected function getContainerClass()
    {
        return parent::getContainerClass() . ' profiles';
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
        $class = parent::getColumnClass($column, $entity);

        if ('access_level' == $column[static::COLUMN_CODE] && $entity && $entity->getAnonymous()) {
            $class = trim($class . ' anonymous');
        }

        return $class;
    }
    /**
     * Get panel class
     *
     * @return \XLite\View\Base\FormStickyPanel
     */
    protected function getPanelClass()
    {
        return 'XLite\View\StickyPanel\ItemsList\Profile';
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
            \XLite\Model\Repo\Profile::SEARCH_PATTERN      => static::PARAM_PATTERN,
            \XLite\Model\Repo\Profile::SEARCH_USER_TYPE    => static::PARAM_USER_TYPE,
            \XLite\Model\Repo\Profile::SEARCH_MEMBERSHIP   => static::PARAM_MEMBERSHIP,
            \XLite\Model\Repo\Profile::SEARCH_COUNTRY      => static::PARAM_COUNTRY,
            \XLite\Model\Repo\Profile::SEARCH_STATE        => static::PARAM_STATE,
            \XLite\Model\Repo\Profile::SEARCH_CUSTOM_STATE => static::PARAM_CUSTOM_STATE,
            \XLite\Model\Repo\Profile::SEARCH_ADDRESS      => static::PARAM_ADDRESS,
            \XLite\Model\Repo\Profile::SEARCH_PHONE        => static::PARAM_PHONE,
            \XLite\Model\Repo\Profile::SEARCH_DATE_TYPE    => static::PARAM_DATE_TYPE,
            \XLite\Model\Repo\Profile::SEARCH_DATE_PERIOD  => static::PARAM_DATE_PERIOD,
            \XLite\Model\Repo\Profile::SEARCH_DATE_RANGE   => static::PARAM_DATE_RANGE,
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
            static::PARAM_PATTERN         => new \XLite\Model\WidgetParam\String('Pattern', ''),
            static::PARAM_USER_TYPE       => new \XLite\Model\WidgetParam\Set('Type', '', false, array('', 'A', 'C')),
            static::PARAM_MEMBERSHIP      => new \XLite\Model\WidgetParam\String('Membership', ''),
            static::PARAM_COUNTRY         => new \XLite\Model\WidgetParam\String('Country', ''),
            static::PARAM_STATE           => new \XLite\Model\WidgetParam\Int('State', null),
            static::PARAM_CUSTOM_STATE    => new \XLite\Model\WidgetParam\String('State name (custom)', ''),
            static::PARAM_ADDRESS         => new \XLite\Model\WidgetParam\String('Address', ''),
            static::PARAM_PHONE           => new \XLite\Model\WidgetParam\String('Phone', ''),
            static::PARAM_DATE_TYPE       => new \XLite\Model\WidgetParam\Set('Date type', '', false, array('', 'R', 'L')),
            static::PARAM_DATE_PERIOD     => new \XLite\Model\WidgetParam\Set('Date period', '', false, array('', 'M', 'W', 'D', 'C')),
            static::PARAM_DATE_RANGE      => new \XLite\Model\WidgetParam\String('Date range', null),
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

        // We initialize structure to define order (field and sort direction) in search query.
        $result->{\XLite\Model\Repo\Profile::SEARCH_ORDERBY} = $this->getOrderBy();

        foreach (static::getSearchParams() as $modelParam => $requestParam) {
            $paramValue = $this->getParam($requestParam);

            if ('' !== $paramValue && 0 !== $paramValue) {
                $result->$modelParam = $paramValue;
            }
        }
        $result->{\XLite\Model\Repo\Profile::SEARCH_ONLY_REAL} = true;

        return $result;
    }

    // }}}

    /**
     * Preprocess added
     *
     * @param integer              $date   Date
     * @param array                $column Column data
     * @param \XLite\Model\Profile $entity Profile
     *
     * @return string
     */
    protected function preprocessAdded($date, array $column, \XLite\Model\Profile $entity)
    {
        return $date
            ? \XLite\Core\Converter::getInstance()->formatTime($date)
            : static::t('Unknown');
    }

    /**
     * Preprocess last login
     *
     * @param integer              $date   Date
     * @param array                $column Column data
     * @param \XLite\Model\Profile $entity Profile
     *
     * @return string
     */
    protected function preprocessLastLogin($date, array $column, \XLite\Model\Profile $entity)
    {
        return $date
            ? \XLite\Core\Converter::getInstance()->formatTime($date)
            : static::t('Never');
    }

    /**
     * Preprocess access level
     *
     * @param integer              $accessLevel Access level
     * @param array                $column      Column data
     * @param \XLite\Model\Profile $entity      Profile
     *
     * @return string
     */
    protected function preprocessAccessLevel($accessLevel, array $column, \XLite\Model\Profile $entity)
    {
        if (0 == $accessLevel) {
            $result = $entity->getAnonymous()
                ? static::t('Anonymous')
                : static::t('Customer');

            if (
                $entity->getMembership()
                || $entity->getPendingMembership()
            ) {
                $result .= ' (';
            }

            if ($entity->getMembership()) {
                $result .= $entity->getMembership()->getName();
            }

            if ($entity->getPendingMembership()) {
                if ($entity->getMembership()) {
                    $result .= ', ';
                }

                $result .= static::t('requested for') . ' '
                    . $entity->getPendingMembership()->getName();
            }

            if (
                $entity->getMembership()
                || $entity->getPendingMembership()
            ) {
                $result .= ')';
            }

        } else {
            $result = static::t('Administrator');
        }

        return $result;
    }
}
