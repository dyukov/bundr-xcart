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
 * Tabs related to shipping settings
 *
 * @ListChild (list="admin.center", zone="admin")
 */
class ShippingSettings extends \XLite\View\Tabs\ATabs
{
    /**
     * Description of tabs related to shipping settings and their targets
     *
     * @var array
     */
    protected $tabs = array(
        'shipping_methods' => array(
            'title'    => 'Methods',
            'template' => 'shipping/methods.tpl',
            'cssFiles' => 'shipping/style.css',
        ),
        'shipping_rates' => array(
            'title'    => 'Rates',
            'template' => 'shipping/charges.tpl',
            'cssFiles' => 'shipping/style.css',
        ),
    );

    /**
     * Markups
     *
     * @var array
     */
    protected $markups;

    /**
     * Zones
     *
     * @var array
     */
    protected $zones;

    /**
     * Returns a list of shipping processors
     *
     * @return array
     */
    public function getShippingProcessors()
    {
        $list = \XLite\Model\Shipping::getInstance()->getProcessors();

        uasort($list, array($this, 'sortProcessors'));

        return $list;
    }

    /**
     * Sort out processors array
     *
     * @param \XLite\Model\Shipping\Processor\AProcessor $a First processor
     * @param \XLite\Model\Shipping\Processor\AProcessor $b Second processor
     *
     * @return integer
     */
    public function sortProcessors($a, $b)
    {
        $aId = $a->getProcessorId();
        $bId = $b->getProcessorId();

        if ('offline' == $aId) {
            $result = -1;

        } elseif ('offline' == $bId) {
            $result = 1;

        } else {
            $result = ($aId < $bId ? -1 : ($aId > $bId ? 1 : 0));
        }

        return $result;
    }

    /**
     * Returns a list of shipping methods
     *
     * @return array
     */
    public function getShippingMethods()
    {
        return \XLite\Model\Shipping::getInstance()->getShippingMethods();
    }

    /**
     * hasShippingMarkups
     *
     * @return void
     */
    public function hasShippingMarkups()
    {
        return count($this->getShippingMarkups()) > 0;
    }

    /**
     * getShippingMarkups
     *
     * @return void
     */
    public function getShippingMarkups()
    {
        $postedData = \XLite\Core\Request::getInstance()->getData();

        // Initialize zoneId and methodId
        $zoneId = $methodId = null;

        // Get zoneId from the request data
        if (isset($postedData['zoneid']) && strlen($postedData['zoneid']) > 0) {
            $zoneId = intval($postedData['zoneid']);
        }

        // Get methodId from the request data
        if (isset($postedData['methodid']) && strlen($postedData['methodid']) > 0) {
            $methodId = intval($postedData['methodid']);
        }

        // Generate key for markups storage
        $key = md5(sprintf('%d-%d', isset($zoneId) ? $zoneId : -1, isset($methodId) ? $methodId : -1));

        // Check if markups for pair zone/method are already calculated
        if (!isset($this->markups[$key])) {

            // Get markups
            $markups = \XLite\Core\Database::getRepo('XLite\Model\Shipping\Markup')
                ->findMarkupsByZoneAndMethod($zoneId, $methodId);

            $this->markups[$key] = $markups;
        }

        return $this->markups[$key];
    }

    /**
     * getPreparedShippingMarkups
     *
     * @return void
     */
    public function getPreparedShippingMarkups()
    {
        return $this->prepareMarkups($this->getShippingMarkups());
    }

    /**
     * Service method for usage in the markups list template
     * Returns true if current markup number is lesser than count of markups of current method
     *
     * @param integer $id    Current index of markup
     * @param array   $array Array of markups
     *
     * @return boolean
     */
    public function isShowMarkupsSeparator($id, array $array)
    {
        return (count($array) - 1 > $id);
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
     * Prepares markups array for displaying on admin page. Result array has the following format:
     *
     * array (
     *    0 => array (
     *       'zone'    => \XLite\Model\Zone,
     *       'methods' => array (
     *          0 => array (
     *             'method'  => \XLite\Model\Shipping\Method,
     *             'markups' => array (
     *                0 => \XLite\Model\Shipping\Markup,
     *                1 => ...
     *             )
     *          ),
     *          1 => ...
     *       ),
     *    1 => ...
     *    )
     * )
     *
     * @param mixed $markups ____param_comment____
     *
     * @return void
     */
    protected function prepareMarkups($markups)
    {
        $result = array();
        $zones = $this->getShippingZones();
        $methods = array();

        foreach ($markups as $markup) {
            if (!isset($methods[$markup->getShippingMethod()->getMethodId()])) {
                $methods[$markup->getShippingMethod()->getMethodId()] = $markup->getShippingMethod();
            }
        }

        foreach ($zones as $zone) {

            $resultZone = array(
                'zone'    => $zone,
                'methods' => array()
            );

            foreach ($methods as $method) {

                $resultMethod = array(
                    'method'  => $method,
                    'markups' => array()
                );

                foreach ($markups as $markup) {

                    if (
                        $markup->getZone()->getZoneId() == $zone->getZoneId()
                        && $markup->getShippingMethod()->getMethodId() == $method->getMethodId()
                    ) {
                        $resultMethod['markups'][] = $markup;
                    }
                }

                if (!empty($resultMethod['markups'])) {
                    $resultZone['methods'][] = $resultMethod;
                }
            }

            if (!empty($resultZone['methods'])) {
                $result[] = $resultZone;
            }
        }

        return $result;
    }

    /**
     * Get processor methods
     *
     * @param \XLite\Model\Shipping\Processor\AProcessor $processor Processor
     *
     * @return \XLite\Model\Shipping\Method
     */
    protected function getProcessorMethods(\XLite\Model\Shipping\Processor\AProcessor $processor)
    {
        return $processor->getShippingMethods();
    }

    /**
     * Get current carrier
     *
     * @return string
     */
    protected function getCarrier()
    {
        return \XLite::getController()->getCarrier();
    }

    /**
     * Return true if processor match to the current carrier
     *
     * @param \XLite\Model\Shipping\Processor\AProcessor $processor Shipping processor object
     *
     * @return boolean
     */
    protected function isActiveCarrier($processor)
    {
        $carrier = $this->getCarrier();

        return $carrier == $processor->getProcessorId()
            || (empty($carrier) && 'offline' == $processor->getProcessorId());
    }
}
