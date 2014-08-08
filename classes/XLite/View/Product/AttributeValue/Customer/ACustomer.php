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

namespace XLite\View\Product\AttributeValue\Customer;

/**
 * Abstract attribute value (customer)
 */
abstract class ACustomer extends \XLite\View\Product\AttributeValue\AAttributeValue
{
    /**
     * Selected attribute value ids
     *
     * @var array
     */
    protected $selectedIds = null;

    /**
     * Return field name
     *
     * @return string
     */
    protected function getName()
    {
        return 'attribute_values[' . $this->getAttribute()->getId() . ']';
    }

    /**
     * Return selected attribute values ids
     *
     * @return array
     */
    protected function getSelectedIds()
    {
        if (!isset($this->selectedIds)) {
            $this->selectedIds = array();
            if (
                method_exists($this, 'getSelectedAttributeValuesIds')
                || method_exists(\XLite::getController(), 'getSelectedAttributeValuesIds')
            ) {
                $this->selectedIds = $this->getSelectedAttributeValuesIds();
            }
        }

        return $this->selectedIds;
    }

}
