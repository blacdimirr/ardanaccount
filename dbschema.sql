/*
SQLyog Enterprise - MySQL GUI v8.05 
MySQL - 8.0.30 : Database - account
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `assets` */

CREATE TABLE `assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_date` date NOT NULL,
  `supported_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_active` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_active_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_garantia` date DEFAULT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bank_accounts` */

CREATE TABLE `bank_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `holder_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chart_account_id` int NOT NULL DEFAULT '0',
  `opening_balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bank_transfers` */

CREATE TABLE `bank_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `retainer_id` int NOT NULL,
  `order_id` int NOT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bill_accounts` */

CREATE TABLE `bill_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `chart_account_id` int NOT NULL DEFAULT '0',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bill_payments` */

CREATE TABLE `bill_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int NOT NULL,
  `date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `account_id` int DEFAULT NULL,
  `payment_method` int DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bill_products` */

CREATE TABLE `bill_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int NOT NULL,
  `product_id` int NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `received_quantity` int unsigned NOT NULL DEFAULT '0',
  `tax` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_products_category_id_foreign` (`category_id`),
  CONSTRAINT `bill_products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `bills` */

CREATE TABLE `bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `vender_id` int NOT NULL,
  `supplier_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bill_date` date NOT NULL,
  `due_date` date NOT NULL,
  `order_number` int NOT NULL DEFAULT '0',
  `ncf_type_id` bigint unsigned DEFAULT NULL,
  `ncf_series_id` bigint unsigned DEFAULT NULL,
  `ncf_number` text COLLATE utf8mb4_unicode_ci,
  `itbis_billed_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `itbis_withheld_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `isr_withheld_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` int NOT NULL DEFAULT '0',
  `shipping_display` int NOT NULL DEFAULT '1',
  `send_date` date DEFAULT NULL,
  `discount_apply` int NOT NULL DEFAULT '0',
  `category_id` int NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bills_ncf_type_id_foreign` (`ncf_type_id`),
  KEY `bills_ncf_series_id_foreign` (`ncf_series_id`),
  CONSTRAINT `bills_ncf_series_id_foreign` FOREIGN KEY (`ncf_series_id`) REFERENCES `ncf_series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bills_ncf_type_id_foreign` FOREIGN KEY (`ncf_type_id`) REFERENCES `ncf_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `budgets` */

CREATE TABLE `budgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `income_data` text COLLATE utf8mb4_unicode_ci,
  `expense_data` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `chart_of_account_parents` */

CREATE TABLE `chart_of_account_parents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_type` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `account` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `chart_of_account_sub_types` */

CREATE TABLE `chart_of_account_sub_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `type` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `chart_of_account_types` */

CREATE TABLE `chart_of_account_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `chart_of_accounts` */

CREATE TABLE `chart_of_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` int NOT NULL DEFAULT '0',
  `type` int NOT NULL DEFAULT '0',
  `sub_type` int NOT NULL DEFAULT '0',
  `parent` int NOT NULL DEFAULT '0',
  `is_enabled` int NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1379 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `company_payment_settings` */

CREATE TABLE `company_payment_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `company_payment_settings_name_created_by_unique` (`name`,`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `contract_attachments` */

CREATE TABLE `contract_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `files` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `contract_comments` */

CREATE TABLE `contract_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `comment` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `contract_notes` */

CREATE TABLE `contract_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL,
  `note` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `contract_types` */

CREATE TABLE `contract_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `contracts` */

CREATE TABLE `contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer` int NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `type` int NOT NULL DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `edit_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` longtext COLLATE utf8mb4_unicode_ci,
  `customer_signature` longtext COLLATE utf8mb4_unicode_ci,
  `company_signature` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `credit_notes` */

CREATE TABLE `credit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice` int NOT NULL DEFAULT '0',
  `customer` int NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `custom_field_values` */

CREATE TABLE `custom_field_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `record_id` bigint unsigned NOT NULL,
  `field_id` bigint unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_field_values_record_id_field_id_unique` (`record_id`,`field_id`),
  KEY `custom_field_values_field_id_foreign` (`field_id`),
  CONSTRAINT `custom_field_values_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `custom_fields` */

CREATE TABLE `custom_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `customers` */

CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_by` int NOT NULL DEFAULT '0',
  `is_active` int NOT NULL DEFAULT '1',
  `is_enable_login` int NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `billing_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `debit_notes` */

CREATE TABLE `debit_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill` int NOT NULL DEFAULT '0',
  `vendor` int NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `email_template_langs` */

CREATE TABLE `email_template_langs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `lang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `email_templates` */

CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `expenses` */

CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `ncf_type_id` bigint unsigned DEFAULT NULL,
  `ncf_series_id` bigint unsigned DEFAULT NULL,
  `ncf_number` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date` date DEFAULT NULL,
  `project` bigint unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_ncf_type_id_foreign` (`ncf_type_id`),
  KEY `expenses_ncf_series_id_foreign` (`ncf_series_id`),
  CONSTRAINT `expenses_ncf_series_id_foreign` FOREIGN KEY (`ncf_series_id`) REFERENCES `ncf_series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_ncf_type_id_foreign` FOREIGN KEY (`ncf_type_id`) REFERENCES `ncf_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `failed_jobs` */

CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `goals` */

CREATE TABLE `goals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `is_display` int NOT NULL DEFAULT '1',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `invoice_payments` */

CREATE TABLE `invoice_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `account_id` int NOT NULL,
  `payment_method` int NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `txn_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manually',
  `receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `invoice_products` */

CREATE TABLE `invoice_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `invoices` */

CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `send_date` date DEFAULT NULL,
  `category_id` int NOT NULL,
  `ref_number` text COLLATE utf8mb4_unicode_ci,
  `ncf_type_id` bigint unsigned DEFAULT NULL,
  `ncf_series_id` bigint unsigned DEFAULT NULL,
  `ncf_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `shipping_display` int NOT NULL DEFAULT '1',
  `discount_apply` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_ncf_type_id_foreign` (`ncf_type_id`),
  KEY `invoices_ncf_series_id_foreign` (`ncf_series_id`),
  CONSTRAINT `invoices_ncf_series_id_foreign` FOREIGN KEY (`ncf_series_id`) REFERENCES `ncf_series` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ncf_type_id_foreign` FOREIGN KEY (`ncf_type_id`) REFERENCES `ncf_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `join_us` */

CREATE TABLE `join_us` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `join_us_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `journal_entries` */

CREATE TABLE `journal_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `journal_id` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `journal_items` */

CREATE TABLE `journal_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `journal` int NOT NULL DEFAULT '0',
  `account` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `landing_page_settings` */

CREATE TABLE `landing_page_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `landing_page_settings_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `languages` */

CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `login_details` */

CREATE TABLE `login_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `migrations` */

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `model_has_permissions` */

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `model_has_roles` */

CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `ncf_series` */

CREATE TABLE `ncf_series` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ncf_type_id` bigint unsigned NOT NULL,
  `series` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_number` bigint unsigned NOT NULL,
  `end_number` bigint unsigned NOT NULL,
  `current_number` bigint unsigned DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `status` enum('activo','vencido','agotado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ncf_series_ncf_type_id_foreign` (`ncf_type_id`),
  CONSTRAINT `ncf_series_ncf_type_id_foreign` FOREIGN KEY (`ncf_type_id`) REFERENCES `ncf_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `ncf_types` */

CREATE TABLE `ncf_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ncf_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification_template_langs` */

CREATE TABLE `notification_template_langs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `lang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `variables` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `notification_templates` */

CREATE TABLE `notification_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `password_resets` */

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `payments` */

CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `account_id` int DEFAULT NULL,
  `vender_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int DEFAULT NULL,
  `recurring` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` int DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ncf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `permissions` */

CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3439 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `personal_access_tokens` */

CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_service_categories` */

CREATE TABLE `product_service_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `chart_account_id` int NOT NULL DEFAULT '0',
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#fc544b',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=523 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_service_units` */

CREATE TABLE `product_service_units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `product_services` */

CREATE TABLE `product_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `purchase_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL DEFAULT '0',
  `tax_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int NOT NULL DEFAULT '0',
  `unit_id` int NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_received` int NOT NULL DEFAULT '0',
  `sale_chartaccount_id` int NOT NULL DEFAULT '0',
  `expense_chartaccount_id` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=839 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `proposal_products` */

CREATE TABLE `proposal_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proposal_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `proposals` */

CREATE TABLE `proposals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proposal_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `send_date` date DEFAULT NULL,
  `category_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `discount_apply` int NOT NULL DEFAULT '0',
  `is_convert` int NOT NULL DEFAULT '0',
  `converted_invoice_id` int NOT NULL DEFAULT '0',
  `converted_retainer_id` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `retainer_payments` */

CREATE TABLE `retainer_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `retainer_id` int NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `account_id` int NOT NULL,
  `payment_method` int NOT NULL,
  `receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Manually',
  `txn_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `retainer_products` */

CREATE TABLE `retainer_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `retainer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `retainers` */

CREATE TABLE `retainers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `retainer_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `send_date` date DEFAULT NULL,
  `category_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `discount_apply` int NOT NULL DEFAULT '0',
  `converted_invoice_id` int NOT NULL DEFAULT '0',
  `is_convert` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `retention_certificates` */

CREATE TABLE `retention_certificates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` bigint unsigned NOT NULL,
  `vender_id` bigint unsigned DEFAULT NULL,
  `supplier_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `itbis_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `isr_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `issued_at` date DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `retention_certificates_bill_id_foreign` (`bill_id`),
  KEY `retention_certificates_vender_id_foreign` (`vender_id`),
  CONSTRAINT `retention_certificates_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `retention_certificates_vender_id_foreign` FOREIGN KEY (`vender_id`) REFERENCES `venders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `retention_rules` */

CREATE TABLE `retention_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_category_id` bigint unsigned DEFAULT NULL,
  `itbis_retention_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `isr_retention_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `retention_rules_service_category_id_foreign` (`service_category_id`),
  CONSTRAINT `retention_rules_service_category_id_foreign` FOREIGN KEY (`service_category_id`) REFERENCES `product_service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `revenues` */

CREATE TABLE `revenues` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `account_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `category_id` int NOT NULL,
  `payment_method` int NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `add_receipt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `role_has_permissions` */

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `roles` */

CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `settings` */

CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_name_created_by_unique` (`name`,`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=388 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `statuses` */

CREATE TABLE `statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `statuses_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `stock_reports` */

CREATE TABLE `stock_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL DEFAULT '0',
  `quantity` int NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=975 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `taxes` */

CREATE TABLE `taxes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `template` */

CREATE TABLE `template` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_json` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_tone` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `transaction_lines` */

CREATE TABLE `transaction_lines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` int NOT NULL,
  `reference_sub_id` int NOT NULL,
  `date` date NOT NULL,
  `credit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `debit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `transactions` */

CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account` int NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `description` text COLLATE utf8mb4_unicode_ci,
  `date` date NOT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `payment_id` int NOT NULL DEFAULT '0',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `transfers` */

CREATE TABLE `transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_account` int NOT NULL DEFAULT '0',
  `to_account` int NOT NULL DEFAULT '0',
  `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `date` date NOT NULL,
  `payment_method` int NOT NULL DEFAULT '0',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `user_email_templates` */

CREATE TABLE `user_email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int NOT NULL,
  `user_id` int NOT NULL,
  `is_active` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `users` */

CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `created_by` int NOT NULL DEFAULT '0',
  `delete_status` int NOT NULL DEFAULT '1',
  `is_active` int NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `is_enable_login` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `venders` */

CREATE TABLE `venders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vender_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_by` int NOT NULL DEFAULT '0',
  `is_active` int NOT NULL DEFAULT '1',
  `is_enable_login` int NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `billing_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `shipping_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `balance` decimal(15,2) NOT NULL DEFAULT '0.00',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `webhooks` */

CREATE TABLE `webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;