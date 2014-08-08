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

namespace XLite\Module\XC\WebmasterKit;

/**
 * Logger
 */
abstract class Logger extends \XLite\Logger implements \XLite\Base\IDecorator, \Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * Query data
     *
     * @var   array
     */
    protected $query;

    /**
     * Count
     *
     * @var   integer
     */
    protected $count = 0;

    /**
     * Logs a SQL statement somewhere.
     *
     * @param string $sql    The SQL to be executed.
     * @param array  $params The SQL parameters.
     * @param array  $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->query = array(
            'sql'    => $sql,
            'params' => $params,
            'start'  => microtime(true),
        );
    }

    /**
     * Mark the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
        $duration = microtime(true) - $this->query['start'];

        $params = array();
        if ($this->query['params']) {
            foreach ($this->query['params'] as $v) {
                $params[] = var_export($v, true);
            }
        }

        $this->count++;

        $this->logCustom(
            'sql',
            'Query #' . $this->count . ': ' . $this->query['sql'] . PHP_EOL
            . ($params ? 'Parameters: ' . PHP_EOL . "\t" . implode(PHP_EOL . "\t", $params) . PHP_EOL : '')
            . 'Duration: ' . round($duration, 4) . 'sec.' . PHP_EOL
            . 'Doctrine UnitOfWork size: ' . \XLite\Core\Database::getEM()->getUnitOfWork()->size(),
            true,
            4
        );

        unset($this->query);
    }

}

