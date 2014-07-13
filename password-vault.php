<?php
/*
Plugin Name: Password Vault
Version: 1.7
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
			'label1_req' => 'checked',
			'label2_req' => 'checked',
			'label3_req' => 'checked',
			'label4_req' => 'checked',
			'label5_req' => 'checked',
			'ssl_only' => '',
			'auditing' => 'checked',
			'seperate_icon' => 'checked',
			'hide_without_rights' => 'checked',
			'hide_page' => 'checked',
			'hide_page_after' => '10',
			'allow_delete' => 'checked',
			'hide_dcac_ad' => '',
			'limit_security_view' => '',
			'group_membership_to_use' => ''
		);

		// Add options
		add_option('password_vault', $options);

		$options = get_option('password_vault');

		if (!$options['auditing']) {
			$password_vault_tools = new password_vault_tools;
			$password_vault_tools->insert_audit(0, 'audit_auto_enabled', NULL, NULL, 'true');

			$options['auditing'] = 'checked';
			update_option('password_vault', $options);
		}

		$setup = new password_vault_setup();
		$setup->create_db_objects();

	 }

	function deactivation() {
		//delete_option('password_vault');
	}

	function upgrade() {
		$options = get_option('password_vault');

		if (!$options['auditing']) {
			$password_vault_tools = new password_vault_tools;
			$password_vault_tools->insert_audit(0, 'audit_auto_enabled', NULL, NULL, 'true');

			$options['auditing'] = 'checked';
			update_option('password_vault', $options);
		}

		$setup = new password_vault_setup();
		$setup->create_db_objects();
	}


	function tools_menu() {
		$options = get_option('password_vault');

		if ($options['seperate_icon'] != 'checked') {
			if (($options['group_membership_to_use'] && $this->is_in_group() != 0) || !$options['group_membership_to_use']) {
				$forms = new password_vault_tools();
	
				add_submenu_page('tools.php', __('Password Vault', 'password_vault'), __('Password Vault', 'password_vault'), 'read', 'password_vault', array($forms, 'show_tools_page'));
			}
		} else {
			$this->custom_menu();
		}
	}

	function is_in_group() {
		global $wpdb;
		$user_id = get_current_user_id();
		
		$count = $wpdb->get_var("select count(*) from {$wpdb->prefix}password_vault_group_users where user_id = $user_id");

		return $count;
	}

	function settings_menu() {
		$options = get_option('password_vault');

		if ($options['seperate_icon'] != 'checked') {
			$settings = new password_vault_settings();

			add_submenu_page('options-general.php', __('Password Vault', 'password_vault'), __('Password Vault', 'password_vault'), 'manage_options', 'password_vault_settings', array($settings, 'show_settings_page'));
		}
		
	}

	function custom_menu() {
		$options = get_option('password_vault');
		$password_vault_tools = new password_vault_tools();
		$password_vault_settings = new password_vault_settings();

		add_menu_page('Password Vault', 'Password Vault', '', 'password_vault_top', array($password_vault_tools, 'show_tools_page'), plugins_url( 'password-vault/icon.png' ));

		if (($options['group_membership_to_use'] && $this->is_in_group() != 0) || !$options['group_membership_to_use']) {
			add_submenu_page('password_vault_top', 'Password Vault', 'Password Vault', 'read', 'password_vault', array($password_vault_tools, 'show_tools_page'));
		}

		add_submenu_page('password_vault_top', 'Password Vault Settings', 'Password Vault Settings', 'manage_options', 'password_vault_settings', array($password_vault_settings, 'show_settings_page'));
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

	function get_source_page($area) {
		$options = get_option('password_vault');

		if ($options['seperate_icon'] == 'checked') {
			return 'admin.php';
		} else {
			if ($area == 'tools') {
				return 'tools.php';
			} elseif ($area=='settings') {
				return 'options-general.php';
			}
		}
	}

	function footer() {
		$options=get_option('password_vault');
		if (!$options['hide_dcac_ad']) {
			$password_vault_main = new password_vault_main();
			add_filter('admin_footer_text', array($password_vault_main, 'show_footer'));
			add_filter( 'update_footer', array($password_vault_main, 'show_footer_version'));
		} else {
			remove_filter('admin_footer_text', array($password_vault_main, 'show_footer'));
			remove_filter( 'update_footer', array($password_vault_main, 'show_footer_version'));
		}
	}

	function show_footer() {
		echo '<span id="footer-thankyou"><a href="http://dcac.co/applications/password-vault">Password Vault</a> provided by <a href="http://www.dcac.co">Denny Cherry & Associates Consulting</a><p></span>';
		
	}

	function show_footer_version() {
		$folder = plugins_url();
		$info = get_plugin_data( __FILE__ );
		echo "Version {$info['Version']}";
	}
} //End Class

$password_vault = new password_vault_main();

register_activation_hook(__FILE__, array($password_vault, 'activation'));
add_action('admin_menu', array($password_vault, 'tools_menu'));
add_action('admin_menu', array($password_vault, 'settings_menu'));
add_action('admin_init', array($password_vault, 'init_settings'), 1);
add_filter('plugin_action_links', array($password_vault, 'settings_menu_add_settings_link'),10,2);
register_deactivation_hook( __FILE__, array($password_vault, 'deactivation' ));
add_filter('upgrader_post_install', array($password_vault, 'upgrade'), 10, 2);
