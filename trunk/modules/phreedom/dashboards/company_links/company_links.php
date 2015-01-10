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
//  Path: /modules/phreedom/dashboards/company_links/company_links.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreedom\dashboards\company_links;
class company_links extends \core\classes\ctl_panel {
	public  $description 		= CP_COMPANY_LINKS_DESCRIPTION;
	private $security_id  		= SECURITY_ID_PHREEFORM;
	private $security_level		= 0;
	public  $text		 		= CP_COMPANY_LINKS_TITLE;
	public  $version      		= '4.0';

  	function install($column_id = 1, $row_id = 0) {
  		global $admin;
		// fetch the pages params to copy to new install
		$result = $admin->DataBase->query("SELECT params FROM ".TABLE_USERS_PROFILES." WHERE menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this) . "'"); // just need one
		$this->default_params = unserialize($result['params']);
		parent::install($column_id, $row_id);
  	}

  	function output() {
		global $admin;
		$contents = '';
		$control  = '';
		// Build control box form data
		$control  = '<div class="row">';
		$control .= '<div style="white-space:nowrap">';
		if ($this->security_level > 1) { // only show add new if user permission is set to add
			$control .= TEXT_TITLE . '&nbsp;' . html_input_field('company_links_field_0', '', 'size="40"') . '<br />';
			$control .= TEXT_URL   . '&nbsp;' . html_input_field('company_links_field_1', '', 'size="64"');
			$control .= '&nbsp;&nbsp;&nbsp;&nbsp;';
			$control .= html_submit_field('sub_company_links', TEXT_ADD);
		}
		$control .= html_hidden_field('company_links_rId', '');
		$control .= '</div></div>';
		// Build content box
		$contents = '';
		if (is_array($this->params)) {
		  	$index = 1;
		  	foreach ($this->params as $title => $hyperlink) {
				if ($this->security_level > 3) { // only let delete if user permission is full
			  		$contents .= '<div style="float:right; height:16px;">';
			  		$contents .= html_icon('phreebooks/dashboard-remove.png', TEXT_REMOVE, 'small', 'onclick="return del_index(\'' . $this->id . '\', ' . $index . ')"');
			  		$contents .= '</div>';
				}
				$contents .= '<div style="height:16px;">';
				$contents .= '  <a href="' . $hyperlink . '" target="_blank">' . $title . '</a>' . chr(10);
				$contents .= '</div>';
				$index++;
		  	}
		} else {
			$contents = TEXT_NO_RESULTS_FOUND;
		}
		return $this->build_div($contents, $control);
  	}

	function update() {
		global $admin;
		$my_title  = db_prepare_input($_POST['company_links_field_0']);
		$my_url    = db_prepare_input($_POST['company_links_field_1']);
		$remove_id = db_prepare_input($_POST[$this->id . '_rId']);
		// do nothing if no title or url entered
		if (!$remove_id && ($my_title == '' || $my_url == '')) return;
		// fetch the current params
		$result = $admin->DataBase->query("SELECT params FROM " . TABLE_USERS_PROFILES . " WHERE menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this) . "'"); // just need one
		if ($remove_id) { // remove element
			$this->params	= unserialize($result['params']);
			$first_part 	= array_slice($this->params, 0, $remove_id - 1);
			$last_part  	= array_slice($this->params, $remove_id);
			$this->params   = array_merge($first_part, $last_part);
		} elseif ($result['params']) { // append new url and sort
		  	$this->params     			= unserialize($result['params']);
		  	$this->params[$my_title] 	= $my_url;
		} else { // first entry
		  	$this->params[$my_title] 	= $my_url;
		}
		ksort($this->params);
		db_perform(TABLE_USERS_PROFILES, array('params' => serialize($this->params)), "update", "menu_id = '{$this->menu_id}' and dashboard_id = '" . get_class($this). "'");
	}
}
?>