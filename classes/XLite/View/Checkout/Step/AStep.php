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

namespace XLite\View\Checkout\Step;

/**
 * Abstract checkout step widget
 */
abstract class AStep extends \XLite\View\AView
{
    /**
     * Common widget parameter names
     */
    const PARAM_PARENT_WIDGET = 'parentWidget';

    /**
     * Get step name
     *
     * @return string
     */
    abstract public function getStepName();

    /**
     * Get step title
     *
     * @return string
     */
    abstract public function getTitle();

    /**
     * Get steps collector
     *
     * @return \XLite\View\Checkout\Steps
     */
    public function getStepsCollector()
    {
        return $this->getParam(self::PARAM_PARENT_WIDGET);
    }

    /**
     * Check - step is enabled (true) or skipped (false)
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * Define widget parameters
     *
     * @return void
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        $this->widgetParams += array(
            self::PARAM_PARENT_WIDGET => new \XLite\Model\WidgetParam\Object('Parent widget', null, false, '\XLite\View\Checkout\Steps'),
        );
    }

    /**
     * Return widget default template
     *
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'checkout/body.tpl';
    }

    /**
     * Return current template
     *
     * @return string
     */
    protected function getTemplate()
    {
        return $this->getParam(self::PARAM_TEMPLATE) == $this->getDefaultTemplate()
            ? $this->getStepTemplate()
            : $this->getParam(self::PARAM_TEMPLATE);
    }

    /**
     * Get step template
     *
     * @return string
     */
    protected function getStepTemplate()
    {
        $path = 'checkout/steps/' . $this->getStepName() . '/';

        if (!$this->isEnabled()) {
            $path .= 'disabled.tpl';

        } else {
            $path .= 'selected.tpl';
        }

        return $path;
    }

}
