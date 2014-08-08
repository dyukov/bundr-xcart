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

namespace XLite\Controller\Admin;

/**
 * Performance
 */
class CssJsPerformance extends \XLite\Controller\Admin\Settings
{
    /**
     * Page
     *
     * @var string
     */
    public $page = self::PERFORMANCE_PAGE;

    /**
     * Get tab names
     *
     * @return array
     */
    public function getPages()
    {
        $list = parent::getPages();
        $list[static::PERFORMANCE_PAGE] = 'Look & Feel';

        return $list;
    }

    /**
     * Clean aggregation cache directory
     *
     * @return void
     */
    public function doActionCleanAggregationCache()
    {
        \Includes\Utils\FileManager::unlinkRecursive(LC_DIR_CACHE_RESOURCES);

        \Less_Cache::SetCacheDir(LC_DIR_DATACACHE);
        \Less_Cache::CleanCache();

        \XLite\Core\TopMessage::addInfo('Aggregation cache has been cleaned');
    }

    /**
     * Clean view cache
     *
     * @return void
     */
    public function doActionCleanViewCache()
    {
        \XLite\Core\WidgetCache::getInstance()->deleteAll();

        \XLite\Core\TopMessage::addInfo('Widgets cache has been cleaned');
    }

    /**
     * Perform some actions before redirect
     *
     * FIXME: check. Action should not be an optional param
     *
     * @param string|null $action Performed action OPTIONAL
     *
     * @return void
     */
    protected function actionPostprocess($action = null)
    {
        parent::actionPostprocess($action);

        $this->setReturnURL(
            $this->buildURL('css_js_performance')
        );
    }
}
