<?php

class password_vault_setup {

	function create_db_objects() {
		$this->create_tables();
	}

	function create_tables() {
		global $wpdb;

		$charset_collate = $this->get_charset();

		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_groups`(
			`group_id` int(11) unsigned  NOT NULL AUTO_INCREMENT,
			`group_name` mediumtext NOT NULL,
			PRIMARY KEY (`group_id`)
			) $charset_collate";
		$wpdb->query($sql);

		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_group_users`(
			`group_id` int(11) unsigned  NOT NULL,
			`user_id` bigint(20)  NOT NULL,
			PRIMARY KEY (`group_id`, `user_id`)
			) $charset_collate";
		$wpdb->query($sql);

		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_vault`(
			`vault_id` int(11) unsigned  NOT NULL AUTO_INCREMENT,
			`username` varchar(255) NOT NULL,
			`password` varchar(255) NOT NULL,
			`label1` varchar(50) NOT NULL,
			`label2` varchar(50) NOT NULL,
			`label3` varchar(50) NOT NULL,
			`label4` varchar(50) NOT NULL,
			`label5` varchar(50) NOT NULL,
			`create_date` datetime NOT NULL,
			`modify_date` datetime NULL,
			`create_by` bigint(20) NOT NULL,
			`modify_by` bigint(20) NULL,			
			PRIMARY KEY (`vault_id`),
			INDEX `ix_username` (`username`),
			INDEX `ix_username_label1` (`username`, `label1`),
			INDEX `ix_username_label2` (`username`, `label2`),
			INDEX `ix_username_label3` (`username`, `label3`),
			INDEX `ix_username_label4` (`username`, `label4`),
			INDEX `ix_username_label5` (`username`, `label5`)
			) $charset_collate";
		$wpdb->query($sql);


		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_deleted`(
			`vault_id` int(11) unsigned  NOT NULL,
			`username` varchar(255) NOT NULL,
			`password` varchar(255) NOT NULL,
			`label1` varchar(50) NOT NULL,
			`label2` varchar(50) NOT NULL,
			`label3` varchar(50) NOT NULL,
			`label4` varchar(50) NOT NULL,
			`label5` varchar(50) NOT NULL,
			`create_date` datetime NOT NULL,
			`modify_date` datetime NULL,
			`create_by` bigint(20) NOT NULL,
			`modify_by` bigint(20) NULL,			
			PRIMARY KEY (`vault_id`),
			INDEX `ix_username` (`username`),
			INDEX `ix_username_label1` (`username`, `label1`),
			INDEX `ix_username_label2` (`username`, `label2`),
			INDEX `ix_username_label3` (`username`, `label3`),
			INDEX `ix_username_label4` (`username`, `label4`),
			INDEX `ix_username_label5` (`username`, `label5`)
			) $charset_collate";
		$wpdb->query($sql);

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_audit`(
			`audit_id` bigint(20) unsigned  NOT NULL AUTO_INCREMENT,
			`vault_id` int(11)  unsigned  NOT NULL,
			`create_date` datetime NOT NULL,
			`user_id` bigint(20) NOT NULL,
			`action` varchar(20) NOT NULL,
			`old_username` varchar(20),
			`new_username` varchar(20),
			`old_password` varchar(20),
			`new_password` varchar(20),
			PRIMARY KEY (`audit_id`),
			INDEX ix_vault_id (vault_id, action),
			INDEX ix_user_id (user_id, action)
			) $charset_collate";
		$wpdb->query($sql);

		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_group_permissions`(
			`group_id` int(11) unsigned  NOT NULL,
			`vault_id` int(11)  NOT NULL,
			`read_per` TINYINT(1) NOT NULL,
			`write_per` TINYINT(1) NOT NULL,
			`owner_per` TINYINT(1) NOT NULL,
			PRIMARY KEY (`group_id`, `vault_id`)
			) $charset_collate";
		$wpdb->query($sql);

		$sql = 	"CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}password_vault_user_permissions`(
			`user_id` bigint(20)  NOT NULL,
			`vault_id` int(11)  NOT NULL,
			`read_per` TINYINT(1) NOT NULL,
			`write_per` TINYINT(1) NOT NULL,
			`owner_per` TINYINT(1) NOT NULL,
			PRIMARY KEY (`user_id`, `vault_id`)
			) $charset_collate";
		$wpdb->query($sql);

	}


	function get_charset() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		return $charset_collate;
	}

}