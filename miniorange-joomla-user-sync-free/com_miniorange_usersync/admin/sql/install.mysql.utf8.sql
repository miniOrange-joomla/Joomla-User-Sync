
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
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `#__miniorange_user_sync_config` (
`id` int(11) UNSIGNED NOT NULL ,
`mo_sync_configuration` text,
`app_name` VARCHAR(255)  NOT NULL ,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `#__miniorange_sync_to_joomla` (
`id` int(11) UNSIGNED NOT NULL ,
`moUserAttr` text,
`moName` VARCHAR(255)  NOT NULL ,
`moUsername` VARCHAR(255)  NOT NULL ,
`moEmail` VARCHAR(255)  NOT NULL ,
`moUserList` text,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;


INSERT IGNORE INTO `#__miniorange_usersync_customer`(`id`,`login_status`) values (1,false);
INSERT IGNORE INTO `#__miniorange_user_sync_config`(`id`) values (1);
INSERT IGNORE INTO `#__miniorange_sync_to_joomla`(`id`) values (1);