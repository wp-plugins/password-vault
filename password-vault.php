<?php
/*
Plugin Name: Password Vault
Version: 1.1
Plugin URI: http://dcac.co/go/password-vault
Description: Allows for the secure saving of passwords.  Access to a specific account can be given based on users and/or groups.  Groups are defined within WordPress or within the plugin directly.
Author: Denny Cherry
Author URI: http://dcac.co/
*/


require_once dirname( __FILE__ ) .'/setup.php';
require_once dirname( __FILE__ ) .'/settings.php';
require_once dirname( __FILE__ ) .'/tools.php';
require_once dirname( __FILE__ ) .'/group_management.php';


class password_vault_main {

	function activation() {

		// Default options
		$options = array (
			'donate' => '',
			'old_key' => '',
			'label1' => '',
			'label2' => '',
			'label3' => '',
			'label4' => '',
			'label5' => '',
			'min_permissions' => '',
			'label1_req' => '1',
			'label2_req' => '1',
			'label3_req' => '1',
			'label4_req' => '1',
			'label5_req' => '1',
			'ssl_only' => ''
		);

		// Add options
		add_option('password_vault', $options);
    
		$setup = new password_vault_setup();
		$setup->create_db_objects();

	 }

	function deactivation() {
		//delete_option('password_vault');
	}

	function upgrade() {
		$setup = new password_vault_setup();
		$setup->create_db_objects();
	}


	function tools_menu() {
		$forms = new password_vault_tools();

		add_submenu_page('tools.php', __('Password Vault', 'password_vault'), __('Password Vault', 'password_vault'), 'manage_options', 'password_vault', array($forms, 'show_tools_page'));
	}

	function settings_menu() {
		$settings = new password_vault_settings();

		add_submenu_page('options-general.php', __('Password Vault', 'password_vault'), __('Password Vault', 'password_vault'), 'manage_options', 'password_vault_settings', array($settings, 'show_settings_page'));
		
	}


	function settings_menu_add_settings_link ($links, $file) {
	// Add "Settings" link to the plugins page
		if ( $file != plugin_basename( __FILE__ ))
			return $links;

		$settings_link = sprintf( '<a href="options-general.php?page=password_vault_settings">%s</a>', __( 'Settings', '' ) );

		array_unshift( $links, $settings_link );

		return $links;
	}

	function init_settings(){
		$settings = new password_vault_settings();
		$settings->register_settings();

	}

	function footer() {
		#echo '<div id="wpfooter"><span id="footer-thankyou">Provided by <a href="http://www.dcac.co">Denny Cherry & Associates Consulting</a><p></span></div>';
	}
} //End Class

$password_vault = new password_vault_main();

register_activation_hook(__FILE__, array($password_vault, 'activation'));
add_action('admin_menu', array($password_vault, 'tools_menu'));
add_action('admin_menu', array($password_vault, 'settings_menu'));
add_action('admin_init', array($password_vault, 'init_settings'), 1);
add_filter('plugin_action_links', array($password_vault, 'settings_menu_add_settings_link'),10,2);
register_deactivation_hook( __FILE__, array($password_vault, 'deactivation' ));
add_action('upgrader_post_install', array($password_vault, 'upgrade')); //Deploy database proc on upgrade as needed.