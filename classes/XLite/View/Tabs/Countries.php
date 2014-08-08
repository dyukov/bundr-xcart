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

namespace XLite\View\Tabs;

/**
 * Tabs related to countries, states and zones
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class Countries extends \XLite\View\Tabs\ATabs
{
    /**
     * Description of tabs related to shipping settings and their targets
     *
     * @var array
     */
    protected $tabs = array(
        'countries' => array(
            'title'    => 'Countries',
            'template' => 'countries.tpl',
        ),
        'states' => array(
            'title'    => 'States',
            'template' => 'states/body.tpl',
        ),
        'zones' => array(
            'title' => 'Zones',
            'template' => 'zones/body.tpl',
            'jsFiles' => 'zones/details/controller.js',
            'cssFiles' => 'zones/style.css',
        ),
    );

    /**
     * Zone
     *
     * @var \XLite\Model\Zone
     */
    protected $zone;

    /**
     * Zones
     *
     * @var array
     */
    protected $zones;

    /**
     * Check if zone details page should be displayed
     *
     * @return boolean
     */
    public function isDisplayZoneDetails()
    {
        return 'add' == \XLite\Core\Request::getInstance()->mode
            || isset(\XLite\Core\Request::getInstance()->zoneid);
    }

    /**
     * Return zone
     *
     * @return \XLite\Model\Zone
     */
    public function getZone()
    {
        if (!isset($this->zone)) {
            if (isset(\XLite\Core\Request::getInstance()->zoneid)) {
                $zone = \XLite\Core\Database::getRepo('XLite\Model\Zone')
                    ->find(\XLite\Core\Request::getInstance()->zoneid);

                if (!isset($zone)) {
                    \XLite\Core\TopMessage::addError('Requested zone does not exists');

                } else {
                    $this->zone = $zone;
                }

            } else {
                $this->zone = new \XLite\Model\Zone();
            }
        }

        return $this->zone;
    }

    /**
     * getShippingZones
     *
     * @return array
     */
    public function getShippingZones()
    {
        if (!isset($this->zones)) {
            $this->zones = \XLite\Core\Database::getRepo('XLite\Model\Zone')->findAllZones();
        }

        return $this->zones;
    }

    /**
     * isZonesDefined
     *
     * @return boolean
     */
    public function isZonesDefined()
    {
        return (count($this->getShippingZones()) > 1);
    }

    /**
     * Disable city masks field in the interface
     *
     * @return boolean
     */
    protected function isCityMasksEditEnabled()
    {
        return false;
    }

    /**
     * Disable address masks field in the interface
     *
     * @return boolean
     */
    protected function isAddressMasksEditEnabled()
    {
        return false;
    }
}
