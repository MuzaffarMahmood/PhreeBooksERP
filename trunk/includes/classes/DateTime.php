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
//  Path: /includes/classes/DateTime.php
//
namespace core\classes;
class DateTime extends \DateTime {
   
	/** builds sql date string and description string based on passed criteria
	 * function requires as input an associative array with two entries:
	 * @param date_prefs = imploded (:) string with three entries
	 *    entry 1 => date range specfication for switch statement
	 *    entry 2 => start date value db format
	 *    entry 3 => end date value db format
	 * @param df = database fieldname for the sql date search
	 * @todo replacement of gen_build_sql_date
	 */
	function sql_date_array($date_prefs, $fieldname) {
		$dates = $this->array_dates();
		$DateArray = explode(':', $date_prefs);
		$t = time();
		$start_date = '0000-00-00';
		$end_date = '2199-00-00';
		$raw_sql = '';
		$fildesc = '';
		switch ($DateArray[0]) {
			default:
			case "a": // All, skip the date addition to the where statement, all dates in db
				break;
			case "b": // Date Range
				$fildesc = TEXT_DATE_RANGE. ': ';
				if ($DateArray[1] <> '') {
			  		$start_date = $this->db_date_format($DateArray[1]);
			  		$raw_sql .=  "$fieldname >= '$start_date'";
			  		$fildesc .= ' ' . TEXT_FROM . ' ' . $DateArray[1];
				}
				if ($DateArray[2] <> '') { // a value entered, check
			  		if (strlen($raw_sql) > 0) $raw_sql .= ' and ';
			  		$end_date = gen_specific_date($this->db_date_format($DateArray[2]), 1);
			  		$raw_sql.= "$fieldname < '$end_date'";
			  		$fildesc .= ' ' . TEXT_TO . ' ' . $DateArray[2];
				}
				$fildesc .= '; ';
				break;
			case "c": // Today (specify range for datetime type fields to match for time parts)
				$end_date = clone $this;
				$end_date->modify('+1 day');
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname < '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' = ' . $this->format(DATE_FORMAT) . '; ';
				break;
			case "d": // This Week
				$this->modify("-{$this->format('w')} day");
				$end_date = clone $this;
				$end_date->modify('+6 day');
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname =< '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "e": // This Week to Date
				$end_date = clone $this;
				$this->modify("-{$this->format('w')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname =< '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT) . '; ';
				break;
			case "f": // This Month
				$this->modify("-{$this->format('j')} day");
				$end_date = clone $this;
				$end_date->modify("+-{$this->format('t')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname =< '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT). '; ';
				break;
			case "g": // This Month to Date
				$end_date = clone $this;
				$this->modify("-{$this->format('j')} day");
				$raw_sql = "$fieldname >= '{$this->format('Y-m-d')}' and $fieldname =< '{$end_date->format('Y-m-d')}'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->format(DATE_FORMAT) . ' ' . TEXT_TO . ' ' . $end_date->format(DATE_FORMAT). '; ';
				break;
			case "h": // This Quarter
				$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3); @toto hier
				$temp = $this->get_fiscal_dates($QtrStrt);
				$start_date = $temp['start_date'];
				$temp = $this->get_fiscal_dates($QtrStrt + 2);
				$end_date = gen_specific_date($temp['end_date'], 1);
				$raw_sql = "$fieldname >= '$start_date' and $fieldname < '$end_date'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($temp['end_date']) . '; ';
				break;
			case "i": // Quarter to Date
				$QtrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 3);
				$temp = $this->get_fiscal_dates($QtrStrt);
				$start_date = $temp['start_date'];
				$end_date = gen_specific_date($dates['Today'], 1);
				$raw_sql = "$fieldname >= '$start_date' and $fieldname < '$end_date'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($dates['Today']) . '; ';
				break;
			case "j": // This Year
				$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
				$temp = $this->get_fiscal_dates($YrStrt);
				$start_date = $temp['start_date'];
				$temp = $this->get_fiscal_dates($YrStrt + 11);
				$end_date = gen_specific_date($temp['end_date'], 1);
				$raw_sql = "$fieldname >= '$start_date' and $fieldname < '$end_date'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($temp['end_date']) . '; ';
				break;
			case "k": // Year to Date
				$YrStrt = CURRENT_ACCOUNTING_PERIOD - ((CURRENT_ACCOUNTING_PERIOD - 1) % 12);
				$temp = $this->get_fiscal_dates($YrStrt);
				$start_date = $temp['start_date'];
				$end_date = gen_specific_date($dates['Today'], 1);
				$raw_sql = "$fieldname >= '$start_date' and $fieldname < '$end_date'";
				$fildesc = TEXT_DATE_RANGE . ' ' . TEXT_FROM . ' ' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($dates['Today']) . '; ';
				break;
			case "l": // This Period
				$start_date = CURRENT_ACCOUNTING_PERIOD_START;
				$end_date = gen_specific_date(CURRENT_ACCOUNTING_PERIOD_END, 1);
				$raw_sql = "$fieldname >= '$start_date' and $fieldname < '$end_date'";
				$fildesc = TEXT_PERIOD . ' ' . CURRENT_ACCOUNTING_PERIOD . ' (' . $this->locale_date(CURRENT_ACCOUNTING_PERIOD_START) . ' ' . TEXT_TO . ' ' . $this->locale_date(CURRENT_ACCOUNTING_PERIOD_END) . '); ';
				break;
			case "z": // date by period
				$temp = $this->get_fiscal_dates($DateArray[1]);
				$start_date = $temp['start_date'];
				$end_date = $temp['end_date'];
				$raw_sql = 'period = ' . $DateArray[1];
				$fildesc = TEXT_PERIOD . ' ' . $DateArray[1] . ' (' . $this->locale_date($start_date) . ' ' . TEXT_TO . ' ' . $this->locale_date($end_date) . '); ';
				break;
		}
		$dates = array(
		  'sql'         => $raw_sql,
		  'description' => $fildesc,
		  'start_date'  => $start_date,
		  'end_date'    => $end_date,
		);
		return $dates;
	}
	
	/**
	 * returns a database date from when formated in DATE_FORMAT 
	 * @param string $raw_date
	 * @return string
	 * @todo repalcement of gen_db_date
	 */
	function db_date_format($raw_date = '') { 
		$this->createFromFormat ( DATE_FORMAT , $raw_date);
		$errors = $this->getLastErrors();
		$year = $this->format('Y');
		if ($year  < 1900 || $year  > 2099) throw new \core\classes\userException("The year is lower than 1900 or higher than 2099 recieved: $year ", 'error');
		if ($errors['warning_count'] != 0)  throw new \core\classes\userException($errors['warnings'], 	'error');
		if ($errors['error_count'] != 0)    throw new \core\classes\userException($errors['errors'],	'error');
		return $this->format('Y-m-d');
	}
	
	/**
	 * sets current date to local date.
	 * the date needs to be construced first.
	 * @param bool $long
	 * @return string
	 * @todo replacement of gen_locale_date
	 */
	function locale_date($long = false) { // from db to display format
		$errors = $this->getLastErrors();
		$year = $this->format('Y');
		if ($year  < 1900 || $year  > 2099) throw new \core\classes\userException("The year is lower than 1900 or higher than 2099 recieved: $year ", 'error');
		if ($errors['warning_count'] != 0)  throw new \core\classes\userException($errors['warnings'], 	'error');
		if ($errors['error_count'] != 0)    throw new \core\classes\userException($errors['errors'],	'error');
		if ($long) return $this->format(DATE_TIME_FORMAT);
		return $this->format(DATE_FORMAT);
	}
	
	/**
	 * generates a dates array( Today, ThisDay, ThisMonth, ThisYear, TotalDays, MonthName)
	 * @return array 
	 * @todo replacement of gen_get_dates
	 */
	function array_dates() {
		$result = array();
		$result['Today']     = $this->format('Y-m-d');
		$result['ThisDay']   = $this->format('d');
		$result['ThisMonth'] = $this->format('m');
		$result['ThisYear']  = $this->format('Y');
		$result['TotalDays'] = $this->format('t');
		switch($result['ThisMonth']){
			case 1:		$result['MonthName'] = TEXT_JAN;	break;
			case 2:		$result['MonthName'] = TEXT_FEB;	break;
			case 3:		$result['MonthName'] = TEXT_MAR;	break;
			case 4:		$result['MonthName'] = TEXT_APR;	break;
			case 5:		$result['MonthName'] = TEXT_MAY;	break;
			case 6:		$result['MonthName'] = TEXT_JUN;	break;
			case 7:		$result['MonthName'] = TEXT_JUL;	break;
			case 8:		$result['MonthName'] = TEXT_AUG;	break;
			case 9:		$result['MonthName'] = TEXT_SEP;	break;
			case 10:	$result['MonthName'] = TEXT_OCT;	break;
			case 11:	$result['MonthName'] = TEXT_NOV;	break;
			case 12:	$result['MonthName'] = TEXT_DEC;	break;
		}
		return $result;
	}
	
	/**
	 * gets fiscal dates from database.
	 * @param number $period in format YYYY/mm/dd
	 * @throws \core\classes\userException
	 * @return array (fiscal_year, start_date, end_date)
	 * @todo replacements of gen_calculate_fiscal_dates
	 */
	static function get_fiscal_dates($period = 1) {
		global $admin;
		$result = $admin->DataBase->query("SELECT fiscal_year, start_date, end_date FROM " . TABLE_ACCOUNTING_PERIODS . " WHERE period = $period");
		// post_date is out of range of defined accounting periods
		if ($result->rowCount() <> 1) throw new \core\classes\userException(ERROR_MSG_POST_DATE_NOT_IN_FISCAL_YEAR,'error');
		return $result;
	}
}

?>