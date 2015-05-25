<?php
/**
 * Plugin Name: Basic Dev Tools plugin
 * Plugin URI: http://marsminds.com
 * Description: Basic Tools and Functions for Development
 * Version: 1.4.1
 * Author: Marsminds
 * Author URI: http://marsminds.com
 */


//TODO
//$wpdb->prefix

defined('ABSPATH') or die('No script kiddies please!');
require_once(plugin_dir_path(__FILE__).'includes/settings-manager.php');
require_once(plugin_dir_path(__FILE__).'includes/cron-manager.php');
require_once(plugin_dir_path(__FILE__).'includes/post-type-manager.php');

//init
add_action('init', 'marsminds_basic_dev_tools_init');
function marsminds_basic_dev_tools_init() {
	add_action('admin_menu', 'marsminds_basic_dev_tools_add_menus');
	
	global $basic_dev_tools_post_type_manager_obj;
	$basic_dev_tools_post_type_manager_obj->add_post_types();

	global $basic_dev_tools_settings_manager_obj;
	$basic_dev_tools_settings_manager_obj->apply_special_settings();

	add_shortcode('bdt_post_type', array($basic_dev_tools_post_type_manager_obj, 'process_shortcodes'));
}

function marsminds_basic_dev_tools_add_menus() {
	global $basic_dev_tools_cron_manager_obj, $basic_dev_tools_post_type_manager_obj;
	
	add_menu_page('Basic Dev Tools', 'Basic Dev Tools', 'manage_options', 'basic-dev-tools/includes/index.php', '', '', 90);
	add_submenu_page('basic-dev-tools/includes/index.php', 'Special Settings', 'Special Settings', 'manage_options', 'basic-dev-tools/includes/index.php');
	add_submenu_page('basic-dev-tools/includes/index.php', 'Cron Manager', 'Cron Manager', 'manage_options', 'basic-dev-tools/includes/cron-manager.php', array($basic_dev_tools_cron_manager_obj, 'show'));
	add_submenu_page('basic-dev-tools/includes/index.php', 'Post Type Manager', 'Post Type Manager', 'manage_options', 'basic-dev-tools/includes/post-type-manager.php', array($basic_dev_tools_post_type_manager_obj, 'show'));
}

register_activation_hook(__FILE__, 'marsminds_basic_dev_tools_activation');
function marsminds_basic_dev_tools_activation() {
	global $basic_dev_tools_cron_manager_obj, $basic_dev_tools_post_type_manager_obj;

	$basic_dev_tools_cron_manager_obj->plugin_activation();
	$basic_dev_tools_post_type_manager_obj->plugin_activation();
}

register_deactivation_hook(__FILE__, 'marsminds_basic_dev_tools_deactivation');
function marsminds_basic_dev_tools_deactivation() {
	global $basic_dev_tools_cron_manager_obj, $basic_dev_tools_post_type_manager_obj;

	$basic_dev_tools_cron_manager_obj->plugin_deactivation();
	$basic_dev_tools_post_type_manager_obj->plugin_deactivation();
}