<?php
class Basic_Dev_Tools_Settings_Manager {
	function __construct() {
		
		//

		
		//add_filter( 'pre_site_transient_update_plugins', array($this, 'last_checked_plugins') );

		//disable core updates option
		//add_filter( 'pre_site_transient_update_core', array($this, 'last_checked_core') );
	}

	function apply_special_settings() {
		//disable admin bar option
		if(get_option('basic_dev_tools_hide_admin_bar', false))
			add_filter('show_admin_bar', '__return_false');
		
		//disable theme updates option
		if(get_option('basic_dev_tools_disable_theme_updates', false)) {
			add_filter('pre_site_transient_update_themes', array($this, 'cancel_theme_updates'));

			remove_action('load-update-core.php', 'wp_update_themes');
		}
		
		//disable plugins updates option
		if(get_option('basic_dev_tools_disable_plugin_updates', false)) {
			add_filter('pre_site_transient_update_plugins', array($this, 'cancel_plugin_updates'));

			remove_action('load-update-core.php', 'wp_update_plugins');
		}
		
		//disable core updates option
		if(get_option('basic_dev_tools_disable_core_updates', false)) {
			$this->cancel_core_updates();

			remove_action('wp_maybe_auto_update', 'wp_maybe_auto_update');
			remove_action('admin_init', 'wp_maybe_auto_update');
			remove_action('admin_init', 'wp_auto_update_core');
		}
	}

	function cancel_theme_updates() {
		global $wp_version;

		$theme_files = array();
		if(count(wp_get_themes())>0)
			foreach(wp_get_themes() as $theme)
				$theme_files[$theme->get_stylesheet()] = $theme->get('Version');

		return (object) array(	'last_checked' => time(),
								'updates' => array(),
								'version_checked' => $wp_version,
								'checked' => $theme_files);
	}

	function cancel_plugin_updates() {
		global $wp_version;

		$plugin_files = array();
		if(!function_exists('get_plugins'))
			require_once ABSPATH.'wp-admin/includes/plugin.php';

		if(count(get_plugins())>0)
			foreach(get_plugins() as $file_name => $plugin)
				$plugin_files[$file_name] = $plugin['Version'];

		return (object) array(	'last_checked' => time(),
								'updates' => array(),
								'version_checked' => $wp_version,
								'checked' => $plugin_files);
	}

	function cancel_core_updates() {
		if(!defined('AUTOMATIC_UPDATER_DISABLED'))
			define('AUTOMATIC_UPDATER_DISABLED', true);
		
		if(!defined('WP_AUTO_UPDATE_CORE'))
			define('WP_AUTO_UPDATE_CORE', false);

		add_filter('pre_site_transient_update_core', array($this, 'cancel_core_update'));

		add_filter('automatic_updates_send_debug_email', '__return_false', 1);
		add_filter('pre_http_request', array($this, 'block_request'), 10, 3);
		add_filter('auto_update_translation', '__return_false');
		add_filter('automatic_updater_disabled', '__return_true');
		add_filter('allow_minor_auto_core_updates', '__return_false');
		add_filter('allow_major_auto_core_updates', '__return_false');
		add_filter('allow_dev_auto_core_updates', '__return_false');
		add_filter('auto_update_core', '__return_false');
		add_filter('wp_auto_update_core', '__return_false');
		add_filter('auto_core_update_send_email', '__return_false');
		add_filter('send_core_update_notification_email', '__return_false');
		add_filter('auto_update_plugin', '__return_false');
		add_filter('auto_update_theme', '__return_false');
		add_filter('automatic_updates_send_debug_email', '__return_false');
		add_filter('automatic_updates_is_vcs_checkout', '__return_true');
	}

	function cancel_core_update() {
		global $wp_version;
		
		return (object) array( 	'last_checked' => time(),
								'updates' => array(),
								'version_checked' => $wp_version);
	}
}

global $basic_dev_tools_settings_manager_obj;
$basic_dev_tools_settings_manager_obj = new Basic_Dev_Tools_Settings_Manager();
?>