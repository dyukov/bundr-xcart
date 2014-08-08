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

namespace XLite\Module\XC\Reviews\Controller\Admin;

/**
 * Reviews controller
 *
 */
class Reviews extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Products reviews';
    }

    /**
     * Return null since it's common reviews list
     *
     * @return integer
     */
    public function getProductId()
    {
        return 0;
    }

    // {{{ Search

    /**
     * Get search condition parameter by name
     *
     * @param string $paramName Parameter name
     *
     * @return mixed
     */
    public function getCondition($paramName)
    {
        $searchParams = $this->getConditions();

        return isset($searchParams[$paramName])
            ? $searchParams[$paramName]
            : null;
    }

    /**
     * Get date condition parameter (start or end)
     *
     * @param boolean $start Start date flag, otherwise - end date  OPTIONAL
     *
     * @return mixed
     */
    public function getDateCondition($start = true)
    {
        $dates = $this->getCondition(\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_ADDITION_DATE);
        $n = (true === $start) ? 0 : 1;

        $date = isset($dates[$n]) ? $dates[$n] : LC_START_TIME;
        if ($start && LC_START_TIME == $date) {
            $date -= 86400 * 30;
        }

        return $date;
    }

    /**
     * Get date value for search params
     *
     * @param string  $fieldName Field name (prefix)
     * @param boolean $isEndDate End date flag OPTIONAL
     *
     * @return integer
     */
    public function getDateValue($fieldName, $isEndDate = false)
    {
        $dateValue = \XLite\Core\Request::getInstance()->$fieldName;

        if (isset($dateValue)) {
            $timeValue = $isEndDate ? '23:59:59' : '0:0:0';
            $dateValue = intval(strtotime($dateValue . ' ' . $timeValue));
        } else {
            $dateValue = time();
        }

        return $dateValue;
    }

    /**
     * Update list
     *
     * @return void
     */
    protected function doActionUpdate()
    {
        $list = new \XLite\Module\XC\Reviews\View\ItemsList\Model\Review;
        $list->processQuick();
    }

    /**
     * Save search conditions
     *
     * @return void
     */
    protected function doActionSearch()
    {
        $cellName = \XLite\Module\XC\Reviews\View\ItemsList\Model\Review::getSessionCellName();

        \XLite\Core\Session::getInstance()->$cellName = $this->getSearchParams();
    }

    /**
     * Return search parameters
     *
     * @return array
     */
    protected function getSearchParams()
    {
        // Prepare dates

        $this->startDate = $this->getDateValue('startDate');
        $this->endDate   = $this->getDateValue('endDate', true);

        if (
            0 === $this->startDate
            || 0 === $this->endDate
            || $this->startDate > $this->endDate
        ) {
            $date = getdate(time());

            $this->startDate = mktime(0, 0, 0, $date['mon'], 1, $date['year']);
            $this->endDate   = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
        }

        $searchParams = $this->getConditions();

        foreach (\XLite\Module\XC\Reviews\View\ItemsList\Model\Review::getSearchParams() as $requestParam) {
            if (\XLite\Module\XC\Reviews\Model\Repo\Review::SEARCH_ADDITION_DATE === $requestParam) {
                $searchParams[$requestParam] = array($this->startDate, $this->endDate);
            } elseif (isset(\XLite\Core\Request::getInstance()->$requestParam)) {
                $searchParams[$requestParam] = \XLite\Core\Request::getInstance()->$requestParam;
            }
        }

        return $searchParams;
    }

    /**
     * Get search conditions
     *
     * @return array
     */
    protected function getConditions()
    {
        $cellName = \XLite\Module\XC\Reviews\View\ItemsList\Model\Review::getSessionCellName();

        $searchParams = \XLite\Core\Session::getInstance()->$cellName;

        if (!is_array($searchParams)) {
            $searchParams = array();

            $now = time();
            $startDate = $now - 2592000; // One month

            $searchParams['dateRange'] =  date('Y-m-d', $startDate) . ' ~ ' . date('Y-m-d', $now);
            \XLite\Core\Session::getInstance()->$cellName =  $searchParams;
        }

        return $searchParams;
    }

    // }}}
}
