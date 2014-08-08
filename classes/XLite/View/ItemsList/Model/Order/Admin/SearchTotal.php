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

namespace XLite\View\ItemsList\Model\Order\Admin;

/**
 * Search total block (for order list page)
 *
 * @ListChild (list="pager.admin.model.table.right", weight="100", zone="admin")
 */
class SearchTotal extends \XLite\View\ItemsList\Model\Order\Admin\Search
{
    /*
     * The number of the cells from the end of table to the "Search total" cell
     */
    const SEARCH_TOTAL_CELL_NUMBER_FROM_END = 3;

    /**
     * Return list of targets allowed for this widget
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $result = parent::getAllowedTargets();

        $result[] = 'order_list';

        return $result;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'pager/model/table/parts/search_total.block.tpl';
    }

    /**
     * Return number of the search total cell (for "colspan" option)
     *
     * @return array
     */
    protected function getSearchTotalCellColspan()
    {
        $cellNumber = parent::getColumnsCount();

        if ($cellNumber) {
            $cellNumber = $cellNumber - static::SEARCH_TOTAL_CELL_NUMBER_FROM_END + 1;
        }

        return $cellNumber;
    }

    /**
     * Search total amount
     *
     * @return \Doctrine\ORM\PersistentCollection
     */
    protected function getSearchTotals()
    {
        // Get search conditions
        $name = \XLite\View\ItemsList\Model\Order\Admin\Search::getSessionCellName();

        $cnd = new \XLite\Core\CommonCell(\XLite\Core\Session::getInstance()->$name);

        $qb = \XLite\Core\Database::getRepo('\XLite\Model\Order')->defineGetSearchTotalQuery($cnd);

        $searchTotals = $qb->getResult();

        return $searchTotals;
    }

    /**
     * Get count of the search total amounts
     *
     * @return integer
     */
    protected function getSearchTotalsCount()
    {
        return count($this->getSearchTotals());
    }

    /**
     * Get count of the search total amounts
     *
     * @param integer $index Current search total index
     *
     * @return integer
     */
    protected function isNeedSearchTotalsSeparator($index)
    {
        $searchTotalsCount = $this->getSearchTotalsCount();

        return 1 < $searchTotalsCount
            && $index < $searchTotalsCount - 1;
    }

    /**
     * Get currency for the search total
     *
     * @param integer $currencyId Currency id
     *
     * @return \XLite\Model\Currency
     */
    protected function getSearchTotalCurrency($currencyId)
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Currency')
            ->findOneBy(array('currency_id' => $currencyId));
    }
}
