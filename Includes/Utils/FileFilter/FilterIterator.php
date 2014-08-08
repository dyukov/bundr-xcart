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

namespace Includes\Utils\FileFilter;

/**
 * FilterIterator
 *
 * @package XLite
 */
class FilterIterator extends \FilterIterator
{
    /**
     * Pattern to filter paths
     *
     * @var string
     */
    protected $pattern;

    /**
     * List of filtering callbacks
     *
     * @var array
     */
    protected $callbacks = array();


    /**
     * Constructor
     *
     * @param \Iterator $iterator iterator to use
     * @param string    $pattern  pattern to filter paths
     *
     * @return void
     */
    public function __construct(\Iterator $iterator, $pattern = null)
    {
        parent::__construct($iterator);

        $this->pattern = $pattern;
    }

    /**
     * Add callback to filter files
     *
     * @param array $callback Callback to register
     *
     * @return void
     */
    public function registerCallback(array $callback)
    {
        if (!is_callable($callback)) {
            \Includes\ErrorHandler::fireError('Filtering callback is not valid');
        }

        $this->callbacks[] = $callback;
    }

    /**
     * Check if current element of the iterator is acceptable through this filter
     *
     * @return bool
     */
    public function accept()
    {
        if (!($result = !isset($this->pattern))) {
            $result = preg_match($this->pattern, $this->getPathname());
        }

        if (!empty($this->callbacks)) {

            while ($result && (list(, $callback) = each($this->callbacks))) {
                $result = call_user_func_array($callback, array($this));
            }

            reset($this->callbacks);
        }

        return $result;
    }
}
