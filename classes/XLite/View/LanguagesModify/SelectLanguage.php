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

namespace XLite\View\LanguagesModify;

/**
 * Select language dialog
 */
class SelectLanguage extends \XLite\View\AView
{
    /**
     * Translate language (cache)
     *
     * @var \XLite\Model\Language
     */
    protected $translateLanguage = null;

    /**
     * Get added languages
     *
     * @return array
     */
    public function getAddedLanguages()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Language')->findAddedLanguages();
    }

    /**
     * Check - is interface language or not
     *
     * @param \XLite\Model\Language $language ____param_comment____
     *
     * @return void
     */
    public function isInterfaceLanguage(\XLite\Model\Language $language)
    {
        return static::getDefaultLanguage() == $language->code;
    }

    /**
     * Check - is translate language or not
     *
     * @param \XLite\Model\Language $language Language
     *
     * @return boolean
     */
    public function isTranslateLanguage(\XLite\Model\Language $language)
    {
        return $this->getTranslatedLanguage()
            && $this->getTranslatedLanguage()->code == $language->code;
    }

    /**
     * Check - specified language can been selected or not
     *
     * @param \XLite\Model\Language $language Language
     *
     * @return boolean
     */
    public function canSelect(\XLite\Model\Language $language)
    {
        return $language->code !== static::getDefaultLanguage()
            && (!$this->getTranslatedLanguage() || $language->code != $this->getTranslatedLanguage()->code);
    }

    /**
     * Check - specified language can been deleted or not
     *
     * @param \XLite\Model\Language $language Language
     *
     * @return boolean
     */
    public function canDelete(\XLite\Model\Language $language)
    {
        return $language->code !== static::getDefaultLanguage();
    }

    /**
     * Get inactive languages
     *
     * @return array
     */
    public function getInactiveLanguages()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Language')
            ->findInactiveLanguages();
    }


    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'languages/select_language.tpl';
    }

    /**
     * Get translated language
     *
     * @return \XLite\Model\Language|boolean
     */
    protected function getTranslatedLanguage()
    {
        if (!isset($this->translateLanguage)) {
            if (\XLite\Core\Request::getInstance()->language) {
                $this->translateLanguage = \XLite\Core\Database::getRepo('\XLite\Model\Language')->findOneByCode(
                    \XLite\Core\Request::getInstance()->language
                );
                if (!$this->translateLanguage || !$this->translateLanguage->added) {
                    $this->translateLanguage = false;
                }
            }
        }

        return $this->translateLanguage;
    }

    /**
     * Check widget visibility
     *
     * @return void
     */
    protected function isVisible()
    {
        return parent::isVisible()
            && $this->getInactiveLanguages();
    }
}
