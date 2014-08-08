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

namespace XLite\Module\CDev\PINCodes\Model;

/**
 * OrderItem
 *
 * @HasLifecycleCallbacks
 */
class OrderItem extends \XLite\Model\OrderItem implements \XLite\Base\IDecorator
{
    /**
     * Pin codes (relation)
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @OneToMany (targetEntity="XLite\Module\CDev\PINCodes\Model\PinCode", mappedBy="orderItem")
     */
    protected $pinCodes;

    /**
     * Constructor
     *
     * @param array $data Entity properties OPTIONAL
     *
     * @return void
     */
    public function __construct(array $data = array())
    {
        $this->pinCodes = new \Doctrine\Common\Collections\ArrayCollection();

        parent::__construct($data);
    }

    /**
     * Count pin codes
     *
     * @return array
     */
    public function countPinCodes()
    {
        return $this->getPinCodes()->count();
    }

    /**
     * Counts amount of PIN codes that should be assigned to this order item
     *
     * @return integer
     */
    public function countMissingPinCodes()
    {
        $count = 0;
        if ($this->getProduct()->getPinCodesEnabled()) {
            $count = $this->getAmount() - $this->countPinCodes();
        }

        return $count; 
    }

    /**
     * Acquire pin codes from assigned product
     *
     * @return array|void
     */
    public function acquirePinCodes() 
    {
        $pincodes = null;
        $product = $this->getProduct();
        if ($product->getPinCodesEnabled()) {
            $pincodes = array();
            for ($i = 0; $i < $this->getAmount(); $i++) {
                if ($product->getAutoPinCodes()) {
                    $code = new \XLite\Module\CDev\PINCodes\Model\PinCode;
                    $code->setProduct($product);
                    $code->generateCode();
                    \XLite\Core\Database::getEM()->persist($code);
                } else {
                    $code = \XLite\Core\Database::getRepo('XLite\Module\CDev\PINCodes\Model\PinCode')
                        ->getAvailablePinCode($product, $i);
                }

                if ($code) {
                    $code->setOrderItem($this);
                    $this->addPinCodes($code);
                    $code->setIsSold(true);
                    $pinCodes[] = $code;
                } else {
                    \XLite\Logger::getInstance()->log(
                        'Could not acquire pin code for order item #' . $this->getItemId(), 
                        LOG_ERR
                    ); 
                }
            }
        }

        return $pincodes;
    }
}
