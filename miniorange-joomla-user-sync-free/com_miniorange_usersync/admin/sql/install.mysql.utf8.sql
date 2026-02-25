CREATE TABLE IF NOT EXISTS `#__miniorange_usersync_customer` (
`id` int(11) UNSIGNED NOT NULL ,
`email` VARCHAR(255)  NOT NULL ,
`password` VARCHAR(255)  NOT NULL ,
`admin_phone` VARCHAR(255)  NOT NULL ,
`customer_key` VARCHAR(255)  NOT NULL ,
`customer_token` VARCHAR(255) NOT NULL,
`api_key` VARCHAR(255)  NOT NULL,
`login_status` tinyint(1) DEFAULT FALSE,
`status` VARCHAR(255)  NOT NULL,
`azure_lk` VARCHAR(255)  NOT NULL,
`registration_status` VARCHAR(255) NOT NULL,
`transaction_id` VARCHAR(255) NOT NULL,
`email_count` int(11),
`sms_count` int(11),
`sso_var` VARCHAR(255) NOT NULL,
`sso_test` VARCHAR(255) NOT NULL,
`uninstall_feedback` int(2) NOT NULL,
`licenseExpiry` TIMESTAMP NULL DEFAULT NULL,
`supportExpiry` TIMESTAMP NULL DEFAULT NULL,
`licensePlan` VARCHAR(64) NOT NULL,
`trists` TEXT NOT NULL,
`miniorange_fifteen_days_before_lexp` tinyint default 0,
`miniorange_five_days_before_lexp` tinyint default 0,
`miniorange_after_lexp` tinyint default 0,
`miniorange_after_five_days_lexp` tinyint default 0,
`miniorange_lexp_notification_sent` tinyint default 0,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `#__miniorange_user_sync_config` (
`id` int(11) UNSIGNED NOT NULL ,
`mo_sync_configuration` text,
`app_name` VARCHAR(255)  NOT NULL ,
`is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
`mo_usersync_user_password` VARCHAR(255)  NOT NULL ,
`mo_usersync_sync_on_joomla_delete` VARCHAR(10) NOT NULL,
`mo_usersync_sync_on_joomla_create` VARCHAR(10) NOT NULL,
`mo_usersync_sync_on_joomla_update` VARCHAR(10) NOT NULL,
`mo_usersync_username_attribute` VARCHAR (255) NOT NULL,
`mo_usersync_email_attribute` VARCHAR (255) NOT NULL,
`mo_usersync_test_attributes` VARCHAR (1064) NOT NULL,
`mo_usersync_user_details` VARCHAR (8080) NOT NULL,
`mo_usersync_user_groups` text NOT NULL,
`mo_usersync_group_mapping` text NOT NULL,
`mo_usersync_test_username` VARCHAR(255) NOT NULL,
`is_email_verified` int(2) NOT NULL,
`mo_joomla_to_usersync_options` text NOT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_joomla_to_provider_config` (
`id` int(11) UNSIGNED NOT NULL ,
`username` VARCHAR(255) NOT NULL,
`email` VARCHAR(255) NOT NULL,
`firstname` VARCHAR(255) NOT NULL,
`lastname` VARCHAR(255) NOT NULL,
`is_email_verified` int(2) NOT NULL,
`sync_options` text NOT NULL,
`user_profile_attributes` text NOT NULL,
`mapping_value_default` VARCHAR(255)  NOT NULL ,
`role_mapping_key_value` text,
`role_mapping_groupvalue` text,
`enable_role_mapping` int(11) UNSIGNED NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_sync_to_joomla` (
`id` int(11) UNSIGNED NOT NULL ,
`moUserAttr` text,
`moName` VARCHAR(255)  NOT NULL ,
`moUsername` VARCHAR(255)  NOT NULL ,
`moEmail` VARCHAR(255)  NOT NULL ,
`moUserList` text,
`mapping_value_default` VARCHAR(255)  NOT NULL ,
`role_mapping_key_value` text,
`role_mapping_groupvalue` text,
`enable_role_mapping` int(11) UNSIGNED NOT NULL ,
`provider_to_joomla_attributes` text,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT IGNORE INTO `#__miniorange_usersync_customer`(`id`,`login_status`) values (1,false);
INSERT IGNORE INTO `#__miniorange_user_sync_config`(`id`) values (1);
INSERT IGNORE INTO `#__miniorange_sync_to_joomla`(`id`) values (1);
INSERT IGNORE INTO `#__miniorange_joomla_to_provider_config`(`id`) values (1);