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

namespace XLite\Module\CDev\Egoods\View;

/**
 * Browse server file system
 */
abstract class BrowseServer extends \XLite\View\BrowseServer implements \XLite\Base\IDecorator
{
    /**
     * Return files entries structure
     * type      - 'catalog' or 'file' value
     * extension - extension of file entry. CSS class will be added according this parameter
     * name      - name of entry (catalog/file) inside the current catalog.
     *
     * Catalog entries go first in the entries list
     *
     * @return array
     */
    protected function getFSEntries()
    {
        $list = parent::getFSEntries();

        foreach ($list as &$row) {
            if (preg_match('/\.[a-f0-9]{32}$/Ss', $row['name'])) {
                $row['name'] = substr($row['name'], 0, -33);
                $row['egoods'] = true;
            }
        }

        return $list;
    }

    /**
     * Get file entry class
     *
     * @param array $entry Entry
     *
     * @return string
     */
    protected function getItemClass(array $entry)
    {
        return parent::getItemClass($entry)
            . (isset($entry['egoods']) && $entry['egoods'] ? ' egood-entry' : '');
    }
}

