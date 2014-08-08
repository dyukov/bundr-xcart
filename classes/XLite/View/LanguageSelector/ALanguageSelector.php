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
 * Abstract language selector
 */
abstract class ALanguageSelector extends \XLite\View\AView
{
    /**
     * Return widget directory
     *
     * @return string
     */
    protected function getDir()
    {
        return 'language_selector';
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return $this->getDir() . '/body.tpl';
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     */
    protected function isVisible()
    {
        return 1 < count($this->getActiveLanguages());
    }

    /**
     * Return list of all active languages
     *
     * @return array
     */
    protected function getActiveLanguages()
    {
        $list = array();

        foreach (\XLite\Core\Database::getRepo('\XLite\Model\Language')->findActiveLanguages() as $language) {
            if ($this->isActiveLanguage($language)) {
                $list[] = $language;
            }
        }

        return $list;
    }

    /**
     * Link to change language
     *
     * @param \XLite\Model\Language $language Language to set
     *
     * @return string
     */
    protected function getChangeLanguageLink(\XLite\Model\Language $language)
    {
        return $this->buildURL(
            $this->getTarget(),
            'change_language',
            array(
                'language' => $language->getCode(),
            ) + $this->getAllParams(),
            false
        );
    }


    /**
     * Check if language is selected
     *
     * @param \XLite\Model\Language $language Language to check
     *
     * @return boolean
     */
    protected function isLanguageSelected(\XLite\Model\Language $language)
    {
        return $language->getCode() === \XLite\Core\Session::getInstance()->getLanguage()->getCode();
    }

    /**
     * Check if language is selected
     *
     * @param \XLite\Model\Language $language Language to check
     *
     * @return boolean
     */
    protected function isActiveLanguage(\XLite\Model\Language $language)
    {
        return true;
    }
}
