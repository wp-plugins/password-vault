<?
class password_vault_hosting {

	function customer_hosting_menu() {
		global $wpdb;
		$options = get_option('password_vault');
		$password_vault_hosting = new password_vault_hosting();
		$user_id = get_current_user_id();

		$count = $wpdb->query("select count(*) from {$wpdb->prefix}password_vault_group_users where user_id = {$user_id} and group_id = {$options['group_membership_group']}");
		
		if ($count !=0)
		{
			add_submenu_page('password_vault_top', 'Password Vault Group Management', 'Password Vault Group Management', 'read', 'password_vault_group_management', array($password_vault_hosting, 'group_settings_menu'));
		}
	}

	function hosting_settings() {
		add_settings_field('group_membership_group', __('Group To Manage Groups:', ''), array(&$this, 'group_membership_group'), 'password_vault', 'password_vault_security');
	}

	function group_membership_group() {
		global $wpdb;
		$options = get_option('password_vault');

		$sql = "select * from {$wpdb->prefix}password_vault_groups order by group_name";

		$groups = $wpdb->get_results($sql);

		if ($groups) {
			echo "<select name='password_vault[group_membership_group]'>";
			foreach ($groups as $group) {
				echo "<option value='{$group->group_id}' {$this->is_match($group->group_id, $options['group_membership_group'], 'selected')}>{$group->group_name}</option>";
			}
			echo "</select>";
		}
	}

	function group_settings_menu() {
		$password_vault_settings = new password_vault_settings();

		$password_vault_settings->footer();

		$options = get_option('password_vault');

		$page = $password_vault_settings->get_source_page();

		echo "<br><form name='action_to_take' method='post' action='./admin.php?page=password_vault_group_management'>";
		echo "<input type='submit' value='Group Management' name='action' class='button-secondary'>&nbsp;";
		echo "<input type='submit' value='Group Membership' name='action' class='button-secondary'>";

		echo "</form>";

		if ($_POST['action']) {
			$action = $_POST['action'];
		} else {
			$action=$_GET['action'];
		}

		$password_vault_groups = new password_vault_groups();

		echo '<div class="wrap">';
		echo '<H2>Password Vault Settings</H2>';

		if ($action=='Group Membership' || $action=='group_membership') {
			$password_vault_groups->group_membership();
		} else {
			$password_vault_groups->group_management();
		}
	}

	function is_match($v1, $v2, $return, $debug=0) {
		$groups = new password_vault_groups();
		return $groups->is_match($v1, $v2, $return, $debug);
	}
}