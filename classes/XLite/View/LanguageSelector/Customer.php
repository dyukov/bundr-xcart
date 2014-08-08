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

namespace XLite\View\LanguageSelector;

/**
 * Language selector (customer)
 *
 *
 * @ListChild (list="layout.header.bar.links.newby", weight="999999", zone="customer")
 * @ListChild (list="layout.header.bar.links.logged", weight="999999", zone="customer")
 * @ListChild (list="header.language.menu", weight="100", zone="customer")
 */
class Customer extends \XLite\View\LanguageSelector\ALanguageSelector
{
    /**
     * Register CSS files
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        $list[] = $this->getDir() . '/style.css';

        return $list;
    }

    /**
     * Check if language is active
     *
     * @param \XLite\Model\Language $language Language to check
     *
     * @return boolean
     */
    protected function isActiveLanguage(\XLite\Model\Language $language)
    {
        return !$this->isLanguageSelected($language);
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return !$this->isCheckoutLayout() && 0 < count($this->getActiveLanguages());
    }

}
