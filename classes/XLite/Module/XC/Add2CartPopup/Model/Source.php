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

namespace XLite\Module\XC\Add2CartPopup\Model;

/**
 * Fake entity to use for products sources items list
 *
 * @Entity
 * @Table  (name="add2cart_popup_sources")
 */
class Source extends \XLite\Model\AEntity
{
    /**
     * Unique Id
     *
     * @var integer
     *
     * @Id
     * @GeneratedValue (strategy="AUTO")
     * @Column         (type="integer")
     */
    protected $id;

    /**
     * Unique source code
     *
     * @var string
     *
     * @Column (type="string", length=3)
     */
    protected $code;

    /**
     * Enabled
     *
     * @var boolean
     *
     * @Column (type="boolean")
     */
    protected $enabled;

    /**
     * Position (priority)
     *
     * @var integer
     *
     * @Column (type="integer")
     */
    protected $position;


    /**
     * isPersistens is always returns true for this object as this is a fake entity
     *
     * @return boolean
     */
    public function isPersistent()
    {
        return true;
    }

    /**
     * Get entity unique identifier value
     *
     * @return string
     */
    public function getUniqueIdentifier()
    {
        return $this->code;
    }
}
