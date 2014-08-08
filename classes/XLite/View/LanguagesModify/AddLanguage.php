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
 * Add (activate) language dialog
 */
class AddLanguage extends \XLite\View\AView
{
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
        return 'languages/add_language.tpl';
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

    /**
     * Get related module page URL
     *
     * @param \XLite\Model\Language $entity Language object
     *
     * @return string
     */
    protected function getModulePageURL($entity)
    {
        $url = null;

        $module = $entity->getModule();

        if (!empty($module) && preg_match('/(\w+)\\\\(\w+)/', $module, $match)) {

            $params = array('clearCnd' => 1);
            $limit = \XLite\View\Pager\Admin\Module\Manage::getInstance()->getItemsPerPage();
            $pageId = \XLite\Core\Database::getRepo('XLite\Model\Module')->getModulePageId($match[1], $match[2], $limit);

            if (0 < $pageId) {
                $params['page'] = $pageId;
            }

            $url = $this->buildURL('addons_list_installed', '', $params) . '#' . $match[2];
        }

        return $url;
    }
}
