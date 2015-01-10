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
//  Path: /includes/template_index.php
//
if ($custom_html) { // load the template only as the rest of the html will be generated by the template
  if (is_file($template_path)) { require($template_path); } else trigger_error('No template file. Looking for: ' . $template_path, E_USER_ERROR);
} else {
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
 <head>
  <script type="text/javascript">
    window.onerror = function(msg, url, linenumber) {
    	$.messager.alert("Javascript Error",'Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber,"error");
        return true;
    }
  </script>
  <!-- module: <?php echo "{$basis->module} - page: {$basis->page}"; ?> -->
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<?php if ($force_reset_cache) { header("Cache-Control: no-cache, must-revalidate"); header("Expires: ".date('D, j M \2\0\0\0 G:i:s T')); } ?>
  <title><?php echo $basis->page_title; ?></title>
  <!-- start loading includes -->
  <?php $basis->returnCurrentObserver()->print_css_includes($basis);
  		$basis->returnCurrentObserver()->print_js_includes($basis);
  ?>
  <!-- end loading includes -->
  <script type="text/javascript">
  var module              = '<?php echo $module; ?>';
  var pbBrowser           = (document.all) ? 'IE' : 'FF';
  var text_search         = '<?php echo TEXT_SEARCH; ?>';
  var date_format         = '<?php echo DATE_FORMAT; ?>';
  var date_delimiter      = '<?php echo DATE_DELIMITER; ?>';
  var inactive_text_color = '#cccccc';
  var form_submitted      = false;
  // Variables for script generated combo boxes
  var icon_path           = '<?php echo DIR_WS_ICONS; ?>';
  var combo_image_on      = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_active.gif'; ?>';
  var combo_image_off     = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_inactive.gif'; ?>';
<?php if (is_object($currencies)) { // will not be defined unless logged in and db defined ?>
  var decimal_places      = <?php  echo $currencies->currencies[DEFAULT_CURRENCY]['decimal_places']; ?>;
  var decimal_precise     = <?php  echo $currencies->currencies[DEFAULT_CURRENCY]['decimal_precise']; ?>;
  var decimal_point       = "<?php echo $currencies->currencies[DEFAULT_CURRENCY]['decimal_point']; ?>"; // leave " for ' separator
  var thousands_point     = "<?php echo $currencies->currencies[DEFAULT_CURRENCY]['thousands_point']; ?>";
  var formatted_zero      = "<?php echo $currencies->format(0); ?>";
<?php } ?>
  </script>
<?php
//require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/config.php');

?>
 </head>
 <body>
  <div id="please_wait"><p><?php echo html_icon('phreebooks/please_wait.gif', TEXT_PLEASE_WAIT, 'large'); ?></p></div>
  <!-- start Menu -->
  <?php $basis->returnCurrentObserver()->print_menu($basis);?>
  <!-- end Menu -->
  <!-- Template -->
  <?php require($basis->returnCurrentObserver()->include_template);?>
  </div>
  <!-- Footer -->
  <?php if ($basis->include_footer) { // Hook for custom logo
  $image_path = defined('FOOTER_LOGO') ? FOOTER_LOGO : (DIR_WS_ADMIN . 'modules/phreedom/images/phreesoft_logo.png');
  ?>
  <div style="clear:both;text-align:center;font-size:9px">
    <a href="http://www.PhreeSoft.com" target="_blank"><?php echo html_image($image_path, TEXT_PHREEDOM_INFO, NULL, '64'); ?></a><br />
  <?php
  $footer_info  = COMPANY_NAME.' | '.TEXT_ACCOUNTING_PERIOD.': '.CURRENT_ACCOUNTING_PERIOD.' | '.TEXT_PHREEDOM_INFO." ({$basis->classes['phreedom']->version}) ";
  if ($module <> 'phreedom') $footer_info .= "({$module} {$basis->classes[$module]->version}) ";
  $footer_info .= '<br />' . TEXT_COPYRIGHT .  ' &copy;' . date('Y') . ' <a href="http://www.PhreeSoft.com" target="_blank">PhreeSoft&trade;</a>';
  $footer_info .= '(' . (int)(1000 * (microtime(true) - PAGE_EXECUTION_START_TIME)) . ' ms) ' . $basis->DataBase->count_queries . ' SQLs (' . (int)($basis->DataBase->total_query_time * 1000).' ms)';
  echo $footer_info;
  ?>
  </div>
  <?php } // end if include_footer ?>
</body>
</html>
<?php } // end else if (custom_html) ?>
