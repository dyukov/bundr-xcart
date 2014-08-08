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

namespace XLite\View;

/**
 * Benchmark summary block
 *
 * @ListChild (list="dashboard-sidebar", weight="300", zone="admin")
 */
class BenchmarkSummary extends \XLite\View\AView
{
    /**
     * Measure (cache)
     *
     * @var array
     */
    protected $measure;

    /**
     * Return list of allowed targets
     *
     * @return array
     */
    public static function getAllowedTargets()
    {
        $list = parent::getAllowedTargets();

        $list[] = 'main';

        return $list;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    public function isVisible()
    {
        return false;
    }
    
    /**
     * Check ACL permissions
     *
     * @return boolean
     */
    protected function checkACL()
    {
        return parent::checkACL()
            && \XLite\Core\Auth::getInstance()->isPermissionAllowed(\XLite\Model\Role\Permission::ROOT_ACCESS);
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'benchmark_summary/body.tpl';
    }

    /**
     * Check - benchmark did run in past or not
     *
     * @return boolean
     */
    protected function isAlreadyMeasure()
    {
        $measure = $this->getMeasure();

        return 0 < $measure['total'];
    }

    /**
     * Get measure
     *
     * @return array
     */
    protected function getMeasure()
    {
        if (!isset($this->measure)) {
            $repo = \XLite\Core\Database::getRepo('XLite\Model\Measure');

            $this->measure = array(
                'total' => $repo->getScore(),
                'fs'    => $repo->getFilesystemScore(),
                'db'    => $repo->getDatabaseScore(),
                'cpu'   => $repo->getComputationScore(),
            );
        }

        return $this->measure;
    }

    /**
     * Get last measure date
     *
     * @return integer
     */
    protected function getLastDate()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Measure')->getLastDate();
    }

    /**
     * Get hosting score
     *
     * @return array
     */
    protected function getHostingScore()
    {
        return \XLite\Core\Marketplace::getInstance()->getHostingScore();
    }

    /**
     * Check - score is high or not
     *
     * @return boolean
     */
    protected function isHighScore()
    {
        $infelicity = 0.05;
        $measure = $this->getMeasure();

        $hostingScores = $this->getHostingScore();

        $highScore = null;
        if ($hostingScores) {
            foreach ($hostingScores as $hostingScore) {
                if (!isset($highScore) || $hostingScore['score'] > $highScore) {
                    $highScore = $hostingScore['score'];
                }
            }
        }

        return isset($highScore) && $measure && ($highScore * (1 + $infelicity)) < $measure['total'];
    }
}
