<?php
class Basic_Dev_Tools_Cron_Manager {	
	public function __construct() {
		
	}

	public function plugin_activation() {
		
	}

	public function plugin_deactivation() {
		
	}

	public function show_list() {
		echo 'hello world 2223444';
	}
}

global $basic_dev_tools_cron_manager_obj;
$basic_dev_tools_cron_manager_obj = new Basic_Dev_Tools_Cron_Manager();
?>