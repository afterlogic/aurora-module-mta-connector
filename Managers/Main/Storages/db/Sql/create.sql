CREATE TABLE IF NOT EXISTS `awm_accounts` (
	`id_acct` INT(11) NOT NULL AUTO_INCREMENT,
	`id_user` INT(11) NOT NULL DEFAULT '0',
	`id_domain` INT(11) NOT NULL DEFAULT '0',
	`id_tenant` INT(11) NOT NULL DEFAULT '0',
	`deleted` TINYINT(1) NOT NULL DEFAULT '0',
	`mail_quota` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`email` VARCHAR(255) NOT NULL DEFAULT '',
	`mail_inc_login` VARCHAR(255) NULL DEFAULT NULL,
	`mail_inc_pass` VARCHAR(255) NULL DEFAULT NULL,
	`mailing_list` TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id_acct`),
	UNIQUE INDEX `mail_inc_login` (`mail_inc_login`),
	INDEX `AWM_ACCOUNTS_ID_USER_INDEX` (`id_user`),
	INDEX `AWM_ACCOUNTS_ID_ACCT_ID_USER_INDEX` (`id_acct`, `id_user`),
	INDEX `AWM_ACCOUNTS_MAIL_INC_LOGIN_INDEX` (`mail_inc_login`),
	INDEX `AWM_ACCOUNTS_EMAIL_INDEX` (`email`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `awm_account_quotas` (
	`name` VARCHAR(100) NOT NULL DEFAULT '',
	`mail_quota_usage_bytes` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`quota_usage_messages` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	INDEX `AWM_ACCOUNT_QUOTAS_NAME_INDEX` (`name`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `awm_domains` (
	`id_domain` INT(11) NOT NULL AUTO_INCREMENT,
	`id_tenant` INT(11) NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NULL DEFAULT NULL,
	`mail_user_quota` INT(11) NOT NULL DEFAULT '0',
	`total_user_quota` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id_domain`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `awm_mailaliases` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_acct` INT(11) NULL DEFAULT NULL,
	`alias_name` VARCHAR(255) NOT NULL DEFAULT '',
	`alias_domain` VARCHAR(255) NOT NULL DEFAULT '',
	`alias_to` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `AWM_MAILALIASES_ID_ACCT_INDEX` (`id_acct`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `awm_mailforwards` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_acct` INT(11) NULL DEFAULT NULL,
	`forward_name` VARCHAR(255) NOT NULL DEFAULT '',
	`forward_domain` VARCHAR(255) NOT NULL DEFAULT '',
	`forward_to` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `AWM_MAILFORWARDS_ID_ACCT_INDEX` (`id_acct`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `awm_mailinglists` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`id_acct` INT(11) NULL DEFAULT NULL,
	`list_name` VARCHAR(255) NOT NULL DEFAULT '',
	`list_to` VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`),
	INDEX `AWM_MAILINGLISTS_ID_ACCT_INDEX` (`id_acct`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
