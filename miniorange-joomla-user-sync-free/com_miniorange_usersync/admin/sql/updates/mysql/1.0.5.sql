-- Database updates for com_miniorange_usersync
-- Version: 1.0.6

ALTER TABLE `#__miniorange_usersync_customer`
  ADD COLUMN `licenseExpiry` TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN `supportExpiry` TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN `licensePlan` VARCHAR(64) NOT NULL,
  ADD COLUMN `trists` TEXT NOT NULL,
  ADD COLUMN `miniorange_fifteen_days_before_lexp` TINYINT DEFAULT 0,
  ADD COLUMN `miniorange_five_days_before_lexp` TINYINT DEFAULT 0,
  ADD COLUMN `miniorange_after_lexp` TINYINT DEFAULT 0,
  ADD COLUMN `miniorange_after_five_days_lexp` TINYINT DEFAULT 0,
  ADD COLUMN `miniorange_lexp_notification_sent` TINYINT DEFAULT 0;

ALTER TABLE `#__miniorange_user_sync_config`
  ADD COLUMN `is_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `mo_usersync_user_password` VARCHAR(255) NOT NULL,
  ADD COLUMN `mo_usersync_sync_on_joomla_delete` VARCHAR(10) NOT NULL,
  ADD COLUMN `mo_usersync_sync_on_joomla_create` VARCHAR(10) NOT NULL,
  ADD COLUMN `mo_usersync_sync_on_joomla_update` VARCHAR(10) NOT NULL,
  ADD COLUMN `mo_usersync_username_attribute` VARCHAR(255) NOT NULL,
  ADD COLUMN `mo_usersync_email_attribute` VARCHAR(255) NOT NULL,
  ADD COLUMN `mo_usersync_test_attributes` VARCHAR(1064) NOT NULL,
  ADD COLUMN `mo_usersync_user_details` VARCHAR(8080) NOT NULL,
  ADD COLUMN `mo_usersync_user_groups` TEXT NOT NULL,
  ADD COLUMN `mo_usersync_group_mapping` TEXT NOT NULL,
  ADD COLUMN `mo_usersync_test_username` VARCHAR(255) NOT NULL,
  ADD COLUMN `is_email_verified` INT(2) NOT NULL,
  ADD COLUMN `mo_joomla_to_usersync_options` TEXT NOT NULL;

CREATE TABLE IF NOT EXISTS `#__miniorange_joomla_to_provider_config` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `firstname` VARCHAR(255) NOT NULL,
  `lastname` VARCHAR(255) NOT NULL,
  `is_email_verified` int(2) NOT NULL,
  `sync_options` text NOT NULL,
  `user_profile_attributes` text NOT NULL,
  `mapping_value_default` VARCHAR(255) NOT NULL,
  `role_mapping_key_value` text,
  `role_mapping_groupvalue` text,
  `enable_role_mapping` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_general_ci;

ALTER TABLE `#__miniorange_sync_to_joomla`
  ADD COLUMN `mapping_value_default` VARCHAR(255) NOT NULL,
  ADD COLUMN `role_mapping_key_value` TEXT,
  ADD COLUMN `role_mapping_groupvalue` TEXT,
  ADD COLUMN `enable_role_mapping` INT(11) UNSIGNED NOT NULL,
  ADD COLUMN `provider_to_joomla_attributes` TEXT;


