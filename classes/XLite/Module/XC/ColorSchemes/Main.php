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

namespace XLite\Module\XC\ColorSchemes;

/**
 * Module description
 *
 * @package XLite
 */
abstract class Main extends \XLite\Module\AModule
{
    /**
     * Author name
     *
     * @return string
     */
    public static function getAuthorName()
    {
        return 'X-Cart team';
    }

    /**
     * Module name
     *
     * @return string
     */
    public static function getModuleName()
    {
        return 'Color Schemes';
    }

    /**
     * Get module major version
     *
     * @return string
     */
    public static function getMajorVersion()
    {
        return '5.1';
    }

    /**
     * Module version
     *
     * @return string
     */
    public static function getMinorVersion()
    {
        return '3';
    }

    /**
     * Module description
     *
     * @return string
     */
    public static function getDescription()
    {
        return 'This module adds three new color schemes to the base X-Cart 5 design theme.';
    }

    /**
     * Determines if we need to show settings form link
     *
     * @return boolean
     */
    public static function showSettingsForm()
	{
		return false;
	}

    /**
     * Construct the CSS file name of the selected color scheme
     *
     * @return string
     */
	public static function getColorSchemeCSS()
    {
		return 'modules/XC/ColorSchemes/' . \XLite\Core\Config::getInstance()->XC->ColorSchemes->skin_name . '/style.css';
	}

    /**
     * Construct the Less file name of the selected color scheme
     *
     * @return string
     */
	public static function getColorSchemeLess()
    {
		return 'modules/XC/ColorSchemes/' . \XLite\Core\Config::getInstance()->XC->ColorSchemes->skin_name . '/style.less';
	}

    /**
     * Defines if the current skin is the default one
     *
     * @return boolean
     */
    public static function isDefaultColorScheme()
    {
        $skinName = \XLite\Core\Config::getInstance()->XC->ColorSchemes->skin_name;

        return 'Default' === $skinName || '' === $skinName;
    }
}
