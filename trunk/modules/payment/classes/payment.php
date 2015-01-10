<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/payment/classes/payment.php
//
// Revision history
// 2012-05-11 - Created
namespace payment\classes;
gen_pull_language('payment');
class payment {
	public $id;
  	public $payment_fields;
  	public $text;
  	public $description;
  	public $open_pos_drawer	= false;
  	public $show_in_pos		= true;
  	public $pos_gl_acct;
  	public $sort_order;
  	public $keys            = array();
  	public $installed		= false;

  	public function __construct(){
	  	$method = get_called_class();
	  	if($this->id == '')   trigger_error("the id variable for the method $method can not be empty ", E_USER_ERROR);
	  	if($this->text == '') trigger_error("the text variable for the method $method can not be empty ", E_USER_ERROR);
	  	define('FILENAME_POPUP_CVV_HELP', 'popup_cvv_help'); // TBD
		$this->open_pos_drawer  = defined('MODULE_PAYMENT_'.strtoupper($this->id).'_OPEN_POS_DRAWER')  ? constant('MODULE_PAYMENT_'.strtoupper($this->id).'_OPEN_POS_DRAWER')  : $this->open_pos_drawer;
		$this->sort_order  		= defined('MODULE_PAYMENT_'.strtoupper($this->id).'_SORT_ORDER')  	 ? constant('MODULE_PAYMENT_'.strtoupper($this->id).'_SORT_ORDER')  	   : $this->sort_order;
		$this->pos_gl_acct 		= defined('MODULE_PAYMENT_'.strtoupper($this->id).'_POS_GL_ACCT') 	 ? constant('MODULE_PAYMENT_'.strtoupper($this->id).'_POS_GL_ACCT') 	   : $this->pos_gl_acct;
		$this->show_in_pos      = defined('MODULE_PAYMENT_'.strtoupper($this->id).'_SHOW_IN_POS')      ? constant('MODULE_PAYMENT_'.strtoupper($this->id).'_SHOW_IN_POS')      : $this->show_in_pos;
		$this->keys[] = array('key' => 'MODULE_PAYMENT_'.strtoupper($this->id).'_OPEN_POS_DRAWER', 'default' => $this->open_pos_drawer, 'text' => OPEN_POS_DRAWER_DESC );
		$this->keys[] = array('key' => 'MODULE_PAYMENT_'.strtoupper($this->id).'_SORT_ORDER', 	    'default' => $this->sort_order,      'text' => SORT_ORDER_DESC);
		$this->keys[] = array('key' => 'MODULE_PAYMENT_'.strtoupper($this->id).'_POS_GL_ACCT', 	'default' => $this->pos_gl_acct,     'text' => TEXT_POS_GL_ACCOUNT_PAYMENT_TYPE);
		$this->keys[] = array('key' => 'MODULE_PAYMENT_'.strtoupper($this->id).'_SHOW_IN_POS', 	'default' => $this->show_in_pos,     'text' => TEXT_SHOW_THIS_PAYMENT_IN_PHREEPOS);
		$this->field_0 = isset($_POST[$this->id.'_field_0']) ? $_POST[$this->id.'_field_0'] : '';//$this->cc_card_owner_last
		$this->field_1 = isset($_POST[$this->id.'_field_1']) ? $_POST[$this->id.'_field_1'] : '';//$this->cc_card_number
		$this->field_2 = isset($_POST[$this->id.'_field_2']) ? $_POST[$this->id.'_field_2'] : '';//$this->cc_expiry_month
		$this->field_3 = isset($_POST[$this->id.'_field_3']) ? $_POST[$this->id.'_field_3'] : '';//$this->cc_expiry_year
		$this->field_4 = isset($_POST[$this->id.'_field_4']) ? $_POST[$this->id.'_field_4'] : '';//$this->cc_cvv2
		$this->field_5 = isset($_POST[$this->id.'_field_5']) ? $_POST[$this->id.'_field_5'] : '';//$this->cc_card_owner_first
		$this->field_6 = isset($_POST[$this->id.'_field_6']) ? $_POST[$this->id.'_field_6'] : '';//$this->alternate 2
		$card_number = trim($this->field_1);
		$card_number = substr($card_number, 0, 4) . '********' . substr($card_number, -4);
		$this->payment_fields = implode(':', array($this->field_0, $card_number, $this->field_2, $this->field_3, $this->field_4, $this->field_5, $this->field_6));
	  	if (defined('MODULE_PAYMENT_' . strtoupper($this->id) . '_STATUS'))	 $this->installed = true;
  	}

  	/**
  	 * this will preform the install functions
  	 */

  	function install(){
  		write_configure('MODULE_PAYMENT_' . strtoupper($this->id) . '_STATUS', '1');
		foreach ($this->keys as $key) write_configure($key['key'], $key['default']);

  	}
  	/**
	 * this method is used when you update config settings.
	 */

    function update() {
    	foreach ($this->keys as $key) {
          $field = strtolower($key['key']);
          if (isset($_POST[$field])) write_configure($key['key'], $_POST[$field]);
        }
  	}

	function configure($key) {
	    switch ($key) {
	        case 'MODULE_PAYMENT_'.strtoupper($this->id).'_OPEN_POS_DRAWER':
	                $temp = array(
	                                array('id' => '0', 'text' => TEXT_NO),
	                                array('id' => '1', 'text' => TEXT_YES),
	                );
	                return html_pull_down_menu(strtolower($key), $temp, constant($key));
	            case 'MODULE_PAYMENT_'.strtoupper($this->id).'_SHOW_IN_POS':
	                $temp = array(
	                                array('id' => '0', 'text' => TEXT_NO),
	                                array('id' => '1', 'text' => TEXT_YES),
	                );
	                return html_pull_down_menu(strtolower($key), $temp, constant($key));
	        case 'MODULE_PAYMENT_'.strtoupper($this->id).'_POS_GL_ACCT':
	                return html_pull_down_menu(strtolower($key), gen_coa_pull_down(), constant($key));
	        default:
	                return html_input_field(strtolower($key), constant($key));
	    }
	}

	function selection() {
	    return array(
	      'id'   => $this->id,
	      'page' => $this->text,
	    );
	}

	/**
  	 * this function will be called when a module is removed.
  	 * or when it is removed by in the payment admin page
  	 */

	function delete(){
		foreach ($this->keys as $key) remove_configure($key['key']); // remove all of the keys from the configuration table
		remove_configure('MODULE_PAYMENT_' . strtoupper($this->id) . '_STATUS');
		return true;
	}

	function javascript_validation() {
	    return false;
	}

	function pre_confirmation_check() {
	    return false;
	}

	function before_process() {
	    return false;
	}

	function confirmation() {
	    return array('title' => $this->description);
	}

  	function expirationMonths() {
  		$months = array();
  		for ($i = 1; $i < 13; $i++) {
  			$j = ($i < 10) ? '0' . $i : $i;
  			$months[] = array('id' => sprintf('%02d', $i), 'text' => $j.'-'.strftime('%B',mktime(0,0,0,$i,1,2000)));
  		}
  		return $months;
  	}

	function expirationYears() {
	  	$years = array();
	  	$today = getdate();
	  	for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
	  		$year = strftime('%Y',mktime(0,0,0,1,1,$i));
	  		$years[] = array('id' => $year, 'text' => $year);
	  	}
	  	return $years;
	}

	function validate($ccNumber) {
	    $cardNumber = strrev($ccNumber);
	    $numSum = 0;
	    for ($i = 0; $i < strlen($cardNumber); $i++) {
	    	$currentNum = substr($cardNumber, $i, 1);
	      	if ($i % 2 == 1) $currentNum *= 2; // Double every second digit
	      	if ($currentNum > 9) { // Add digits of 2-digit numbers together
	        	$firstNum = $currentNum % 10;
	        	$secondNum = ($currentNum - $firstNum) / 10;
	        	$currentNum = $firstNum + $secondNum;
	      	}
	      	$numSum += $currentNum;
	    }
	    // If the total has remainder it's bad
	    if ($numSum % 10 <> 0) throw new \core\classes\userException(TEXT_CCVAL_ERROR_INVALID_NUMBER);
	    return true;
	}
}
?>