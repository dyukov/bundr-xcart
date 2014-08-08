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

return function()
{

    $setting = \XLite\Core\Database::getRepo('XLite\Model\Config')->findOneBy(
        array(
            'name'     => 'fb_like_send_button',
            'category' => 'CDev\GoSocial',
        )
    );
    if ($setting) {
        $translation = $setting->getTranslation('en');
        if ($translation) {
            $translation->setOptionName(
                'Display the Share button along with the Like button'
            );
            $translation->setOptionComment(
                'The Share button lets people add a personalized message to links before sharing on their timeline,'
                . ' in groups, or to their friends via a Facebook Message.'
            );
        }
    }

    $setting = \XLite\Core\Database::getRepo('XLite\Model\Config')->findOneBy(
        array(
            'name'     => 'fb_like_use',
            'category' => 'CDev\GoSocial',
        )
    );
    if ($setting) {
        $translation = $setting->getTranslation('en');
        if ($translation) {
            $translation->setOptionComment(
                'The Like button lets a customer share your product pages with friends on Facebook.'
                . ' The button will not be displayed if Facebook application id is not set.'
            );
        }
    }

    \XLite\Core\Database::getEM()->flush();

};
