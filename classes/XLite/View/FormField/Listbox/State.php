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

namespace XLite\View\FormField\Listbox;

/**
 * States listbox widget
 */
class State extends \XLite\View\FormField\Listbox\AListbox
{
    /**
     * Widget param names
     */
    const PARAM_ALL = 'all';


    /**
     * Prepare and set up value of listbox
     *
     * @param mixed $value Value to set
     *
     * @return void
     */
    public function setValue($value)
    {
        if (is_object($value) && $value instanceOf \Doctrine\Common\Collections\Collection) {
            $value = $value->toArray();

        } elseif (!is_array($value)) {
            $value = array($value);
        }

        foreach ($value as $k => $v) {
            if (is_object($v) && $v instanceOf \XLite\Model\AEntity) {
                $value[$k] = $v->getCountry()->getCode() . '_' . $v->getCode();
            }
        }

        parent::setValue($value);
    }

    /**
     * Get selector default options list
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        $list = \XLite\Core\Database::getRepo('XLite\Model\State')->findAllStates();
        usort($list, array('\XLite\Model\Zone', 'sortStates'));

        $options = array();

        foreach ($list as $state) {
            $options[$state->getCountry()->getCode() . '_' . $state->getCode()] = $state->getCountry()->getCountry() . ': ' . $state->getState();
        }

        return $options;
    }

    /**
     * Get value container class
     *
     * @return string
     */
    protected function getValueContainerClass()
    {
        return parent::getValueContainerClass() . ' state-listbox';
    }
}
