<?php

class password_vault_groups {

	function group_management() {
		global $wpdb;
		
		$group_id = $_GET['group_id'];

		if ($_GET['sub_action']=='delete') {
			$sql = "delete from {$wpdb->prefix}password_vault_group_users where group_id=%d";
			$wpdb->query(
				$wpdb->prepare($sql, array($group_id))
			);

			$sql = "delete from {$wpdb->prefix}password_vault_group_permissions where group_id = %d";
			$wpdb->query(
				$wpdb->prepare($sql, array($group_id))
			);

			$sql = "delete from {$wpdb->prefix}password_vault_groups where group_id = %d";
			$wpdb->query(
				$wpdb->prepare($sql, array($group_id))
			);

			echo '<div if="message" class="updated"><p>Group has been deleted.</p></div>';
		}

		if ($_POST['sub_action']=='add') {

			if (!$_POST['group_name']) {
				echo "<div if='message' class='error'><p>Group names are required.</p></div>";
			} else {
				$group_name = $_POST['group_name'];
				$sql = "insert into {$wpdb->prefix}password_vault_groups (group_name) values (%s)";
				$wpdb->query(
					$wpdb->prepare($sql, array($group_name))
				);

				echo "<div if='message' class='updated'><p>{$group_name} has been added.</p></div>";
			}
		}

		$sql = "select group_id, group_name
			from {$wpdb->prefix}password_vault_groups
			order by group_name";

		$groups = $wpdb->get_results($sql);

		$page2 = $_GET['page'];
		$folder = plugins_url();
		if ($groups) {
			echo "<table>";
			foreach ($groups as $group) {
				echo "<tr><td><a href='?page={$page2}&action=group_management&sub_action=delete&group_id={$group->group_id}'><img src='{$folder}/password-vault/x.png' width='10' height='10' border='0'></a></td><td>{$group->group_name}</td></tr>";
			}
			echo "</table>";
		}

		$page = $this->get_source_page();


		echo "<form name='new-group' method='post' action='{$page}?page={$page2}&action=group_management'>";
		echo "<input type='hidden' name='sub_action' value='add'>";
		echo "Group Name: <input type='text' name='group_name'><br>";
		echo '<div class="submit"><input name="submit" type="submit" class="button-primary" value="Save Group"></div></form>';
	}

	function group_membership() {
		global $wpdb;
		$sql = "select group_id, group_name
			from {$wpdb->prefix}password_vault_groups
			order by group_name";
		$page = $this->get_source_page();
		$groups = $wpdb->get_results($sql);
		if ($_POST['group_id']) {
			$group_id=$_POST['group_id'];
		} elseif ($_GET['group_id']) {
			$group_id=$_GET['group_id'];
		}

		echo "<form name='new-group' method='post'>";
		echo "<input type='hidden' name='action' value='Group Membership'>";
		echo "Select a group to manage: <select name='group_id'>"; 
		if ($groups) {
			foreach ($groups as $group) {
				echo "<option value='{$group->group_id}'";
				if ($group_id==$group->group_id) {
					echo " selected";
				}
				echo ">{$group->group_name}</option>";
			}
			echo "</select>";
		}
		echo '<div class="submit"><input name="submit" type="submit" class="button-primary" value="Select Group"></div></form>';
		
		if ($_POST['group_id']) {
			$group_id = $_POST['group_id'];
		} elseif ($_GET['group_id']) {
			$group_id = $_GET['group_id'];
		}

		$page2 = $_GET['page'];

		if ($group_id && $_GET['user_id'] && $_GET['sub_action']=='add')
		{
			$sql = "insert into {$wpdb->prefix}password_vault_group_users (group_id, user_id) values (%d, %d)";
			$wpdb->query(
				$wpdb->prepare($sql, array($group_id, $_GET['user_id']))
			);
		}


		if ($group_id && $_GET['user_id'] && $_GET['sub_action']=='delete')
		{
			$sql = "delete from {$wpdb->prefix}password_vault_group_users where group_id = %d and user_id= %d";
			$wpdb->query(
				$wpdb->prepare($sql, array($group_id, $_GET['user_id']))
			);
		}

		if ($group_id) {
			$sql = "select u.ID, u.user_login, case when gu.group_id is null then 0 else 1 end as exists1
				from {$wpdb->users} u
				left outer join {$wpdb->prefix}password_vault_group_users gu ON u.ID = gu.user_id
					and gu.group_id = %d
				order by user_login";

			$users = $wpdb->get_results(
				$wpdb->prepare($sql, array($group_id))
			);
			$folder = plugins_url();
			if ($users) {
				echo "<table border='1' cellspacing='0' cellpadding='0'><tr><td>Add</td><td>Remove</td><td>User Name</td></tr>";
				foreach ($users as $user) {
					echo "<tr><td align='center'>";
					if ($user->exists1 == 0) {
						echo "<a href='./{$page}?page={$page2}&action=group_membership&sub_action=add&group_id={$group_id}&user_id={$user->ID}'><img src='{$folder}/password-vault/check.png' height='20' width='20'></a>";
					}
					echo "</td><td align='center'>";
					if ($user->exists1 == 1) {
						echo "<a href='./{$page}?page={$page2}&action=group_membership&sub_action=delete&group_id={$group_id}&user_id={$user->ID}'><img src='{$folder}/password-vault/x.png' height='13' width='13'></a>";
					}
					echo "</td><td>{$user->user_login}</td></tr>";
				}
				echo "</table>";
			}
				
		}
	}

	function get_source_page() {
		$password_vault_settings = new password_vault_settings();
		$page = $password_vault_settings->get_source_page();
		return $page;
	}

	function is_match($v1, $v2, $return, $debug=0) {
		if ($debug=1) {
			var_dump($v1);
			var_dump($v2);
		}
		if (trim($v1)==trim($v2)) {
			return $return;
		} else {
			return "";
		}
	}

}