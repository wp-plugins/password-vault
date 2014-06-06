<?php

class password_vault_settings {
	function show_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __('You are not allowed to access this part of the site') );
		}
		$this->check_password();

		$options = get_option('password_vault');

		$page = $this->get_source_page();

		echo "<br><form name='action_to_take' method='post' action='./{$page}?page=password_vault_settings'>";
		echo "<input type='submit' value='General Settings' name='action' class='button-secondary'>";
		echo "<input type='submit' value='Group Management' name='action' class='button-secondary'>";
		echo "<input type='submit' value='Group Membership' name='action' class='button-secondary'>";

		if ($options['oldkey']) {
			echo "<input type='submit' value='Complete Key Change' name='action' class='button-secondary'>";
		}
		echo "</form>";

		if ($_POST['action']) {
			$action = $_POST['action'];
		} else {
			$action=$_GET['action'];
		}

		$password_vault_groups = new password_vault_groups();

		echo '<div class="wrap">';
		echo '<H2>Password Vault Settings</H2>';

		if ($action=='Group Management' || $action=='group_management') {
			$password_vault_groups->group_management();
		} elseif ($action=='Group Membership' || $action=='group_membership') {
			$password_vault_groups->group_membership();
		} elseif ($action=='Complete Key Change' || $action=='key_change') {
			$this->finish_key_change();
		} else {
			
			echo '<form action="options.php" method="post">';
			settings_fields('password_vault');
			do_settings_sections('password_vault');
			echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="Save Changes" /></form></div>';
		}
	}

	function check_password() {
		$key = SECURE_AUTH_KEY;
		if ($key=='put your unique phrase here') {
			echo '<div if="message" class="error"><p>The default key is in use within the wp-config.php file.  It is highly recommended that the SECURE_AUTH_KEY value within your wp-config.php file be updated BEFORE putting accounts into this system.  Once set secure a copy of this key offline and do not share it with anyone as it will allow them to decrypt any passwords stored in this system.</p></div>';
		}
	}

	function register_settings() {
		register_setting('password_vault', 'password_vault', array(&$this, 'settings_validate'));
		add_settings_section('password_vault_labels', __('Settings', ''), array(&$this, 'labels_section'), 'password_vault');
		add_settings_field('label1', __('Custom Field 1:', ''), array(&$this, 'label1'), 'password_vault', 'password_vault_labels');
		add_settings_field('label2', __('Custom Field 2:', ''), array(&$this, 'label2'), 'password_vault', 'password_vault_labels');
		add_settings_field('label3', __('Custom Field 3:', ''), array(&$this, 'label3'), 'password_vault', 'password_vault_labels');
		add_settings_field('label4', __('Custom Field 4:', ''), array(&$this, 'label4'), 'password_vault', 'password_vault_labels');
		add_settings_field('label5', __('Custom Field 5:', ''), array(&$this, 'label5'), 'password_vault', 'password_vault_labels');
		add_settings_field('seperate_icon', __('Dedicated Menu:', ''), array(&$this, 'seperate_icon'), 'password_vault', 'password_vault_labels');
		#add_settings_field('min_permissions', __('Minimum Permissions To Use: ', ''), array(&$this, 'min_permissions'), 'password_vault', 'password_vault_labels');

		add_settings_section('password_vault_security', __('Security Settings', ''), array(&$this, 'security_settings'), 'password_vault');
		add_settings_field('ssl_only', __('Requires SSL:', ''), array(&$this, 'ssl_only'), 'password_vault', 'password_vault_security');
		add_settings_field('auditing', __('Enable Auditing:', ''), array(&$this, 'auditing'), 'password_vault', 'password_vault_security');
		
		add_settings_section('password_vault_keymanagement', __('Key Management', ''), array(&$this, 'keymanagement_section'), 'password_vault');
		add_settings_field('oldkey', __('Old Key: ', ''), array(&$this, 'oldkey'), 'password_vault', 'password_vault_keymanagement');
	}

	function seperate_icon() {
		$options = get_option('password_vault');
		echo "<input type='checkbox' name='password_vault[seperate_icon]' value='checked' {$options['seperate_icon']} onclick='change_form(this.form)'>";
		echo '
		<script>function change_form(f) {

			if (f["password_vault[seperate_icon]"].checked == true) {
				
				f["_wp_http_referer"].value = "/wp-admin/admin.php?page=password_vault_settings"
			} else {
				
				f["_wp_http_referer"].value = "/wp-admin/options-general.php?page=password_vault_settings"
			}
		}
		</script>
		';
	}

	function security_settings() {
		echo 'The "Require SSL" setting only applies to the usage of this plugin, not to the entire website.';
	}

	function auditing() {
		$options = get_option('password_vault');
		echo "<input type='checkbox' name='password_vault[auditing]' value='checked' {$options['auditing']}>";
	}

	function ssl_only() {
		$options = get_option('password_vault');
		echo "<input type='checkbox' name='password_vault[ssl_only]' value='checked' {$options['ssl_only']}>";
	}

	function min_permissions() {
		$options = get_option('password_vault');
		echo "<select name='password_vault[min_permissions]'>";
		echo "<option";
		if ($options['min_permissions']=="Admin") {
			echo " selected";
		}
		echo ">Admin</option>";
		echo "<option value='manage_options'";
		if ($options['min_permissions']=="manage_options") {
			echo "selected";
		}
		echo ">Manage Options</option>";
		echo "</select>";
	}

	function label1() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['label1']}' name='password_vault[label1]'>";

		echo "<input type='checkbox' value='checked' name='password_vault[label1_req]' {$options['label1_req']}> Is Required?";
	}

	function label2() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['label2']}' name='password_vault[label2]'>";

		echo "<input type='checkbox' value='checked' name='password_vault[label2_req]' {$options['label2_req']}> Is Required?";

	}

	function label3() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['label3']}' name='password_vault[label3]'>";

		echo "<input type='checkbox' value='checked' name='password_vault[label3_req]' {$options['label3_req']}> Is Required?";
	}

	function label4() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['label4']}' name='password_vault[label4]'>";

		echo "<input type='checkbox' value='checked' name='password_vault[label4_req]' {$options['label4_req']}> Is Required?";
	}

	function label5() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['label5']}' name='password_vault[label5]'>";

		echo "<input type='checkbox' value='checked' name='password_vault[label5_req]' {$options['label5_req']}> Is Required?";
	}

	function oldkey() {
		$options = get_option('password_vault');
		echo "<input type='text' value='{$options['oldkey']}' name='password_vault[oldkey]'>";
	}

	function labels_section() {
		echo "The password vault allows for up to 5 custom fields to be used for categorizing usernames and passwords in the vault.  The fields which should be used should be set here.  Any fields left blank will be hidden from the user interface.";
	}

	function keymanagement_section() {
		echo "If you need to change the private key within the WordPress config file, place the old key here so that passwords can be decrypted and reencrypted.  Normally this field should be left blank.  If a value is placed here and it is not the current key bad things can and probably will happen including all the passwords in the system being set to a blank value.  Keep this in mind, we aren't screwing around with this setting.<p>";
		echo "After setting the old key, a new menu item above will appear.  Use this new menu link at the top of the page to complete the process of re-encrypting the passwords with your new key.";
	}

	function settings_validate($input) {
		if (!$input['label1']) {
			$input['label1_req']='';
		}
		if (!$input['label2']) {
			$input['label2_req']='';
		}
		if (!$input['label3']) {
			$input['label3_req']='';
		}
		if (!$input['label4']) {
			$input['label4_req']='';
		}
		if (!$input['label5']) {
			$input['label5_req']='';
		}

		$options = get_option('password_vault');
		if ($options['auditing'] && !$input['auditing']) {
			$password_vault_tools = new password_vault_tools;
			$password_vault_tools->insert_audit(0, 'audit_disabled', NULL, NULL, 'true');
		}

		if (!$options['auditing'] && $input['auditing']) {
			$password_vault_tools = new password_vault_tools;
			$password_vault_tools->insert_audit(0, 'audit_enabled', NULL, NULL, 'true');
		}

		return $input;
	}

	function finish_key_change() {
		echo "By clicking the button below you understand that if the key which has been entered on the main settings page is incorrect all passwords within the system will be lost with no way to recover them.  It if HIGHLY recommended that before completing this operation that the WordPress database be backed up so that it can be restored in the event of a problem.  If you choose to proceed without a good, validated backup there is no support option which will get your data back.<p>This process may run for a while depending on how many records you have.  Do NOT stop the process.  If you have stopped the process, restore from a backup and start the process over again.";
		if (!$_POST['key-change-submit'] || !$_POST['agreement']) {
			echo "<form name='key_change' method='POST'>";
			echo "<input type='checkbox' name='agreement' value='I Agree'> I have a backup<br>";
			echo '<p class="submit"><input name="key-change-submit" type="submit" class="button-primary" value="Complete Key Change" /></form></div>';
			echo "</form>";
		}

		if ($_POST['agreement']) {
			$this->key_change_vault();
			$this->key_change_audit();

			echo '<div if="message" class="updated"><p>Process complete.  Please review the output below (if any).  Remove the old key from the settings page once any output has been reviewed and any problems have been addressed.</p></div>';
		} else {
			echo '<div if="message" class="error"><p>You must have a good and valid backup before taking this action!</p></div>';
		}
	}

	function key_change_vault() {
		global $wpdb;
		$options = get_option('password_vault');

		$sql = "select vault_id, username, password
			from {$wpdb->prefix}password_vault_vault
			order by vault_id";
	
		$accounts = $wpdb->get_results($sql);
		$password_vault_tools = new password_vault_tools();

		if ($accounts) {
			foreach ($accounts as $account) {
				$text = $this->decrypt_value_specific_key($account->password, $options['oldkey']);
				if ($text) {
					$sql = "update {$wpdb->prefix}password_vault_vault set password = %s where vault_id = %d";
					$wpdb->query(
						$wpdb->prepare($sql,
						$password_vault_tools->encrypt_value($text), $account->vault_id)
					);
				} else {
					echo "Error decrypting {$account->username} with ID number {$account->vault_id}.<br>";
				}
			}
		}
	}

	function key_change_audit() {
		global $wpdb;
		$options = get_option('password_vault');

		$sql = "select vault_id, audit_id, old_password, new_password
			from {$wpdb->prefix}password_vault_audit
			order by vault_id";
	
		$accounts = $wpdb->get_results($sql);
		$password_vault_tools = new password_vault_tools();

		if ($accounts) {
			foreach ($accounts as $account) {
				if ($account->old_password) {
					$text = $this->decrypt_value_specific_key($account->old_password, $options['oldkey']);
					if ($text) {
						$sql = "update {$wpdb->prefix}password_vault_audit set old_password = %s where audit_id = %d";
						$wpdb->query(
							$wpdb->prepare($sql,
							$password_vault_tools->encrypt_value($text), $account->audit_id)
						);
					} else {
						echo "Error decrypting the old password for audit record {$account->audit_id}.<br>";
					}
				}


				if ($account->new_password) {
					$text = $this->decrypt_value_specific_key($account->new_password, $options['oldkey']);
					if ($text) {
						$sql = "update {$wpdb->prefix}password_vault_audit set new_password = %s where audit_id = %d";
						$wpdb->query(
							$wpdb->prepare($sql,
							$password_vault_tools->encrypt_value($text), $account->audit_id)
						);
					} else {
						echo "Error decrypting the new password for audit record {$account->audit_id}.<br>";
					}
				}


			}
		}
	}


	function decrypt_value_specific_key ($encrypted, $key) {
		$text = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

		return $text;
	}

	function footer() {
		$password_vault_main = new password_vault_main();
		$password_vault_main->footer();
	}

	function get_source_page() {
		$password_vault_main = new password_vault_main();
		$page = $password_vault_main->get_source_page('settings');
		return $page;
	}
}