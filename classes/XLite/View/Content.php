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
 * \XLite\View\Content
 *
 * @ListChild (list="body", zone="customer", weight="100")
 * @ListChild (list="body", zone="admin", weigth="100")
 */
class Content extends \XLite\View\AView
{
    /**
     * Title
     *
     * @return string
     */
    protected $title;

    /**
     * Chunk size
     */
    const BUFFER_SIZE = 8192;

    /**
     * Controller content displayed flag
     * 
     * @var   boolean
     */
    protected static $controllerContentDisplayed = false;

    /**
     * display
     *
     * @param string $template Template file name OPTIONAL
     *
     * @return void
     */
    public function display($template = null)
    {
        if (!static::$controllerContentDisplayed && isset(\XLite\View\Controller::$bodyContent)) {
            $this->echoContent();
            static::$controllerContentDisplayed = true;

        } else {
            parent::display($template);
        }
    }

    /**
     * Get title
     *
     * @return string
     */
    protected function getTitle()
    {
        if (!isset($this->title)) {
            $this->title = parent::getTitle();

            if (\XLite::isAdminZone()) {
                $this->title = \XLite\View\Menu\Admin\SideBox\StoreSetup::getInstance()->getTitle() ?: $this->title;
            }
        }

        return $this->title;
    }

    /**
     * getBufferSize
     *
     * @return void
     */
    protected function getOutputChunkSize()
    {
        return self::BUFFER_SIZE;
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return null;
    }

    /**
     * Echo chunk
     *
     * @param string &$chunk Text chunk to output
     *
     * @return void
     */
    protected function echoChunk(&$chunk)
    {
        \Includes\Utils\Operator::flush($chunk, false, null);
    }

    /**
     * echoContent
     *
     * @return void
     */
    protected function echoContent()
    {
        array_map(
            array($this, 'echoChunk'),
            str_split(\XLite\View\Controller::$bodyContent, $this->getOutputChunkSize())
        );
    }

    /**
     * Check - first sidebar is visible or not
     *
     * @return boolean
     */
    protected function isSidebarFirstVisible()
    {
        return \XLite\View\Controller::isSidebarFirstVisible();
    }

    /**
     * Check - second sidebar is visible or not
     *
     * @return boolean
     */
    protected function isSidebarSecondVisible()
    {
        return \XLite\View\Controller::isSidebarSecondVisible();
    }
}
