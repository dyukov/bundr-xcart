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


/**
 * X-Cart (standalone edition) web installation wizard: Report page
 */

if (!defined('XLITE_INSTALL_MODE')) {
	die('Incorrect call of the script. Stopped.');
}


global $requirements;

if (!empty($requirements) && is_array($requirements)) {

    $report = make_check_report($requirements);

?>

<div id="report-layer" class="report-layer" style="display:none;">

    <div id="report-window" class="report-window">

<a class="report-close" href="#" onclick="javascript: document.getElementById('report-layer').style.display='none'; return false;"><img src="<?php echo $skinsDir; ?>images/spacer.gif" width="10" height="10" border="0" alt="" /><span class="report-close" style="display: none;">close</span></a>


<form method="post" name="report_form" action="https://secure.x-cart.com/service.php">

<input type="hidden" name="target" value="install_feedback_report" />
<input type="hidden" name="product_type" value="XC5" />

<div class="report-title"><?php echo xtr('Technical problems report'); ?></div>

<br />
<br />

<?php echo xtr('ask_send_report_text'); ?>

<br />
<br />

<textarea name="report" class="report-details form-control" rows="5" cols="70" readonly="readonly"><?php echo $report; ?></textarea>

<br />
<br />

<div class="section-title"><?php echo xtr('Email:'); ?></div>

<div style="width:25%">
  <input type="text" name="user_email" class="form-control" value="" size="30" />
</div>
<?php echo xtr('user_email_hint'); ?>

<br />
<br />

<div class="section-title"><?php echo xtr('Additional comments'); ?></div>

<textarea name="user_note" class="report-notes form-control" rows="4" cols="70"></textarea>

<br />
<br />

<div style="text-align: center;">
    <input type="submit" class="btn btn-warning" value="<?php echo xtr('Send a report'); ?>" />
</div>

</form>

</div>

<div class="clear"></div>

</div>

<?php

}
