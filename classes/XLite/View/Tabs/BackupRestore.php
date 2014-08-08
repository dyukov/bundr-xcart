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

namespace XLite\View\Tabs;

/**
 * Tabs related to Backup/Restore section
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class BackupRestore extends \XLite\View\Tabs\ATabs
{
    /**
     * Description of tabs related to Backup/Restore section and their targets
     *
     * @var array
     */
    protected $tabs = array(
        'db_backup' => array(
            'title'    => 'Backup database',
            'template' => 'db/backup.tpl',
        ),
        'db_restore' => array(
            'title'    => 'Restore database',
            'template' => 'db/restore.tpl',
        ),
    );


    /**
     * File size limit
     *
     * @return string
     */
    public function getUploadMaxFilesize()
    {
        return ini_get('upload_max_filesize');
    }
}
