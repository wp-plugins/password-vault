<?php

class password_vault_tools {

	function show_tools_page() {
		$options = get_option('password_vault');
		if ($options['ssl_only'] && !is_ssl()) {
			echo '<div if="message" class="error"><p>This application is configured to require SSL.  You have connected to this website without using SSL. Please connect using HTTPS:// instead of HTTP:// in order to use this application or contact your administrator to disable this requirement.</p></div>';
			return;
		}

		if (get_current_user_id()==0) {
			echo '<div if="message" class="error"><p>This application is only available to logged on users.  Please <a href="/wp-admin/">login</a> to the site to use this application.</p></div>';
			return;

		}

		$this->show_menu();

	}

	function insert_audit($vault_id, $action, $new_username = NULL, $new_password = NULL, $bypass = NULL) {

		$options = get_option('password_vault');
		if (!$bypass && !$options['auditing']) {
			return;
		}

		global $wpdb;
		$old_username = null;
		$old_password = null;

		if ($new_username || $new_password) {
			$row=$wpdb->get_row(
				$wpdb->prepare("select username, password 
from {$wpdb->prefix}password_vault_vault
where vault_id = %d",
				$vault_id)
			);

			if ($row) {
				$old_username = $row->username;
				$old_password = $row->password;
			}
			
		}

		if ($new_password) {
			$new_password = $this->encrypt_value($new_password);
		}

		$wpdb->query(
			$wpdb->prepare("insert into {$wpdb->prefix}password_vault_audit (vault_id, create_date, user_id, action, old_username, new_username, old_password, new_password) VALUES (%d, now(), %d, %s, %s, %s, %s, %s)", 
			$vault_id, get_current_user_id(), $action, $old_username, $new_username, $old_password, $new_password)
		);

	}

	function show_menu() {

		if ($_POST['action'] <> '') {
			$action = $_POST['action'];
		} elseif ($_GET['action'] <> '') {
			$action = $_GET['action'];
		}

		echo "<br><form name='action_to_take' method='post'><input type='submit' value='Add Account' name='action' class='button-secondary'>";

		echo "<input type='submit' value='Find Account' name='action' class='button-secondary'>";

		echo "</form>";

		if ($action=='Add Account') {
			$this->add_account();
		}elseif ($action=='Find Account') {
			$this->find_account();
			if ($_POST['sub_action']=='search') {
				$this->run_search();
			}
		}elseif ($action=='show') {
			if ($_POST['sub_action']=='update') {
				$status=0;
				$this->save_account('update', $status);
			}
			$this->show_account();
		}elseif ($action=='edit_permissions') {
			if ($_POST['sub_action']=='update') {
				$this->update_permissions();
			}
			$this->show_permissions();
		}elseif ($action=='view_archive') {
			$this->view_archive();
		}
		else {
			$this->base_display();
		}

		$this->footer();
		
	}

	function view_archive() {
		global $wpdb;

		$sql = "select v.audit_id, v.create_date, u.user_login, v.action, case when strcmp(v.old_username, v.new_username) then ', Username Changed' end UsernameChanged, case when strcmp(v.old_password, v.new_password) then ', Password Changed' end PasswordChanged
from {$wpdb->prefix}password_vault_audit v
join {$wpdb->users} u ON v.user_id = u.ID
WHERE v.vault_id IN (%d, 0)
ORDER BY v.create_date desc, audit_id desc";

		$this->insert_audit($_GET['vault_id'], 'view_audit');

		$audits = $wpdb->get_results(
				$wpdb->prepare($sql,
				array($_GET['vault_id']))
			);

		if ($audits) {
			echo "<p><table border='1' cellpadding='1' cellspacing='0'><tr><td align='center'><b>Audit ID</b></td><td align='center'><b>Create Date</b></td><td align='center'><b>Login Name</b></td><td align='center'><b>Action Taken</b></td></tr>";
			foreach ($audits as $audit) {
				echo "<tr><td>{$audit->audit_id}</td><td>{$audit->create_date}</td><td>{$audit->user_login}</td><td>{$audit->action}{$audit->UsernameChanged}{$audit->PasswordChanged}</td></tr>";
			}
			echo "</table>";
		} else {
			echo "No audit records found.";
		}

	}

	function add_account() {

		if ($_POST['sub_action']=='process'){
			$this->save_account($_POST['sub_action2'], $status);
		}

		$options = get_option('password_vault');

		echo "<form name='new_account' method='post'>";
		echo "<input type='hidden' name='action' value='Add Account'>";
		echo "<input type='hidden' name='sub_action' value='process'>";
		if ($status=='failed' || $status=='') {
			echo "<input type='hidden' name='sub_action2' value='new'>";
		} else {
			echo "<input type='hidden' name='sub_action2' value='update'>";
			echo "<input type='hidden' name='username' value='{$_POST['username']}'>";
		}
		echo "<table>";
		echo "<tr><td>User Name:</td><td><input type='text' name='username' value='{$_POST['username']}'";
		if ($status=='success') {
			echo ' disabled';
		}
		echo ">*</td></tr>";
		echo "<tr><td>Password:</td><td><input type='text' name='password' value='{$_POST['password']}'>*</td></tr>";

		if (!empty($options['label1'])) {
			echo "<tr><td>{$options['label1']}:</td><td><input type='text' name='label1' value='{$_POST['label1']}'>";
			if (!empty($options['label1_req'])) {
				echo "*";
			}
			echo "</td></tr>";
		}

		if (!empty($options['label2'])) {
			echo "<tr><td>{$options['label2']}:</td><td><input type='text' name='label2' value='{$_POST['label2']}'>";
			if (!empty($options['label2_req'])) {
				echo "*";
			}
			echo "</td></tr>";
		}


		if (!empty($options['label3'])) {
			echo "<tr><td>{$options['label3']}:</td><td><input type='text' name='label3' value='{$_POST['label3']}'>";
			if (!empty($options['label3_req'])) {
				echo "*";
			}
			echo "</td></tr>";
		}

		if (!empty($options['label4'])) {
			echo "<tr><td>{$options['label4']}:</td><td><input type='text' name='label4' value='{$_POST['label4']}'>";
			if (!empty($options['label4_req'])) {
				echo "*";
			}
			echo "</td></tr>";
		}


		if (!empty($options['label5'])) {
			echo "<tr><td>{$options['label5']}:</td><td><input type='text' name='label5' value='{$_POST['label5']}'>";
			if (!empty($options['label5_req'])) {
				echo "*";
			}
			echo "</td></tr>";
		}

		echo "</table>";
		echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="Save Changes"';
		if ($status=='success') {
			echo ' disabled';
		}
		echo ' /></form>';
		echo "<BR> Fields with an * are required.";

	}

	function save_account($command, &$status) {

		global $wpdb;

		$options = get_option('password_vault');

		if (!$_POST['username']) {
			$error=true;
			echo '<div if="message" class="error"><p>User Name value must be set.</p></div>';
		}
		if (!$_POST['password']) {
			$error=true;
			echo '<div if="message" class="error"><p>Password value must be set.</p></div>';
		}
		if (!empty($options['label1']) && !empty($options['label1_req'])) {
			if (!$_POST['label1']) {	
				$error=true;
				echo "<div if='message' class='error'><p>{$options['label1']} value must be set.</p></div>";
			}
		}

		if (!empty($options['label2']) && !empty($options['label2_req'])) {
			if (!$_POST['label2']) {	
				$error=true;
				echo "<div if='message' class='error'><p>{$options['label2']} value must be set.</p></div>";
			}
		}

		if (!empty($options['label3']) && !empty($options['label3_req'])) {
			if (!$_POST['label3']) {	
				$error=true;
				echo "<div if='message' class='error'><p>{$options['label3']} value must be set.</p></div>";
			}
		}

		if (!empty($options['label4']) && !empty($options['label4_req'])) {
			if (!$_POST['label4']) {	
				$error=true;
				echo "<div if='message' class='error'><p>{$options['label4']} value must be set.</p></div>";
			}
		}

		if (!empty($options['label5']) && !empty($options['label5_req'])) {
			if (!$_POST['label5']) {	
				$error=true;
				echo "<div if='message' class='error'><p>{$options['label5']} value must be set.</p></div>";
			}
		}
		
		if ($error) {
			$status='failed';
			return;
		}

		if ($command == 'new') {
			$user_id = get_current_user_id();

			$wpdb->query(
				$wpdb->prepare("insert into {$wpdb->prefix}password_vault_vault (username, password, label1, label2, label3, label4, label5, create_date, create_by, modify_date, modify_by) values (%s, %s, %s, %s, %s, %s, %s, now(), %d, null, null)",
				$_POST['username'], $this->encrypt_value($_POST['password']), $_POST['label1'], $_POST['label2'], $_POST['label3'], $_POST['label4'], $_POST['label5'], $user_id)
			);

				$vault_id = $wpdb->get_var("SELECT MAX(vault_id) FROM {$wpdb->prefix}password_vault_vault");

			$sql = "insert into {$wpdb->prefix}password_vault_user_permissions (user_id, vault_id, read_per, write_per, owner_per) values ({$user_id}, {$vault_id}, 1,1,1)";
			$wpdb->query($sql);

			echo "<div if='message' class='updated'><p>{$_POST['username']} has been added with an ID of {$vault_id}.</p></div>";

			$status='success';
		}

		if ($command == 'update') {
			
			$this->insert_audit($_POST['vault_id'], 'update', $_POST['username'], $_POST['password']);

			$wpdb->query(
				$wpdb->prepare("update {$wpdb->prefix}password_vault_vault set username = %s, password = %s, label1 = %s, label2 = %s, label3 = %s, label4 = %s, label5 = %s, modify_date=now(), modify_by=%d WHERE vault_id = %d",
				$_POST['username'], $this->encrypt_value($_POST['password']), $_POST['label1'], $_POST['label2'], $_POST['label3'], $_POST['label4'], $_POST['label5'], get_current_user_id(), $_POST['vault_id'])
			);
			echo "<div if='message' class='updated'><p>{$_POST['username']} has been updated.</p></div>";

			$status='success';
		}
	}

	function encrypt_value ($text) {
		$key = SECURE_AUTH_KEY;
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $text, MCRYPT_MODE_CBC, md5(md5($key))));

		return $encrypted;
	}

	function decrypt_value ($encrypted) {
		$key = SECURE_AUTH_KEY;
		$text = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");

		return $text;
	}

	function find_account() {
		$options = get_option('password_vault');

		echo "<form name='new_account' method='post'>";
		echo "<input type='hidden' name='action' value='Find Account'>";
		echo "<input type='hidden' name='sub_action' value='search'>";
		echo "<table><tr><td>UserName:</td><td><input type='text' name='username' value='{$_POST['username']}'></td></tr>";

		if (!empty($options['label1'])) {
			echo "<tr><td>{$options['label1']}</td><td><input type='text' name='label1' value='{$_POST['label1']}'></td></tr>";
		}

		if (!empty($options['label2'])) {
			echo "<tr><td>{$options['label2']}</td><td><input type='text' name='label2' value='{$_POST['label2']}'></td></tr>";
		}

		if (!empty($options['label3'])) {
			echo "<tr><td>{$options['label3']}</td><td><input type='text' name='label3' value='{$_POST['label3']}'></td></tr>";
		}

		if (!empty($options['label4'])) {
			echo "<tr><td>{$options['label4']}</td><td><input type='text' name='label4' value='{$_POST['label4']}'></td></tr>";
		}

		if (!empty($options['label5'])) {
			echo "<tr><td>{$options['label5']}</td><td><input type='text' name='label5' value='{$_POST['label5']}'></td></tr>";
		}
		echo "</table>";
		echo '<p class="submit"><input name="submit" type="submit" class="button-primary" value="Search"></p></form>';
	}

	function run_search() {
		global $wpdb;
		$options = get_option('password_vault');

		$user_id = get_current_user_id();
		$username = "{$_POST['username']}%";

		$sql = "select v.*, up.read_per user_read_per, up.write_per user_write_per, up.owner_per user_owner_per, gp.read_per group_read_per, gp.write_per group_write_per, gp.owner_per group_owner_per
			from {$wpdb->prefix}password_vault_vault v
			left outer join {$wpdb->prefix}password_vault_user_permissions up on v.vault_id = up.vault_id
				and up.user_id = {$user_id}
			left outer join {$wpdb->prefix}password_vault_group_permissions gp on v.vault_id = gp.vault_id
			left outer join {$wpdb->prefix}password_vault_group_users gu on gp.group_id = gu.group_id
				and gu.user_id = {$user_id}
			where username LIKE %s";

		if ($_POST['label1']) {
			$sql="{$sql} and label1 LIKE %s";
		}
		if ($_POST['label2']) {
			$sql="{$sql} and label2 LIKE %s";
		}
		if ($_POST['label3']) {
			$sql="{$sql} and label3 LIKE %s";
		}
		if ($_POST['label4']) {
			$sql="{$sql} and label4 LIKE %s";
		}
		if ($_POST['label5']) {
			$sql="{$sql} and label5 LIKE %s";
		}

		$values = array($username);

		if ($_POST['label1']) {
			array_push($values, $_POST['label1']);
		}

		if ($_POST['label2']) {
			array_push($values, $_POST['label2']);
		}

		if ($_POST['label3']) {
			array_push($values, $_POST['label3']);
		}

		if ($_POST['label4']) {
			array_push($values, $_POST['label4']);
		}

		if ($_POST['label5']) {
			array_push($values, $_POST['label5']);
		}

		$accounts = $wpdb->get_results(
				$wpdb->prepare($sql,
				$values)
			);

		if ($accounts)
		{
			echo "<table border='1' cellpadding='0' cellspacing='0'><tr><td>User Name</td>";
			if (!empty($options['label1'])) {
				echo "<td>{$options['label1']}</td>";
			}
			if (!empty($options['label2'])) {
				echo "<td>{$options['label2']}</td>";
			}
			if (!empty($options['label3'])) {
				echo "<td>{$options['label3']}</td>";
			}
			if (!empty($options['label4'])) {
				echo "<td>{$options['label4']}</td>";
			}
			if (!empty($options['label5'])) {
				echo "<td>{$options['label5']}</td>";
			}
			echo "<td>View Audit</td></tr>";
			foreach ($accounts as $account)
			{
				echo "<tr><td>";
				if ($this->effective_permission($account, 'read')==1) {
					echo "<a href='./tools.php?page=password_vault&vault_id={$account->vault_id}&action=show'>";
				}
				echo $account->username;
				if ($this->effective_permission($account, 'read')==1) {
					echo "</a>";
				}
				echo "</td>";
				if (!empty($options['label1'])) {
					echo "<td>{$account->label1}</td>";
				}
				if (!empty($options['label2'])) {
					echo "<td>{$account->label2}</td>";
				}
				if (!empty($options['label3'])) {
					echo "<td>{$account->label3}</td>";
				}
				if (!empty($options['label4'])) {
					echo "<td>{$account->label4}</td>";
				}
				if (!empty($options['label5'])) {
					echo "<td>{$account->label5}</td>";
				}
				echo "<td><a href=
'tools.php?page=password_vault&action=view_archive&vault_id={$account->vault_id}'
><img src='../wp-content/plugins/password-vault/archive.png' width='20' height='20' border='0'></a></td>";
				echo "</tr>";
			}
			echo "</table>";
		} else {
			echo "No records found.";
		}
	}

	function show_account() {
		global $wpdb;
		$options = get_option('password_vault');

		$user_id = get_current_user_id();
		if ($_POST['vault_id']) {
			$vault_id = $_POST['vault_id'];
		} elseif ($_GET['vault_id']) {
			$vault_id = $_GET['vault_id'];
		}

		$sql = "select v.*, up.read_per user_read_per, up.write_per user_write_per, up.owner_per user_owner_per, gp.read_per group_read_per, gp.write_per group_write_per, gp.owner_per group_owner_per
			from {$wpdb->prefix}password_vault_vault v
			left outer join {$wpdb->prefix}password_vault_user_permissions up on v.vault_id = up.vault_id
				and up.user_id = {$user_id}
			left outer join {$wpdb->prefix}password_vault_group_permissions gp on v.vault_id = gp.vault_id
			left outer join {$wpdb->prefix}password_vault_group_users gu on gp.group_id = gu.group_id
				and gu.user_id = {$user_id}
			where v.vault_id = %d";

		$values = array($vault_id);

		$account = $wpdb->get_row(
				$wpdb->prepare($sql,
				$values)
			);



		if ($account) {
			if ($this->effective_permission($account, 'write')==1) {
				echo "<form name='update_account' method='post'>";
				echo "<input type='hidden' name='action' value='show'>";
				echo "<input type='hidden' name='sub_action' value='update'>";
			}

			$this->insert_audit($vault_id, 'select');

			echo "<table>";
			echo "<tr><td>User Name:</td><td><input type='text' name='username' value='{$this->clean_text($account->username)}'";
			if ($status=='success') {
				echo ' disabled';
			}
			echo ">*</td></tr>";
			echo "<tr><td>Password:</td><td>";
			if ($this->effective_permission($account, 'read')==1) {
				echo "<input type='text' name='password' value='{$this->decrypt_value($account->password)}'>*";
			} else {
				echo "Password not available to this user.";
			}
			echo "</td><tr>";
			if (!empty($options['label1'])) {
				echo "<tr><td>{$options['label1']}:</td><td><input type='text' name='label1' value='{$account->label1}'>";
			if (!empty($options['label1_req'])) {
				echo "*";
			}
			echo "</td></tr>";
			}
			if (!empty($options['label2'])) {
				echo "<tr><td>{$options['label2']}:</td><td><input type='text' name='label2' value='{$account->label2}'>";
			if (!empty($options['label2_req'])) {
				echo "*";
			}
			echo "</td></tr>";
			}
			if (!empty($options['label3'])) {
				echo "<tr><td>{$options['label3']}:</td><td><input type='text' name='label3' value='{$account->label3}'>";
			if (!empty($options['label3_req'])) {
				echo "*";
			}
			echo "</td></tr>";
			}
			if (!empty($options['label4'])) {
				echo "<tr><td>{$options['label4']}:</td><td><input type='text' name='label4' value='{$account->label4}'>";
			if (!empty($options['label4_req'])) {
				echo "*";
			}
			echo "</td></tr>";
			}
			if (!empty($options['label5'])) {
				echo "<tr><td>{$options['label5']}:</td><td><input type='text' name='label5' value='{$account->label5}'>";
			if (!empty($options['label5_req'])) {
				echo "*";
			}
			echo "</td></tr>";
			}
			echo "</table>";
			if ($this->effective_permission($account, 'write')==1) {
				echo "<input type='hidden' name='vault_id' value='{$account->vault_id}'>";
				echo '<div class="submit"><input name="submit" type="submit" class="button-primary" value="Save Changes"></div></form>';
				echo "Fields with an * are required.";
			}
			if ($this->effective_permission($account, 'owner')==1) {
				echo "<form name='edit_permissions' method='post'>";
				echo '<div class="submit"><input name="submit" type="submit" class="button-primary" value="Edit Permissions"></div>';
				echo "<input type='hidden' name='action' value='edit_permissions'>";
				echo "<input type='hidden' name='vault_id' value='{$account->vault_id}'></form>";

			}
		} else {
			echo '<div if="message" class="error"><p>Invalid Account Identified!</p></div>';
		}
	}

	function show_permissions() {
		global $wpdb;
		$options = get_option('password_vault');

		$user_id = get_current_user_id();

		$sql = "select v.*, up.read_per user_read_per, up.write_per user_write_per, up.owner_per user_owner_per, gp.read_per group_read_per, gp.write_per group_write_per, gp.owner_per group_owner_per
			from {$wpdb->prefix}password_vault_vault v
			left outer join {$wpdb->prefix}password_vault_user_permissions up on v.vault_id = up.vault_id
				and up.user_id = {$user_id}
			left outer join {$wpdb->prefix}password_vault_group_permissions gp on v.vault_id = gp.vault_id
			left outer join {$wpdb->prefix}password_vault_group_users gu on gp.group_id = gu.group_id
				and gu.user_id = {$user_id}
			where v.vault_id = %d";

		$values = array($_GET['vault_id']);

		$account = $wpdb->get_row(
				$wpdb->prepare($sql,
				$values)
			);

		if ($account) {
			if ($this->effective_permission($account, 'owner')==1) {
				$sql = "select u.ID, u.user_login, case when up.read_per = 1 then 'checked' else '' end user_read_per, case when up.write_per = 1 then 'checked' else '' end user_write_per, case when up.owner_per = 1 then 'checked' else '' end user_owner_per
				from {$wpdb->users} u
				left outer join {$wpdb->prefix}password_vault_user_permissions up ON up.user_id = u.ID
				and up.vault_id = %d";

				$values = array($_GET['vault_id']);

				$permissions = $wpdb->get_results(
					$wpdb->prepare($sql,
					$values)
				);
				echo "<table>";
				if ($permissions) {
					echo "<form name='edit_permissions' method='post'>";
					
					echo "<input type='hidden' name='action' value='edit_permissions'>";
					echo "<input type='hidden' name='sub_action' value='update'>";
					echo "<input type='hidden' name='vault_id' value='{$account->vault_id}'>";
					echo "<tr><td align='center'><b>User Name</b></td><td align='center'><b>Read</b></td><td align='center'><b>Write</b></td><td align='center'><b>Owner</b></td></tr>";
					foreach ($permissions as $permission) {
						echo "<tr><td>{$permission->user_login}</td>";
						echo "<td align='center'><input type='checkbox' name='{$permission->user_login}-read' value='1' {$permission->user_read_per}";
						if ($permission->ID==get_current_user_id()) {
							echo " disabled";
						}
						echo "></td>";
						echo "<td align='center'><input type='checkbox' name='{$permission->user_login}-write' value='1' {$permission->user_write_per}";
						if ($permission->ID==get_current_user_id()) {
							echo " disabled";
						}
						echo "></td>";
						echo "<td align='center'><input type='checkbox' name='{$permission->user_login}-owner' value='1' {$permission->user_owner_per}";
						if ($permission->ID==get_current_user_id()) {
							echo " disabled";
						}
						echo ">";
						if ($permission->ID==get_current_user_id()) {
							echo "<input type='hidden' name='{$permission->user_login}-owner' value='1'>";
						}
						echo "</td></tr>";
					}
					//echo "</table>";

				$sql = "select g.group_name, g.group_id, case when up.read_per = 1 then 'checked' else '' end group_read_per, case when up.write_per = 1 then 'checked' else '' end group_write_per, case when up.owner_per = 1 then 'checked' else '' end group_owner_per
				from {$wpdb->prefix}password_vault_groups g
				left outer join {$wpdb->prefix}password_vault_group_permissions up ON up.group_id = g.group_id
				and up.vault_id = %d";

				$values = array($_GET['vault_id']);

				$permissions = $wpdb->get_results(
					$wpdb->prepare($sql,
					$values)
				);

				if ($permissions) {
					echo "<tr><td align='center'><b>Group Name</b></td><td align='center'><b>Read</b></td><td align='center'><b>Write</b></td><td align='center'><b>Owner</b></td></tr>";
					foreach ($permissions as $permission) {
						echo "<tr><td>{$permission->group_name}</td>";
						echo "<td align='center'><input type='checkbox' name='group-{$permission->group_id}-read' value='1' {$permission->group_read_per}></td>";
						echo "<td align='center'><input type='checkbox' name='group-{$permission->group_id}-write' value='1' {$permission->group_write_per}></td>";
						echo "<td align='center'><input type='checkbox' name='group-{$permission->group_id}-owner' value='1' {$permission->group_owner_per}></td></tr>";
					}
				}
					echo "</table>";
					echo '<div class="submit"><input name="submit" type="submit" class="button-primary" value="Save Permissions"></div></form>';
				}
			} else {
				echo '<div if="message" class="error"><p>Invalid Permissions To Edit Permissions For This Account!</p></div>';
			}
		} 
	}

	function update_permissions() {
		global $wpdb;
		$options = get_option('password_vault');

		$user_id = get_current_user_id();

		$sql = "select ID, user_login
		from {$wpdb->users}
		WHERE ID <> {$user_id}";

		$users = $wpdb->get_results($sql);

		if ($users) {
			$vault_id = $_POST['vault_id'];
			foreach ($users as $user) {
				$name_read = "{$user->user_login}-read";
				$name_write = "{$user->user_login}-write";
				$name_owner = "{$user->user_login}-owner";

				$read = $_POST[$name_read];
				$write = $_POST[$name_write];
				$owner = $_POST[$name_owner];

				if ($owner) {
					$read=true;
					$write=true;
				} else {
					$owner = 0;
				}
				if ($write) {
					$read=true;
				} else {
					$write = 0;
				}

				$sql = "delete from {$wpdb->prefix}password_vault_user_permissions where user_id=%d and vault_id=%d";
				$values = array($user->ID, $_POST['vault_id']);
				$wpdb->query(
					$wpdb->prepare($sql,
					$values)
				);

				if ($read) {
					$sql = "insert into {$wpdb->prefix}password_vault_user_permissions (user_id, vault_id, read_per, write_per, owner_per) values (%d, %d, {$read}, {$write}, {$owner})";
					$wpdb->query(
						$wpdb->prepare($sql,
						$values)
					);
				}
			}
		}



		$sql = "select group_id, group_name
		from {$wpdb->prefix}password_vault_groups";

		$groups = $wpdb->get_results($sql);

		if ($groups) {
			$vault_id = $_POST['vault_id'];
			foreach ($groups as $group) {
				$name_read = "group-{$group->group_id}-read";
				$name_write = "group-{$group->group_id}-write";
				$name_owner = "group-{$group->group_id}-owner";

				$read = $_POST[$name_read];
				$write = $_POST[$name_write];
				$owner = $_POST[$name_owner];

				if ($owner) {
					$read=true;
					$write=true;
				} else {
					$owner=0;
				}
				if ($write) {
					$read=true;
				} else {
					$write=0;
				}

				$sql = "delete from {$wpdb->prefix}password_vault_group_permissions where group_id=%d and vault_id=%d";
				$values = array($group->group_id, $_POST['vault_id']);
				$permissions = $wpdb->get_results(
					$wpdb->prepare($sql,
					$values)
				);
				if ($read) {
					$sql = "insert into {$wpdb->prefix}password_vault_group_permissions (group_id, vault_id, read_per, write_per, owner_per) values (%d, %d, {$read}, {$write}, {$owner})";
					$wpdb->query(
						$wpdb->prepare($sql,
						$values)
					);
				}
			}
		}
		echo "<div if='message' class='updated'><p>Permissions have been updated.</p></div>";

	}

	function effective_permission ($account, $target_level) {
		$user_read_per=$account->user_read_per;
		$user_write_per=$account->user_write_per;
		$user_owner_per=$account->user_owner_per;
		$group_read_per=$account->group_read_per;
		$group_write_per=$account->group_write_per;
		$group_owner_per=$account->group_owner_per;

		$effective_permission=0;
		if ($target_level=='read') {
			if ($user_read_per==1 || $group_read_per ==1 || $user_write_per==1 || $group_write_per==1 || $user_owner_per==1 || $group_owner_per==1) {
				$effective_permission=1;
			}
		}

		if ($target_level=='write') {
			if ($user_write_per==1 || $group_write_per==1 || $user_owner_per==1 || $group_owner_per==1) {
				$effective_permission=1;
			}
		}

		if ($target_level=='owner') {
			if ($user_owner_per==1 || $group_owner_per==1) {
				$effective_permission=1;
			}
		}

		return $effective_permission;
	}

	function base_display() {
		echo "<p><p>Please use the button bar above to perform an action.";
	}

	function footer() {
		$password_vault_main = new password_vault_main();
		$password_vault_main->footer();
	}

	function clean_text($string) {
		$patterns = array();
		$replacements = array();

		//$patterns[0] = '\\';
		//$replacements[0] = '\';
		
		$string = str_replace($patterns, $replacements , $string);

		return $string;
	}

}