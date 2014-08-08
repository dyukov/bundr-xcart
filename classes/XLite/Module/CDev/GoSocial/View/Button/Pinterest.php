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

namespace XLite\Module\CDev\GoSocial\View\Button;

/**
 * Pinterest button
 *
 * @ListChild (list="buttons.share", weight="400")
 */
class Pinterest extends \XLite\View\AView
{
    /**
     * Button URL
     */
    const BUTTON_URL = '//pinterest.com/pin/create/button/?';

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/CDev/GoSocial/button/pinterest.tpl';
    }

    /**
     * Get button attributes
     *
     * @return array
     */
    protected function getButtonAttributes()
    {
        return array(
            'count-layout' => 'horizontal',
        );
    }

    /**
     * Get button URL (query  part)
     *
     * @return array
     */
    protected function getButtonURL()
    {
        $query = array();
        foreach ($this->getButtonURLQuery() as $name => $value) {
            $query[] = $name . '=' . urlencode($value);
        }

        return static::BUTTON_URL . implode('&amp;', $query);
    }

    /**
     * Get button URL (query  part)
     *
     * @return array
     */
    protected function getButtonURLQuery()
    {
        $image = $this->getModelObject()->getImage();

        return array(
            'url'         => \XLite::getInstance()->getShopURL($this->getURL()),
            'media'       => isset($image) ? $image->getFrontURL() : null,
            'description' => $this->getModelObject()->getName(),
        );
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        $image = $this->getModelObject()->getImage();

        return parent::isVisible()
            && isset($image)
            && $image->isExists()
            && \XLite\Core\Config::getInstance()->CDev->GoSocial->pinterest_use;
    }

}
