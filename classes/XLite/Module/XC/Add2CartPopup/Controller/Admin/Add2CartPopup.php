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

namespace XLite\Module\XC\Add2CartPopup\Controller\Admin;

/**
 * Add2CartPopup settings page controller
 */
class Add2CartPopup extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Return the current page title (for the content area)
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Add to Cart Popup module settings';
    }

    /**
     * Gets the Add2CartPopup module id
     *
     * @return integer
     */
    public function getModuleId()
    {
        return \XLite\Core\Database::getRepo('XLite\Model\Module')->findOneBy(array(
            'author'            => 'XC',
            'name'              => 'Add2CartPopup',
            'fromMarketplace'   => false,
        ))->getModuleID();
    }

    /**
     * Do action 'Update'
     *
     * @return void
     */
    public function doActionUpdate()
    {
        $isUpdated = false;

        $postedData = \XLite\Core\Request::getInstance()->getData();

        $a2cpEnableForDropping = !empty($postedData['a2cp_enable_for_dropping']);

        if ($a2cpEnableForDropping != \XLite\Core\Config::getInstance()->XC->Add2CartPopup->a2cp_enable_for_dropping) {
            \XLite\Core\Database::getRepo('\XLite\Model\Config')->createOption(
                array(
                    'category' => 'XC\\Add2CartPopup',
                    'name'     => 'a2cp_enable_for_dropping',
                    'value'    => $a2cpEnableForDropping ? 'Y' : 'N',
                )
            );
            $isUpdated = true;
        }

        $options = array();

        if (isset($postedData['data']) && is_array($postedData['data'])) {
            foreach ($postedData['data'] as $code => $info) {
                $options[$code] = array(
                    'position' => $info['position'],
                    'enabled'  => $info['enabled'],
                );
            }

            \XLite\Core\Database::getRepo('\XLite\Model\Config')->createOption(
                array(
                    'category' => 'XC\\Add2CartPopup',
                    'name'     => 'product_sources',
                    'value'    => serialize($options),
                )
            );

            $isUpdated = true;
        }

        if ($isUpdated) {
            \XLite\Core\TopMessage::addInfo('Add to Cart popup module settings have been updated.');
        }
    }
}
