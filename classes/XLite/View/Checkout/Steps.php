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

namespace XLite\View\Checkout;

/**
 * Checkout steps block
 *
 * @ListChild (list="checkout.main", weight="200")
 */
class Steps extends \XLite\View\AView
{
    /**
     * Shipping modifier (cache)
     *
     * @var \XLite\Model\Order\Modifier
     */
    protected $shippingModifier;

    /**
     * Steps (cache)
     *
     * @var array
     */
    protected $steps;

    /**
     * Register JS files
     *
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'form_field/select_country.js';

        return $list;
    }

    /**
     * Get steps
     *
     * @return array
     */
    public function getSteps()
    {
        if (!isset($this->steps)) {

            $this->steps = array();
            foreach ($this->defineSteps() as $step) {
                $widget = $this->getWidget(
                    array(
                        \XLite\View\Checkout\Step\AStep::PARAM_PARENT_WIDGET => $this,
                    ),
                    $step
                );
                $this->steps[$widget->getStepName()] = $widget;
            }
        }

        return $this->steps;
    }

    /**
     * Check - specified step is current or not
     *
     * @param \XLite\View\Checkout\Step\AStep $step Step
     *
     * @return boolean
     */
    public function isEnabledStep(\XLite\View\Checkout\Step\AStep $step)
    {
        return $step->isEnabled();
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'checkout/steps.tpl';
    }

    /**
     * Check - has specified step left arrow or not
     *
     * @param \XLite\View\Checkout\Step\AStep $step Step
     *
     * @return boolean
     */
    protected function hasLeftArrow(\XLite\View\Checkout\Step\AStep $step)
    {
        $steps = $this->getSteps();

        return array_shift($steps) != $step;
    }

    /**
     * Check - has specified step right arrow or not
     *
     * @param \XLite\View\Checkout\Step\AStep $step Step
     *
     * @return boolean
     */
    protected function hasRightArrow(\XLite\View\Checkout\Step\AStep $step)
    {
        $steps = $this->getSteps();

        return array_pop($steps) != $step;
    }

    /**
     * Define checkout widget steps
     *
     * @return array
     */
    protected function defineSteps()
    {
        $steps = array();

        $steps[] = '\XLite\View\Checkout\Step\Shipping';
        $steps[] = '\XLite\View\Checkout\Step\Review';

        return $steps;
    }

    /**
     * Get modifier
     *
     * @return \XLite\Model\Order\Modifier
     */
    protected function getShippingModifier()
    {
        if (!isset($this->shippingModifier)) {
            $this->shippingModifier
                = $this->getCart()->getModifier(\XLite\Model\Base\Surcharge::TYPE_SHIPPING, 'SHIPPING');
        }

        return $this->shippingModifier;
    }
}
