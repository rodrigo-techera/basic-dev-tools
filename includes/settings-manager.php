<?php
class Basic_Dev_Tools_Settings_Manager {
	function __construct() {
		if(get_option('basic_dev_tools_show_admin_bar', false)) {
			add_filter('show_admin_bar', '__return_false');
		}
	}

	function apply_special_settings() {
		if(get_option('basic_dev_tools_show_admin_bar', false)) {
			add_filter('show_admin_bar', '__return_false');
		}
	}
}

global $basic_dev_tools_settings_manager_obj;
$basic_dev_tools_settings_manager_obj = new Basic_Dev_Tools_Settings_Manager();
?>