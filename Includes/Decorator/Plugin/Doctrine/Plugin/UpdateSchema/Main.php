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

namespace Includes\Decorator\Plugin\Doctrine\Plugin\UpdateSchema;

/**
 * Main 
 *
 */
class Main extends \Includes\Decorator\Plugin\Doctrine\Plugin\APlugin
{
    /**
     * Maximum number of requests loaded into DB per step
     */
    const MAX_QUERIES_PER_PASS = 50;

    /**
     * Step TTL
     */
    const STEP_TTL = 20;


    /**
     * Execute certain hook handle
     *
     * @return void
     */
    public function executeHookHandler()
    {
        $schema = \Includes\Decorator\Plugin\Doctrine\Utils\DBSchemaManager::getDBSchema();

        if ($schema) {

            while (true) {

                $queries = array();

                foreach ($schema as $k => $query) {
                    $queries[] = $query;
                    unset($schema[$k]);
                }

                if ($queries) {
                    \XLite\Core\Database::getInstance()->executeQueries($queries);
                }

                if (empty($schema) || \Includes\Decorator\Utils\CacheManager::isTimeExceeds(static::STEP_TTL)) {
                    break;
                }
            }

            \Includes\Decorator\Plugin\Doctrine\Utils\DBSchemaManager::updateDBSchema($schema);
        }
    }
}
