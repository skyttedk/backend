-- phpMyAdmin SQL Dump
-- version 5.1.3-2.el8.remi
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 05. 08 2025 kl. 14:10:00
-- Serverversion: 8.0.42-33
-- PHP-version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gavefabrikken2025`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `accesstoken`
--

CREATE TABLE `accesstoken` (
  `id` int NOT NULL,
  `token` varchar(100) NOT NULL,
  `created` datetime DEFAULT NULL,
  `expire` datetime DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `reference` varchar(250) NOT NULL,
  `data` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `actionlog`
--

CREATE TABLE `actionlog` (
  `id` int NOT NULL,
  `created` datetime NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `headline` varchar(350) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `author_systemuser_id` int DEFAULT NULL,
  `author_shopuser_id` int DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `company_order_id` int DEFAULT NULL,
  `shop_user_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `shipment_id` int DEFAULT NULL,
  `other_refs` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `debugdata` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `system_log_id` int NOT NULL,
  `is_tech` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `airfryer`
--

CREATE TABLE `airfryer` (
  `id` int NOT NULL,
  `label_id` int NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_send` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `app_log`
--

CREATE TABLE `app_log` (
  `id` int NOT NULL,
  `app_username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `company_id` int DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `shopuser_id` int DEFAULT NULL,
  `order_id` int DEFAULT NULL,
  `recipient` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `email` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `gift_received` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `extradata` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `log_event` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `log_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `app_users`
--

CREATE TABLE `app_users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `shop_id` int NOT NULL,
  `note` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `token` varchar(200) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `blockmessage`
--

CREATE TABLE `blockmessage` (
  `id` int NOT NULL,
  `company_id` int NOT NULL DEFAULT '0',
  `company_order_id` int NOT NULL DEFAULT '0',
  `shipment_id` int NOT NULL DEFAULT '0',
  `block_type` varchar(150) NOT NULL DEFAULT '',
  `created_by` int NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description_old` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `release_status` int NOT NULL DEFAULT '0',
  `release_date` timestamp NULL DEFAULT NULL,
  `release_user` int DEFAULT NULL,
  `release_message` text,
  `debug_data_old` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `tech_block` int NOT NULL DEFAULT '0',
  `silent` int NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `debug_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `cardshop_expiredate`
--

CREATE TABLE `cardshop_expiredate` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `expire_date_id` int NOT NULL,
  `reservation_code` varchar(50) DEFAULT NULL,
  `packing_state` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `cardshop_freight`
--

CREATE TABLE `cardshop_freight` (
  `id` int NOT NULL,
  `company_order_id` int NOT NULL,
  `company_id` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `note` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `dot` int DEFAULT '0',
  `dot_date` datetime DEFAULT NULL,
  `dot_date_end` datetime DEFAULT NULL,
  `dot_note` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `dot_pricetype` int NOT NULL DEFAULT '0',
  `dot_price` int NOT NULL DEFAULT '0',
  `carryup` int NOT NULL DEFAULT '0',
  `carryuptype` int NOT NULL DEFAULT '0',
  `carryup_pricetype` int NOT NULL DEFAULT '0',
  `carryup_price` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `cardshop_freight_09072025`
--

CREATE TABLE `cardshop_freight_09072025` (
  `id` int NOT NULL,
  `company_order_id` int NOT NULL,
  `company_id` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `note` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `dot` int DEFAULT '0',
  `dot_date` datetime DEFAULT NULL,
  `dot_date_end` datetime DEFAULT NULL,
  `dot_note` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `dot_pricetype` int NOT NULL DEFAULT '0',
  `dot_price` int NOT NULL DEFAULT '0',
  `carryup` int NOT NULL DEFAULT '0',
  `carryuptype` int NOT NULL DEFAULT '0',
  `carryup_pricetype` int NOT NULL DEFAULT '0',
  `carryup_price` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `cardshop_settings`
--

CREATE TABLE `cardshop_settings` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `language_code` int NOT NULL,
  `shipment_print_language` int NOT NULL DEFAULT '0',
  `concept_parent` varchar(50) NOT NULL DEFAULT '',
  `concept_code` varchar(15) NOT NULL DEFAULT '',
  `concept_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `card_price` int NOT NULL DEFAULT '0',
  `card_db` double NOT NULL,
  `card_moms_multiplier` double NOT NULL DEFAULT '1.25',
  `card_values` varchar(100) DEFAULT NULL,
  `env_fee_percent` double DEFAULT '0',
  `web_salesperson` varchar(50) NOT NULL DEFAULT '',
  `privatedelivery_use` tinyint NOT NULL DEFAULT '0',
  `privatedelivery_price` int NOT NULL DEFAULT '0',
  `cardfee_use` tinyint NOT NULL DEFAULT '0',
  `cardfee_minquantity` int NOT NULL DEFAULT '0',
  `cardfee_percard` int NOT NULL DEFAULT '0',
  `cardfee_price` int NOT NULL DEFAULT '0',
  `carddelivery_use` tinyint NOT NULL DEFAULT '0',
  `carddelivery_price` int NOT NULL DEFAULT '0',
  `carryup_use` tinyint NOT NULL DEFAULT '0',
  `carryup_price` int NOT NULL DEFAULT '0',
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_price` int NOT NULL DEFAULT '0',
  `giftwrap_use` tinyint NOT NULL DEFAULT '0',
  `giftwrap_price` int NOT NULL DEFAULT '0',
  `giftwrap_notset_itemno` varchar(20) NOT NULL DEFAULT '',
  `invoiceinitial_use` tinyint NOT NULL DEFAULT '0',
  `invoiceinitial_price` int NOT NULL DEFAULT '0',
  `invoicefinal_use` tinyint NOT NULL DEFAULT '0',
  `invoicefinal_price` int NOT NULL DEFAULT '0',
  `minorderfee_use` int NOT NULL DEFAULT '0',
  `minorderfee_price` int DEFAULT NULL,
  `minorderfee_mincards` int DEFAULT NULL,
  `namelabels_use` int NOT NULL DEFAULT '0',
  `namelabels_price` int DEFAULT NULL,
  `calculate_freight` tinyint NOT NULL DEFAULT '0',
  `default_present_itemno` varchar(50) NOT NULL DEFAULT '',
  `default_present_name` varchar(50) NOT NULL DEFAULT '',
  `physical_close_days` int NOT NULL DEFAULT '5',
  `min_web_cards` int NOT NULL DEFAULT '5',
  `ordercs_syncwait` int NOT NULL DEFAULT '0',
  `orderweb_syncwait` int NOT NULL DEFAULT '0',
  `shipment_syncwait` int NOT NULL DEFAULT '0',
  `earlyorder_handler` varchar(25) NOT NULL DEFAULT '',
  `privatedelivery_handler` varchar(25) NOT NULL DEFAULT '',
  `week_47_open` datetime DEFAULT NULL,
  `week_47_close` datetime DEFAULT NULL,
  `week_47_close_websale` datetime DEFAULT NULL,
  `week_47_close_sale` datetime DEFAULT NULL,
  `week_48_open` datetime DEFAULT NULL,
  `week_48_close` datetime DEFAULT NULL,
  `week_48_close_websale` datetime DEFAULT NULL,
  `week_48_close_sale` datetime DEFAULT NULL,
  `week_49_open` datetime DEFAULT NULL,
  `week_49_close` datetime DEFAULT NULL,
  `week_49_close_websale` datetime DEFAULT NULL,
  `week_49_close_sale` datetime DEFAULT NULL,
  `week_50_open` datetime DEFAULT NULL,
  `week_50_close` datetime DEFAULT NULL,
  `week_50_close_websale` datetime DEFAULT NULL,
  `week_50_close_sale` datetime DEFAULT NULL,
  `week_51_open` datetime DEFAULT NULL,
  `week_51_close` datetime DEFAULT NULL,
  `week_51_close_websale` datetime DEFAULT NULL,
  `week_51_close_sale` datetime DEFAULT NULL,
  `week_04_open` datetime DEFAULT NULL,
  `week_04_close` datetime DEFAULT NULL,
  `week_04_close_websale` datetime DEFAULT NULL,
  `week_04_close_sale` datetime DEFAULT NULL,
  `private_open` datetime DEFAULT NULL,
  `private_close` datetime DEFAULT NULL,
  `private_close_websale` datetime DEFAULT NULL,
  `private_close_sale` datetime DEFAULT NULL,
  `private_expire_date` varchar(20) NOT NULL DEFAULT '',
  `private_expire_date_future` varchar(20) NOT NULL DEFAULT '',
  `special_private1_open` datetime DEFAULT NULL,
  `special_private1_close` datetime DEFAULT NULL,
  `special_private1_close_websale` datetime DEFAULT NULL,
  `special_private1_close_sale` datetime DEFAULT NULL,
  `special_private1_expiredate` varchar(20) DEFAULT NULL,
  `special_private2_open` datetime DEFAULT NULL,
  `special_private2_close` datetime DEFAULT NULL,
  `special_private2_close_websale` datetime DEFAULT NULL,
  `special_private2_close_sale` datetime DEFAULT NULL,
  `special_private2_expiredate` varchar(20) DEFAULT NULL,
  `replacement_company_id` int NOT NULL DEFAULT '0',
  `show_index` int NOT NULL,
  `floating_expire_months` int DEFAULT NULL,
  `send_certificates` int NOT NULL DEFAULT '0',
  `is_hidden` int NOT NULL DEFAULT '0',
  `navsync_orders` int NOT NULL DEFAULT '0',
  `navsync_shipments` int NOT NULL DEFAULT '0',
  `navsync_privatedelivery` int NOT NULL DEFAULT '0',
  `navsync_earlyorders` int NOT NULL DEFAULT '0',
  `bonus_presents` varchar(20) DEFAULT NULL,
  `earlyorder_print_language` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company`
--

CREATE TABLE `company` (
  `id` int NOT NULL,
  `pid` int NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `website` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `language_code` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0',
  `so_no` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sales_person` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_responsible` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `ean` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `footer` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `logo` int DEFAULT '0',
  `bill_to_address` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_address_2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_postal_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_company` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `ship_to_attention` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_address` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_address_2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_postal_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `shutdown` tinyint(1) NOT NULL DEFAULT '0',
  `pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `is_gift_certificate` tinyint(1) DEFAULT '0',
  `address_updated` tinyint DEFAULT '0',
  `internal_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `rapport_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `token` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `import_2017` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `onhold` int NOT NULL DEFAULT '0',
  `hasCard` int NOT NULL DEFAULT '0',
  `hasVoucher` tinyint(1) NOT NULL DEFAULT '0',
  `nav_customer_no` int NOT NULL DEFAULT '0',
  `nav_on_hold` tinyint NOT NULL DEFAULT '0',
  `nav_min_invoicedate` datetime DEFAULT NULL,
  `company_state` int NOT NULL DEFAULT '0' COMMENT '0: blocked, 1: created, 2: approved, 3: wait for sync, 4: blocked, 5: synced, 6: sync fail, 7: child (no sync)',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `prepayment` int NOT NULL DEFAULT '0',
  `excempt_invoicefee` int NOT NULL DEFAULT '0',
  `excempt_envfee` int NOT NULL DEFAULT '0',
  `payment_terms` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `allow_delivery` int NOT NULL DEFAULT '0',
  `manual_freight` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_2306`
--

CREATE TABLE `company_2306` (
  `id` int NOT NULL,
  `pid` int NOT NULL DEFAULT '0',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `website` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `language_code` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0',
  `so_no` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sales_person` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_responsible` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `ean` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `footer` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `logo` int DEFAULT '0',
  `bill_to_address` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_address_2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_postal_code` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `bill_to_email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_company` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `ship_to_attention` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_address` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_address_2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_postal_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `ship_to_country` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `contact_email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `shutdown` tinyint(1) NOT NULL DEFAULT '0',
  `pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `is_gift_certificate` tinyint(1) DEFAULT '0',
  `address_updated` tinyint DEFAULT '0',
  `internal_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `rapport_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `token` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `import_2017` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `onhold` int NOT NULL DEFAULT '0',
  `hasCard` int NOT NULL DEFAULT '0',
  `hasVoucher` tinyint(1) NOT NULL DEFAULT '0',
  `nav_customer_no` int NOT NULL DEFAULT '0',
  `nav_on_hold` tinyint NOT NULL DEFAULT '0',
  `nav_min_invoicedate` datetime DEFAULT NULL,
  `company_state` int NOT NULL DEFAULT '0' COMMENT '0: blocked, 1: created, 2: approved, 3: wait for sync, 4: blocked, 5: synced, 6: sync fail, 7: child (no sync)',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `prepayment` int NOT NULL DEFAULT '0',
  `excempt_invoicefee` int NOT NULL DEFAULT '0',
  `excempt_envfee` int NOT NULL DEFAULT '0',
  `payment_terms` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `allow_delivery` int NOT NULL DEFAULT '0',
  `manual_freight` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_notes`
--

CREATE TABLE `company_notes` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `note` text,
  `priority` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL DEFAULT '0',
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_by` int DEFAULT NULL,
  `deleted_datetime` datetime DEFAULT NULL,
  `resolved_by` int DEFAULT NULL,
  `resolved_datetime` datetime DEFAULT NULL,
  `reminder_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_order`
--

CREATE TABLE `company_order` (
  `id` int NOT NULL,
  `order_no` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `shop_id` int NOT NULL,
  `shop_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `salesperson` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `salenote` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `quantity` int NOT NULL,
  `expire_date` date DEFAULT NULL,
  `card_values` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_email` tinyint DEFAULT '0',
  `certificate_no_begin` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `certificate_no_end` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `certificate_value` int DEFAULT NULL,
  `floating_expire_date` datetime DEFAULT NULL,
  `is_printed` tinyint DEFAULT '0',
  `is_shipped` tinyint DEFAULT '0',
  `is_invoiced` tinyint DEFAULT '0',
  `ship_to_company` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `ship_to_address` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_address_2` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_postal_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_city` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `ship_to_country` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `contact_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `contact_email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `contact_phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `spdeal` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `spdealtxt` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_cancelled` tinyint DEFAULT '0' COMMENT 'er ordre annuleret',
  `cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'CVR nr',
  `ean` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `is_appendix_order` tinyint DEFAULT '0' COMMENT 'er det en till�gsordre',
  `freight_calculated` tinyint DEFAULT '0' COMMENT 'er fragt beregnet',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `giftwrap` tinyint DEFAULT '0',
  `name_label` int NOT NULL DEFAULT '0',
  `gift_spe_lev` tinyint NOT NULL DEFAULT '0',
  `ordernote` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `earlyorder` int DEFAULT '0',
  `earlyorderList` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `requisition_no` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `navsync_date` datetime DEFAULT NULL,
  `navsync_status` int NOT NULL DEFAULT '0',
  `navsync_response` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `navsync_error` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `nav_levering_blocked` int NOT NULL DEFAULT '0',
  `nocards` int NOT NULL DEFAULT '0',
  `send_welcome_mail` tinyint NOT NULL DEFAULT '1',
  `welcome_mail_is_send` tinyint(1) NOT NULL DEFAULT '0',
  `welcome_check` tinyint(1) NOT NULL DEFAULT '0',
  `free_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `shipment_token` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shipment_ready` int NOT NULL DEFAULT '0',
  `shipment_ready_only_parent` int NOT NULL DEFAULT '0',
  `nav_done` datetime DEFAULT NULL,
  `nav_synced` int NOT NULL DEFAULT '0',
  `nav_lastsync` datetime DEFAULT NULL,
  `nav_on_hold` tinyint NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0' COMMENT '0: created, 1: ready, 2: approved, 3: wait for sync, 4: synced, 5: sent, 6: failed, 7: to cancel, 8: cancelled, 9: to complete, 10: completed, 11: archive, 20: Manual (no nav)',
  `free_cards` int NOT NULL DEFAULT '0',
  `prepayment` int NOT NULL DEFAULT '0',
  `shipment_on_hold` int NOT NULL DEFAULT '1',
  `dot` int NOT NULL DEFAULT '0',
  `freight_state` int NOT NULL DEFAULT '0' COMMENT '0: not calculated, 1: add calculated, 2: added to another order, 3: fixed cost on this order, 4: fixed cost on another order, 5: fixed cost i 0, 6: order cancelled do not use for freight',
  `force_orderconf` int NOT NULL DEFAULT '0',
  `force_syncnow` int NOT NULL DEFAULT '0',
  `prepayment_date` datetime DEFAULT NULL,
  `prepayment_duedate` timestamp NULL DEFAULT NULL,
  `is_test` int NOT NULL DEFAULT '1',
  `excempt_invoicefee` int NOT NULL DEFAULT '0',
  `excempt_envfee` int NOT NULL DEFAULT '0',
  `envfee_run` int NOT NULL DEFAULT '0',
  `order_freight` int DEFAULT NULL,
  `allow_delivery` int NOT NULL DEFAULT '0',
  `envfee_override` double DEFAULT NULL,
  `nav_paid` int NOT NULL DEFAULT '0',
  `default_delivery_country` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_wait` int NOT NULL DEFAULT '0',
  `choice_exception` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `force_choice` int NOT NULL DEFAULT '0',
  `force_choice_version` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_order_history`
--

CREATE TABLE `company_order_history` (
  `id` int NOT NULL,
  `order_no` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `version_no` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `company_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `shop_id` int DEFAULT NULL,
  `shop_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `salesperson` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `salenote` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `is_email` tinyint DEFAULT '0',
  `certificate_no_begin` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `certificate_no_end` varchar(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `certificate_value` int DEFAULT NULL,
  `is_printed` tinyint DEFAULT '0',
  `is_shipped` tinyint DEFAULT '0',
  `is_invoiced` tinyint DEFAULT '0',
  `ship_to_company` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `ship_to_address` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `ship_to_address_2` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `ship_to_postal_code` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `ship_to_city` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `contact_name` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `contact_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `contact_phone` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `spdeal` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `spdealTxt` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `gift_spe_lev` tinyint NOT NULL DEFAULT '0',
  `is_cancelled` tinyint DEFAULT '0' COMMENT 'er ordre annuleret',
  `cvr` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '' COMMENT 'CVR nr',
  `ean` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_appendix_order` tinyint DEFAULT '0' COMMENT 'er det en till�gsordre',
  `freight_calculated` tinyint DEFAULT '0' COMMENT 'er fragt beregnet',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_order_item`
--

CREATE TABLE `company_order_item` (
  `id` int NOT NULL,
  `companyorder_id` int NOT NULL,
  `quantity` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` int NOT NULL,
  `isdefault` tinyint NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int NOT NULL DEFAULT '0',
  `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_shipping_cost`
--

CREATE TABLE `company_shipping_cost` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `company_order_id` int NOT NULL DEFAULT '0',
  `cost` float NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_shipping_cost_040825`
--

CREATE TABLE `company_shipping_cost_040825` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `company_order_id` int NOT NULL DEFAULT '0',
  `cost` float NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `company_shop`
--

CREATE TABLE `company_shop` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `cronlog`
--

CREATE TABLE `cronlog` (
  `id` int NOT NULL,
  `jobname` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `runtime` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `statsjson` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `debugdata` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `output` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `url` varchar(350) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `system_log_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `debug_log`
--

CREATE TABLE `debug_log` (
  `id` int NOT NULL,
  `note` text NOT NULL,
  `debug` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `dsvstatus`
--

CREATE TABLE `dsvstatus` (
  `id` int NOT NULL,
  `shipment_id` int NOT NULL,
  `order_id` int NOT NULL,
  `last_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `allocated` datetime DEFAULT NULL,
  `released` datetime DEFAULT NULL,
  `picked` datetime DEFAULT NULL,
  `shipped` datetime DEFAULT NULL,
  `completed` datetime DEFAULT NULL,
  `hold` datetime DEFAULT NULL,
  `shipped_date` datetime DEFAULT NULL,
  `dsv_created` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `dsvstatus_log`
--

CREATE TABLE `dsvstatus_log` (
  `id` int NOT NULL,
  `dsvstatus_id` int NOT NULL,
  `shipment_id` int NOT NULL,
  `order_id` int NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `line_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `dsv_input`
--

CREATE TABLE `dsv_input` (
  `id` int NOT NULL,
  `created` datetime NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `linecount` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `early_present`
--

CREATE TABLE `early_present` (
  `id` int NOT NULL,
  `item_nr` varchar(30) NOT NULL,
  `description` varchar(100) NOT NULL,
  `language` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `expire_date`
--

CREATE TABLE `expire_date` (
  `id` int NOT NULL,
  `expire_date` date NOT NULL,
  `week_no` int DEFAULT NULL,
  `display_date` varchar(10) CHARACTER SET dec8 COLLATE dec8_swedish_ci NOT NULL,
  `blocked` tinyint(1) DEFAULT '0',
  `is_delivery` tinyint(1) DEFAULT '0',
  `item_name_format` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `item_no_format` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `is_home_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `is_jgk_50` tinyint NOT NULL DEFAULT '0',
  `is_special_private` int NOT NULL DEFAULT '0',
  `is_floating` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `ftp_download`
--

CREATE TABLE `ftp_download` (
  `id` int NOT NULL,
  `ftpserver_id` int NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_content` longtext NOT NULL,
  `file_type` varchar(10) NOT NULL,
  `webhook` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `error_message` text NOT NULL,
  `is_handled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `ftp_queue`
--

CREATE TABLE `ftp_queue` (
  `id` int NOT NULL,
  `ftpserver_id` int NOT NULL,
  `path` varchar(300) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_content` mediumtext NOT NULL,
  `file_type` varchar(10) NOT NULL,
  `sent` tinyint(1) NOT NULL,
  `error` tinyint(1) NOT NULL,
  `error_message` text NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_datetime` datetime NOT NULL,
  `webhook_onerror` varchar(300) NOT NULL,
  `webhook_success` varchar(300) NOT NULL,
  `note` varchar(200) NOT NULL,
  `note_type` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `ftp_server`
--

CREATE TABLE `ftp_server` (
  `id` int NOT NULL,
  `navn` varchar(100) NOT NULL,
  `host` varchar(200) NOT NULL,
  `port` int NOT NULL,
  `user` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `path` varchar(200) NOT NULL,
  `active` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `gaveklubben`
--

CREATE TABLE `gaveklubben` (
  `id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mobil` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `subscribe_date` datetime DEFAULT NULL,
  `season` int NOT NULL,
  `noter` text CHARACTER SET latin1 COLLATE latin1_swedish_ci
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `gaveklubben_2022`
--

CREATE TABLE `gaveklubben_2022` (
  `id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `mobil` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `subscribe_date` datetime DEFAULT NULL,
  `season` int NOT NULL,
  `noter` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `unsub` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `gift_certificate`
--

CREATE TABLE `gift_certificate` (
  `id` int NOT NULL,
  `certificate_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `value` decimal(10,0) NOT NULL,
  `created_date` date DEFAULT NULL,
  `expire_date` date NOT NULL,
  `shop_id` int DEFAULT NULL,
  `company_id` int DEFAULT '0',
  `blocked` tinyint DEFAULT '0',
  `week_no` int DEFAULT NULL,
  `no_series` int DEFAULT NULL,
  `is_printed` tinyint DEFAULT '0',
  `is_emailed` tinyint DEFAULT '0',
  `is_delivery` tinyint DEFAULT '0',
  `reservation_group` int DEFAULT '0',
  `export_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `homerunner_log`
--

CREATE TABLE `homerunner_log` (
  `id` int NOT NULL,
  `created` datetime NOT NULL,
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `service` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `input` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `response` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `response_code` int DEFAULT NULL,
  `is_error` int NOT NULL DEFAULT '0',
  `truncate_days` int DEFAULT NULL,
  `remove_days` int DEFAULT NULL,
  `reference` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `shipment_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `homerunner_webhook`
--

CREATE TABLE `homerunner_webhook` (
  `id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `handled_time` datetime DEFAULT NULL,
  `handled_state` int DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `shipment_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `kontainer_queue`
--

CREATE TABLE `kontainer_queue` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `country` int NOT NULL DEFAULT '1',
  `itemno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `store` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `send_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `receive_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_sync_error` tinyint(1) NOT NULL DEFAULT '0',
  `has_pim_error` tinyint(1) NOT NULL DEFAULT '0',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `kontainer_queue_status`
--

CREATE TABLE `kontainer_queue_status` (
  `queue_run` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `kontainer_sync_job`
--

CREATE TABLE `kontainer_sync_job` (
  `id` int NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `mail` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `status` int NOT NULL DEFAULT '0' COMMENT '0:aktiv\r\n1:pause\r\n2:done\r\n3:slet',
  `mode` int NOT NULL DEFAULT '1' COMMENT '1:import\r\n2:test',
  `country` int NOT NULL DEFAULT '1',
  `has_error` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `language`
--

CREATE TABLE `language` (
  `id` int NOT NULL,
  `language_code` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `lookupdata`
--

CREATE TABLE `lookupdata` (
  `id` int NOT NULL,
  `handle` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `magento_pim_category_index`
--

CREATE TABLE `magento_pim_category_index` (
  `id` int NOT NULL,
  `magento_index` int NOT NULL,
  `pim_index` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `storeview` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `magento_pim_suppliers_index`
--

CREATE TABLE `magento_pim_suppliers_index` (
  `id` int NOT NULL,
  `magento_index` int NOT NULL,
  `pim_index` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `storeview` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `magento_stock_change`
--

CREATE TABLE `magento_stock_change` (
  `id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `old_quantity` int NOT NULL DEFAULT '0',
  `new_quantity` int NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `new_available` int DEFAULT NULL,
  `old_available` int DEFAULT NULL,
  `new_noblanket` int DEFAULT NULL,
  `old_noblanket` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `magento_stock_total`
--

CREATE TABLE `magento_stock_total` (
  `id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `available` int DEFAULT '0',
  `noblanket` int NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `magento_vip_stock`
--

CREATE TABLE `magento_vip_stock` (
  `id` int NOT NULL,
  `sku` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `valgshop_model_id` bigint DEFAULT NULL,
  `valgshop_model_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `number` int DEFAULT NULL,
  `order_id` bigint DEFAULT NULL,
  `storeview_id` int DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_event`
--

CREATE TABLE `mail_event` (
  `id` int NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `post_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `handled` timestamp NULL DEFAULT NULL,
  `ok_count` int DEFAULT NULL,
  `error_count` int DEFAULT NULL,
  `error_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_library`
--

CREATE TABLE `mail_library` (
  `id` int NOT NULL,
  `handle` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `language_id` int NOT NULL,
  `shop_id` int NOT NULL DEFAULT '0',
  `company_id` int NOT NULL DEFAULT '0',
  `mailserver_id` int NOT NULL,
  `active` int NOT NULL DEFAULT '0',
  `replacer_class` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `subject` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `template` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `from_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_queue`
--

CREATE TABLE `mail_queue` (
  `id` int NOT NULL,
  `mailserver_id` int DEFAULT '1',
  `sender_name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `sender_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `recipent_name` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `recipent_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `subject` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `body` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `sent` tinyint DEFAULT '0',
  `error` tinyint DEFAULT '0',
  `created_datetime` datetime DEFAULT NULL,
  `delivery_datetime` datetime DEFAULT NULL,
  `sent_datetime` datetime DEFAULT NULL,
  `error_message` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `order_id` int DEFAULT '0',
  `company_order_id` int DEFAULT NULL,
  `user_id` int DEFAULT '0',
  `body_base_64` tinyint DEFAULT '0',
  `mark` tinyint DEFAULT '0' COMMENT 'For marking',
  `category` int DEFAULT NULL,
  `is_smtp_error` tinyint DEFAULT NULL,
  `bounce_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `send_group` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `priority` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_queue_block`
--

CREATE TABLE `mail_queue_block` (
  `id` int NOT NULL,
  `domain` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `released` datetime DEFAULT NULL,
  `maxcount` int NOT NULL,
  `override` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_server`
--

CREATE TABLE `mail_server` (
  `id` int NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `server_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `username` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `password` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sender_email` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sender_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `language_default` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_template`
--

CREATE TABLE `mail_template` (
  `id` int NOT NULL,
  `internal_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `shop_id` int NOT NULL,
  `language_id` int NOT NULL,
  `sender_receipt` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `subject_receipt` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `template_receipt` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `template_receipt_model` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `template_receipt_exists` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sender_reminder_deadline` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `subject_reminder_deadline` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `template_reminder_deadline` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `sesnder_reminder_pickup` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `subject_reminder_pickup` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `template_reminder_pickup` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `sender_company_order` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `subject_company_order` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `template_company_order` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `sender_order_confirmation` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `subject_order_confirmation` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `template_order_confirmation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `subject_reminder_giftcertificate` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL COMMENT 'bruges ikke',
  `template_reminder_giftcertificate` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `template_reminder_giftcertificate_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `subject_overwritewarn` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `template_overwritewarn` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `mail_track`
--

CREATE TABLE `mail_track` (
  `id` int NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `company_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_id` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `media`
--

CREATE TABLE `media` (
  `id` int NOT NULL,
  `type` int DEFAULT '0',
  `caption` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `description` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `element_size` int NOT NULL,
  `width` int DEFAULT '0',
  `height` int DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `presentmedia_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `moms`
--

CREATE TABLE `moms` (
  `varenr` varchar(300) NOT NULL,
  `moms` varchar(50) NOT NULL,
  `beskrivelse` varchar(400) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `monitor_itemno`
--

CREATE TABLE `monitor_itemno` (
  `id` int NOT NULL,
  `txt` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_bomitem`
--

CREATE TABLE `navision_bomitem` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `parent_item_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `nav_key` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `assembly_bom` tinyint NOT NULL DEFAULT '0',
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `unit_of_measure_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity_per` int NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_call_log`
--

CREATE TABLE `navision_call_log` (
  `id` int NOT NULL,
  `created` datetime NOT NULL,
  `language` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `service` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `is_error` int NOT NULL DEFAULT '0',
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `description` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_choice_doc`
--

CREATE TABLE `navision_choice_doc` (
  `id` int NOT NULL,
  `company_order_id` int NOT NULL,
  `order_no` varchar(20) NOT NULL,
  `xmldoc` text NOT NULL,
  `version` int NOT NULL,
  `status` int NOT NULL,
  `cardcount` int NOT NULL,
  `error` varchar(250) NOT NULL,
  `navision_call_log_id` int NOT NULL,
  `shopuserlist` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastversion` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_company_no`
--

CREATE TABLE `navision_company_no` (
  `id` int NOT NULL,
  `language_code` int NOT NULL,
  `next_number` int NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_item`
--

CREATE TABLE `navision_item` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `description` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `nav_key` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `unit_price` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `blocked` tinyint NOT NULL DEFAULT '0',
  `base_unit_of_measure` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `assembly_bom` tinyint NOT NULL DEFAULT '0',
  `vat_prod_posting_group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `gen_prod_posting_group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `inventory_posting_group` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `item_category_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `product_group_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `gross_weight` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `net_weight` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `units_per_parcel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `unit_volume` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `reference_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `unit_cost` double DEFAULT NULL,
  `sale_price` varchar(10) DEFAULT '0',
  `price_profit_calculation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `profit_percent` double DEFAULT NULL,
  `costing_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `standard_cost` double DEFAULT NULL,
  `last_direct_cost` double DEFAULT NULL,
  `indirect_cost_percent` double DEFAULT NULL,
  `crossreference_no` varchar(100) NOT NULL DEFAULT '',
  `length` double DEFAULT NULL,
  `width` double DEFAULT NULL,
  `height` double DEFAULT NULL,
  `cubage` double DEFAULT NULL,
  `countryoforigin` varchar(25) DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `vejl_pris` varchar(25) NOT NULL,
  `is_handled` tinyint(1) DEFAULT '0',
  `is_external` int NOT NULL DEFAULT '0',
  `tariff_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_itemrename`
--

CREATE TABLE `navision_itemrename` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `old_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `new_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `nav_key` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `renamed_at` datetime DEFAULT NULL,
  `renamed_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `table_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `sync_reservation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_location`
--

CREATE TABLE `navision_location` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `isprimary` int NOT NULL,
  `blocked` int NOT NULL,
  `nav_key` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `username` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `password` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `show_in_shop` tinyint(1) DEFAULT '0',
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_order_doc`
--

CREATE TABLE `navision_order_doc` (
  `id` int NOT NULL,
  `company_order_id` int NOT NULL,
  `order_no` varchar(20) NOT NULL DEFAULT '',
  `xmldoclatin` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `status` int NOT NULL DEFAULT '0',
  `revision` int NOT NULL DEFAULT '0',
  `error` varchar(250) NOT NULL DEFAULT '',
  `retry` int NOT NULL DEFAULT '0',
  `navision_call_log_id` int NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `xmldoc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `parsed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_order_line`
--

CREATE TABLE `navision_order_line` (
  `id` int NOT NULL,
  `company_order_id` int NOT NULL,
  `order_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `customer_no` int NOT NULL,
  `is_cancelled` int NOT NULL,
  `final_version` int NOT NULL,
  `revision` int NOT NULL,
  `navision_order_doc_id` int NOT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` int NOT NULL,
  `created` datetime NOT NULL,
  `synced` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_reservation_done`
--

CREATE TABLE `navision_reservation_done` (
  `id` int NOT NULL,
  `created_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shop_id` int NOT NULL,
  `sono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  `isbom` int NOT NULL DEFAULT '0',
  `bomno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `bomquantity` int NOT NULL DEFAULT '0',
  `done` int NOT NULL DEFAULT '0',
  `revert` int NOT NULL DEFAULT '0',
  `revert_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_reservation_done_item`
--

CREATE TABLE `navision_reservation_done_item` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `present_reservation_id` int NOT NULL,
  `reservation_done_id` int NOT NULL,
  `quantity` int NOT NULL,
  `oldresdone` int NOT NULL,
  `newresdone` int NOT NULL,
  `olddonebalance` int NOT NULL,
  `newdonebalance` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_revert` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_reservation_log`
--

CREATE TABLE `navision_reservation_log` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shop_id` int NOT NULL,
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `navision_call_log_id` int NOT NULL,
  `delta` int NOT NULL,
  `balance` int NOT NULL,
  `notes` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `present_reservations_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `rename_itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_salesperson`
--

CREATE TABLE `navision_salesperson` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `nav_key` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_salesprice`
--

CREATE TABLE `navision_salesprice` (
  `id` int NOT NULL,
  `language_id` int NOT NULL DEFAULT '0',
  `item_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `nav_key` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `sales_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `sales_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `minimum_quantity` int NOT NULL DEFAULT '0',
  `starting_date` date DEFAULT NULL,
  `ending_date` date DEFAULT NULL,
  `unit_of_measure` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `unit_price` double NOT NULL,
  `price_includes_vat` int NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_stock_total`
--

CREATE TABLE `navision_stock_total` (
  `id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `language_id` int NOT NULL DEFAULT '0',
  `quantity` int NOT NULL DEFAULT '0',
  `available` int DEFAULT '0',
  `noblanket` int NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_vs_state`
--

CREATE TABLE `navision_vs_state` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `state` int NOT NULL DEFAULT '0' COMMENT '0: ikke syncet, 1: synkroniseret (ordre), 2: ordre fejl, 3: skal annulleres, 4: ordre annulleret, 5: under afslutning, 6: afsluttet',
  `last_run_date` datetime DEFAULT NULL,
  `last_run_check` datetime DEFAULT NULL,
  `last_run_error` int NOT NULL DEFAULT '0',
  `last_run_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `on_hold` int NOT NULL DEFAULT '0',
  `needs_sync` int NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished` datetime DEFAULT NULL,
  `sync_language_id` int DEFAULT NULL,
  `sync_debitor_no` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `navision_vs_version`
--

CREATE TABLE `navision_vs_version` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `status` int NOT NULL,
  `version` int NOT NULL,
  `xmldoc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `navision_call_log_id` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `number_series`
--

CREATE TABLE `number_series` (
  `id` int NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prefix` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `decimals` int NOT NULL,
  `current_no` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order`
--

CREATE TABLE `order` (
  `id` int NOT NULL,
  `order_no` int NOT NULL DEFAULT '0',
  `order_timestamp` datetime NOT NULL,
  `shop_id` int NOT NULL,
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `present_copy_of` int DEFAULT NULL,
  `present_shop_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `present_model_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `present_model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_value` int DEFAULT '0',
  `gift_certificate_week_no` int DEFAULT '0',
  `gift_certificate_start_date` date DEFAULT NULL,
  `gift_certificate_end_date` date DEFAULT NULL,
  `registered` tinyint DEFAULT '0',
  `registered_date` date DEFAULT NULL,
  `registered_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_demo` tinyint DEFAULT '0',
  `language_id` int DEFAULT '0',
  `is_delivery` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_0307`
--

CREATE TABLE `order_0307` (
  `id` int NOT NULL,
  `order_no` int NOT NULL DEFAULT '0',
  `order_timestamp` datetime NOT NULL,
  `shop_id` int NOT NULL,
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `present_copy_of` int DEFAULT NULL,
  `present_shop_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `present_model_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `present_model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_value` int DEFAULT '0',
  `gift_certificate_week_no` int DEFAULT '0',
  `gift_certificate_start_date` date DEFAULT NULL,
  `gift_certificate_end_date` date DEFAULT NULL,
  `registered` tinyint DEFAULT '0',
  `registered_date` date DEFAULT NULL,
  `registered_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_demo` tinyint DEFAULT '0',
  `language_id` int DEFAULT '0',
  `is_delivery` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_attribute`
--

CREATE TABLE `order_attribute` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `company_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `attribute_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `attribute_value` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `attribute_index` int DEFAULT '0',
  `is_username` tinyint DEFAULT NULL,
  `is_password` tinyint DEFAULT NULL,
  `is_name` tinyint DEFAULT NULL,
  `is_email` tinyint DEFAULT NULL,
  `list_selection` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_history`
--

CREATE TABLE `order_history` (
  `id` int NOT NULL,
  `order_no` int NOT NULL,
  `order_timestamp` datetime NOT NULL,
  `shop_id` int NOT NULL,
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `present_copy_of` int DEFAULT NULL,
  `present_shop_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `present_model_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_value` int DEFAULT '0',
  `gift_certificate_week_no` int DEFAULT '0',
  `gift_certificate_start_date` date DEFAULT NULL,
  `gift_certificate_end_date` date DEFAULT NULL,
  `is_demo` tinyint DEFAULT '0',
  `language_id` int DEFAULT '0',
  `present_model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `is_delivery` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

--
-- Triggers/udløsere `order_history`
--
DELIMITER $$
CREATE TRIGGER `order_no_history` BEFORE INSERT ON `order_history` FOR EACH ROW SET NEW.order_no = (SELECT MAX(order_no) + 1 FROM `order_history` WHERE is_demo = NEW.is_demo)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_history_attribute`
--

CREATE TABLE `order_history_attribute` (
  `id` int NOT NULL,
  `orderhistory_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `company_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `attribute_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `attribute_value` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `attribute_index` int NOT NULL DEFAULT '0',
  `is_username` tinyint DEFAULT NULL,
  `is_password` tinyint DEFAULT NULL,
  `is_name` tinyint DEFAULT NULL,
  `is_email` tinyint DEFAULT NULL,
  `list_selection` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_present_complaint`
--

CREATE TABLE `order_present_complaint` (
  `id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `company_id` int NOT NULL,
  `complaint_txt` text CHARACTER SET latin1 COLLATE latin1_danish_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_update` timestamp NULL DEFAULT NULL,
  `active` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_present_entry`
--

CREATE TABLE `order_present_entry` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `order_timestamp` datetime NOT NULL,
  `shop_id` int NOT NULL,
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `present_copy_of` int DEFAULT NULL,
  `present_shop_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `present_model_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_value` int DEFAULT '0',
  `gift_certificate_week_no` int DEFAULT '0',
  `gift_certificate_start_date` date DEFAULT NULL,
  `gift_certificate_end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `order_test`
--

CREATE TABLE `order_test` (
  `id` int NOT NULL,
  `order_no` int NOT NULL DEFAULT '0',
  `order_timestamp` datetime NOT NULL,
  `shop_id` int NOT NULL,
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_cvr` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `company_pick_group` varchar(15) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_email` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `user_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `present_vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `present_copy_of` int DEFAULT NULL,
  `present_shop_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `present_model_name` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `present_model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `gift_certificate_value` int DEFAULT '0',
  `gift_certificate_week_no` int DEFAULT '0',
  `gift_certificate_start_date` date DEFAULT NULL,
  `gift_certificate_end_date` date DEFAULT NULL,
  `registered` tinyint DEFAULT '0',
  `registered_date` date DEFAULT NULL,
  `registered_note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_demo` tinyint DEFAULT '0',
  `language_id` int DEFAULT '0',
  `is_delivery` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `paper_log`
--

CREATE TABLE `paper_log` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `user_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `operation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `paper_order`
--

CREATE TABLE `paper_order` (
  `id` int NOT NULL,
  `user_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int NOT NULL,
  `alias` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `shop_id` int NOT NULL,
  `is_sync` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `paper_user_attribute`
--

CREATE TABLE `paper_user_attribute` (
  `id` int NOT NULL,
  `user_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `attribute_id` int NOT NULL,
  `attribute_value` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shop_id` int NOT NULL,
  `is_sync` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `permission_user`
--

CREATE TABLE `permission_user` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pim_logo`
--

CREATE TABLE `pim_logo` (
  `id` int NOT NULL,
  `logo_id` int DEFAULT NULL,
  `file_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pim_logo_log`
--

CREATE TABLE `pim_logo_log` (
  `id` int NOT NULL,
  `logo_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `update_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pim_nav_itemnr_sync`
--

CREATE TABLE `pim_nav_itemnr_sync` (
  `id` int NOT NULL,
  `nav_itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `kontainer_itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pim_sync`
--

CREATE TABLE `pim_sync` (
  `id` int NOT NULL,
  `sync_active` int NOT NULL,
  `sync_update` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pim_sync_queue`
--

CREATE TABLE `pim_sync_queue` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `nav_name_da` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `type` int NOT NULL DEFAULT '1',
  `item_nr` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `sync_dato` datetime NOT NULL,
  `error` tinyint(1) DEFAULT '0',
  `error_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `sync_start` datetime DEFAULT NULL,
  `sync_end` datetime DEFAULT NULL,
  `is_handled` tinyint(1) DEFAULT '0',
  `system` int DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `postnord_orderreport`
--

CREATE TABLE `postnord_orderreport` (
  `id` int NOT NULL,
  `created_date` datetime NOT NULL,
  `shipment_date` datetime NOT NULL,
  `shipment_id` int NOT NULL,
  `shop_user_id` int NOT NULL,
  `order_id` int NOT NULL,
  `username` varchar(20) NOT NULL,
  `shipment_no` varchar(80) NOT NULL,
  `package_no` varchar(80) NOT NULL,
  `itemno` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `delivery_method` varchar(50) NOT NULL,
  `ftp_download_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `postnord_varenr`
--

CREATE TABLE `postnord_varenr` (
  `id` int NOT NULL,
  `varenr` varchar(40) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastsend_date` datetime DEFAULT NULL,
  `lastsend_file_id` int NOT NULL DEFAULT '0',
  `lastsend_doc` text,
  `lastreceive_date` datetime DEFAULT NULL,
  `lastreceive_doc` text,
  `lastreceive_file_id` int NOT NULL DEFAULT '0',
  `language_id` int NOT NULL DEFAULT '0',
  `state` int NOT NULL DEFAULT '0',
  `error` text,
  `current_stock` int NOT NULL DEFAULT '0',
  `current_reserved` int NOT NULL DEFAULT '0',
  `sent_since_update` int NOT NULL DEFAULT '0',
  `navalias` varchar(30) NOT NULL,
  `postnordalias` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `postnord_varenrlog`
--

CREATE TABLE `postnord_varenrlog` (
  `id` int NOT NULL,
  `itemno` varchar(50) NOT NULL,
  `postnord_varenr_id` int NOT NULL,
  `ftp_download_id` int NOT NULL,
  `process_date` datetime NOT NULL,
  `type` varchar(50) NOT NULL,
  `stockadjustment` int DEFAULT NULL,
  `stockcount` int NOT NULL,
  `reserved` int NOT NULL,
  `description` text NOT NULL,
  `created_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present`
--

CREATE TABLE `present` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pchild` int DEFAULT NULL,
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `show_to_saleperson_se` int DEFAULT '0',
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop',
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `shop_present_category_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_group`
--

CREATE TABLE `presentation_group` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `type` int NOT NULL DEFAULT '0',
  `active` int NOT NULL DEFAULT '1',
  `prices_da` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prices_sv` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prices_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_index` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `localisation` int NOT NULL DEFAULT '1',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_group_0906`
--

CREATE TABLE `presentation_group_0906` (
  `id` int NOT NULL,
  `group_id` int NOT NULL,
  `type` int NOT NULL DEFAULT '0',
  `active` int NOT NULL DEFAULT '1',
  `prices_da` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prices_sv` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prices_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_index` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `localisation` int NOT NULL DEFAULT '1',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale`
--

CREATE TABLE `presentation_sale` (
  `id` varchar(40) NOT NULL,
  `author_id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `config` text NOT NULL,
  `show_price` tinyint(1) NOT NULL DEFAULT '1',
  `language` int NOT NULL,
  `has_shop` tinyint(1) DEFAULT '0',
  `is_deleted` smallint DEFAULT '0',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale_log`
--

CREATE TABLE `presentation_sale_log` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `controller` varchar(40) NOT NULL,
  `action` varchar(40) NOT NULL,
  `data` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale_pdf`
--

CREATE TABLE `presentation_sale_pdf` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `author` int NOT NULL,
  `presentation_id` varchar(50) NOT NULL,
  `setting` varchar(200) NOT NULL,
  `create_data` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sort` int NOT NULL,
  `is_deleted` tinyint DEFAULT '0',
  `dum` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale_present`
--

CREATE TABLE `presentation_sale_present` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `author` int NOT NULL,
  `language` int NOT NULL,
  `create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale_profile`
--

CREATE TABLE `presentation_sale_profile` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `mail` varchar(50) NOT NULL,
  `img` varchar(50) NOT NULL,
  `lang` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `presentation_sale_profile_2105`
--

CREATE TABLE `presentation_sale_profile_2105` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(50) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `mail` varchar(50) NOT NULL,
  `img` varchar(50) NOT NULL,
  `lang` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_0307_2`
--

CREATE TABLE `present_0307_2` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pchild` int DEFAULT NULL,
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `show_to_saleperson_se` int DEFAULT '0',
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop',
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `shop_present_category_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_0906`
--

CREATE TABLE `present_0906` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pchild` int DEFAULT NULL,
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `show_to_saleperson_se` int DEFAULT '0',
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop',
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `shop_present_category_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_2406`
--

CREATE TABLE `present_2406` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pchild` int DEFAULT NULL,
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `show_to_saleperson_se` int DEFAULT '0',
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop',
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `shop_present_category_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_20250705`
--

CREATE TABLE `present_20250705` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `pchild` int DEFAULT NULL,
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `show_to_saleperson_se` int DEFAULT '0',
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop',
  `in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `shop_present_category_id` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_description`
--

CREATE TABLE `present_description` (
  `id` int NOT NULL,
  `present_id` int NOT NULL DEFAULT '0',
  `language_id` int NOT NULL DEFAULT '0',
  `caption` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `caption_presentation` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `caption_paper` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `short_description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `long_description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_log`
--

CREATE TABLE `present_log` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `log` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_media`
--

CREATE TABLE `present_media` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `index` int UNSIGNED DEFAULT '0',
  `sync_id` int DEFAULT '0',
  `show_small` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model`
--

CREATE TABLE `present_model` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_0307`
--

CREATE TABLE `present_model_0307` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_0307_2`
--

CREATE TABLE `present_model_0307_2` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_0707`
--

CREATE TABLE `present_model_0707` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_2406`
--

CREATE TABLE `present_model_2406` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_20250705`
--

CREATE TABLE `present_model_20250705` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_allan`
--

CREATE TABLE `present_model_allan` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_kim`
--

CREATE TABLE `present_model_kim` (
  `id` int NOT NULL,
  `model_id` int DEFAULT NULL,
  `pim_id` int DEFAULT NULL,
  `original_model_id` int DEFAULT '0',
  `present_id` int DEFAULT NULL,
  `language_id` int DEFAULT NULL,
  `model_present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '' COMMENT 'varenummer',
  `model_name` varchar(2048) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `model_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `media_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `active` tinyint DEFAULT '0',
  `dummy` tinyblob COMMENT 'bruges internt',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `aliasletter` varchar(2) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `fullalias` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `moms` int NOT NULL DEFAULT '25',
  `msg1` int NOT NULL DEFAULT '0',
  `custom_msg1` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `sampak_items` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `strength` int DEFAULT '0',
  `price` int DEFAULT '0',
  `sale_price` int DEFAULT '0',
  `autopilot` tinyint(1) DEFAULT '0',
  `autopilot_lock` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_options`
--

CREATE TABLE `present_model_options` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `expire_data` varchar(20) NOT NULL,
  `visibility` tinyint(1) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_model_sampak`
--

CREATE TABLE `present_model_sampak` (
  `id` int NOT NULL,
  `model_id` int NOT NULL,
  `item_list` varchar(200) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_options`
--

CREATE TABLE `present_options` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `option_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `option_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_reservation`
--

CREATE TABLE `present_reservation` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int DEFAULT '0',
  `quantity` int DEFAULT '0',
  `old_quantity` int DEFAULT '0',
  `warning_level` decimal(15,2) DEFAULT '0.00',
  `current_level` decimal(15,2) DEFAULT '0.00',
  `replacement_present_id` int DEFAULT NULL,
  `replacement_present_name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `do_close` tinyint(1) NOT NULL DEFAULT '0',
  `is_close` tinyint(1) NOT NULL DEFAULT '0',
  `warning_issued` tinyint DEFAULT '0',
  `quantity_done` int NOT NULL DEFAULT '0',
  `skip_navision` int NOT NULL DEFAULT '0',
  `ship_monitoring` int DEFAULT '0',
  `autotopilot` tinyint(1) NOT NULL DEFAULT '0',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sync_time` timestamp NULL DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL,
  `sync_quantity` int DEFAULT NULL,
  `sync_note` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `adapt_0` int DEFAULT NULL,
  `adapt_1` int DEFAULT NULL,
  `adapt_2` int DEFAULT NULL,
  `adapt_3` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_reservation_2807`
--

CREATE TABLE `present_reservation_2807` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int DEFAULT '0',
  `quantity` int DEFAULT '0',
  `old_quantity` int DEFAULT '0',
  `warning_level` decimal(15,2) DEFAULT '0.00',
  `current_level` decimal(15,2) DEFAULT '0.00',
  `replacement_present_id` int DEFAULT NULL,
  `replacement_present_name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `do_close` tinyint(1) NOT NULL DEFAULT '0',
  `is_close` tinyint(1) NOT NULL DEFAULT '0',
  `warning_issued` tinyint DEFAULT '0',
  `quantity_done` int NOT NULL DEFAULT '0',
  `skip_navision` int NOT NULL DEFAULT '0',
  `ship_monitoring` int DEFAULT '0',
  `autotopilot` tinyint(1) NOT NULL DEFAULT '0',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sync_time` timestamp NULL DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL,
  `sync_quantity` int DEFAULT NULL,
  `sync_note` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `adapt_0` int DEFAULT NULL,
  `adapt_1` int DEFAULT NULL,
  `adapt_2` int DEFAULT NULL,
  `adapt_3` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_reservation_close_log`
--

CREATE TABLE `present_reservation_close_log` (
  `id` int NOT NULL,
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shop_id` int NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_reservation_forecast`
--

CREATE TABLE `present_reservation_forecast` (
  `id` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `model_id` int DEFAULT NULL,
  `itemno` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `forecast` int DEFAULT NULL,
  `procent` int DEFAULT NULL,
  `total_orders` int DEFAULT NULL,
  `stock_available` int DEFAULT NULL,
  `is_external` int DEFAULT NULL,
  `group_id` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_reservation_log`
--

CREATE TABLE `present_reservation_log` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int DEFAULT '0',
  `quantity` int DEFAULT '0',
  `old_quantity` int DEFAULT '0',
  `warning_level` decimal(15,2) DEFAULT '0.00',
  `current_level` decimal(15,2) DEFAULT '0.00',
  `replacement_present_id` int DEFAULT NULL,
  `replacement_present_name` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `do_close` tinyint(1) NOT NULL DEFAULT '0',
  `is_close` tinyint(1) NOT NULL DEFAULT '0',
  `warning_issued` tinyint DEFAULT '0',
  `quantity_done` int NOT NULL DEFAULT '0',
  `skip_navision` int NOT NULL DEFAULT '0',
  `ship_monitoring` int DEFAULT '0',
  `autotopilot` tinyint(1) NOT NULL DEFAULT '0',
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sync_time` timestamp NULL DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL,
  `sync_quantity` int DEFAULT NULL,
  `sync_note` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `adapt_0` int DEFAULT NULL,
  `adapt_1` int DEFAULT NULL,
  `adapt_2` int DEFAULT NULL,
  `adapt_3` int DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `present_test`
--

CREATE TABLE `present_test` (
  `id` int NOT NULL,
  `pim_id` int DEFAULT NULL,
  `pim_sync_time` timestamp NULL DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `nav_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `nav_name_en` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `internal_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `present_no` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `copy_of` int DEFAULT '0',
  `shop_id` int DEFAULT '0',
  `logo` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_size` int DEFAULT NULL,
  `price` decimal(15,2) DEFAULT '0.00',
  `price_no` decimal(15,2) DEFAULT '0.00',
  `price_se` int DEFAULT NULL,
  `price_group` decimal(15,2) DEFAULT '0.00',
  `price_group_no` decimal(15,2) DEFAULT '0.00',
  `price_group_se` int DEFAULT NULL,
  `indicative_price` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `indicative_price_no` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `is_grouped` tinyint(1) DEFAULT '0',
  `present_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `variant_list` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `vendor` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `limit` int DEFAULT '0',
  `present_substitute` int DEFAULT '0',
  `alias` int NOT NULL DEFAULT '0',
  `moms` int NOT NULL DEFAULT '25',
  `pt_layout` int DEFAULT NULL,
  `pt_img` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_img_small_sync` int DEFAULT NULL,
  `pt_img_small_show` varchar(6) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `kunhos` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `mere_at_give` int DEFAULT NULL,
  `pt_options` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `pt_price` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_no` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_price_se` varchar(300) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `pt_show_language` int DEFAULT '0',
  `state` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'a',
  `hide_for_demo_user` int DEFAULT '0',
  `lock_for_sync` tinyint(1) DEFAULT '0',
  `show_to_saleperson` int DEFAULT NULL,
  `show_to_saleperson_no` int DEFAULT NULL,
  `prisents_nav_price` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `prisents_nav_price_no` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `oko_present` tinyint DEFAULT NULL,
  `omtanke` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT 'false',
  `show_if_home_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `strength` int DEFAULT '0',
  `external` int NOT NULL DEFAULT '0',
  `sis_headline` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `sis_badge` int DEFAULT '0',
  `gift_choice_flag` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'When a customer in a presentation has decided that this gift should be included in the shop'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pt_image`
--

CREATE TABLE `pt_image` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `data` mediumtext NOT NULL,
  `sort` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `pwc`
--

CREATE TABLE `pwc` (
  `id` int NOT NULL,
  `office` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `days_per_week` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `distance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `transport` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `public_transport_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `license_plate` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `car_size` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `other_transport_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `post_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `receipt_additions`
--

CREATE TABLE `receipt_additions` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `language` int NOT NULL,
  `top_text` text COLLATE utf8mb4_danish_ci,
  `standard_text` text COLLATE utf8mb4_danish_ci,
  `delivery_date` varchar(255) COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `bottom_text` text COLLATE utf8mb4_danish_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `receipt_custom_part`
--

CREATE TABLE `receipt_custom_part` (
  `id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `da` text NOT NULL,
  `en` text NOT NULL,
  `sv` text NOT NULL,
  `no` text NOT NULL,
  `de` text NOT NULL,
  `pos` int NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `receipt_custom_text`
--

CREATE TABLE `receipt_custom_text` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `company_id` int NOT NULL,
  `da` text NOT NULL,
  `eng` text NOT NULL,
  `no` text NOT NULL,
  `sv` text NOT NULL,
  `de` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `reservation_group`
--

CREATE TABLE `reservation_group` (
  `id` int NOT NULL,
  `name` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `rm_data`
--

CREATE TABLE `rm_data` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `item_nr` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `model_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `model_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `forecast` int DEFAULT NULL,
  `reserved` int DEFAULT NULL,
  `selected` int DEFAULT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `is_exceeded` tinyint(1) NOT NULL DEFAULT '0',
  `is_exceeded_forecast` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `rm_job`
--

CREATE TABLE `rm_job` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `rm_shop_data`
--

CREATE TABLE `rm_shop_data` (
  `id` int NOT NULL,
  `job_id` int NOT NULL,
  `item_nr` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shop_id` int NOT NULL,
  `forecast` float DEFAULT '0',
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `salesperson_shop`
--

CREATE TABLE `salesperson_shop` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shipment`
--

CREATE TABLE `shipment` (
  `id` int NOT NULL,
  `companyorder_id` int NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shipment_type` varchar(20) NOT NULL,
  `handler` varchar(25) NOT NULL DEFAULT 'navision',
  `quantity` int NOT NULL,
  `itemno` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` varchar(400) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `itemno2` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `quantity2` int NOT NULL DEFAULT '0',
  `description2` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `itemno3` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `quantity3` int NOT NULL DEFAULT '0',
  `description3` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `itemno4` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `quantity4` int NOT NULL DEFAULT '0',
  `description4` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `itemno5` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `quantity5` int NOT NULL DEFAULT '0',
  `description5` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `isshipment` int NOT NULL,
  `from_certificate_no` int DEFAULT NULL,
  `to_certificate_no` int DEFAULT NULL,
  `shipto_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shipto_address` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `shipto_address2` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `shipto_city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `shipto_postcode` varchar(50) NOT NULL DEFAULT '',
  `shipto_country` varchar(50) NOT NULL DEFAULT '',
  `shipto_contact` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `shipto_email` varchar(150) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `shipto_phone` varchar(50) NOT NULL DEFAULT '',
  `shipment_note` text,
  `gls_shipment` int NOT NULL DEFAULT '0',
  `handle_country` int NOT NULL DEFAULT '0',
  `shipment_state` int NOT NULL DEFAULT '0' COMMENT '0: waiting, 1: ready, 2: synced, 3: error, 4: blocked, 5: processed externally, 6: synced home, 7: ship to address, 9: country error',
  `shipment_sync_date` datetime DEFAULT NULL,
  `deleted_date` datetime DEFAULT NULL,
  `force_syncnow` int NOT NULL DEFAULT '0',
  `series_master` int NOT NULL DEFAULT '0',
  `series_uuid` varchar(100) DEFAULT NULL,
  `shipto_state` int NOT NULL DEFAULT '0',
  `sync_delay` datetime DEFAULT NULL,
  `sync_note` text,
  `reservation_released` datetime DEFAULT NULL,
  `shipped_date` datetime DEFAULT NULL,
  `consignor_created` datetime DEFAULT NULL,
  `consignor_labelno` varchar(50) DEFAULT NULL,
  `nav_order_no` varchar(50) DEFAULT NULL,
  `delivered_date` datetime DEFAULT NULL,
  `ttlink` varchar(250) DEFAULT NULL,
  `support_note` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop`
--

CREATE TABLE `shop` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `alias` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `in_shopboard` tinyint(1) NOT NULL DEFAULT '0',
  `in_shopboard_date` date DEFAULT NULL,
  `is_gift_certificate` tinyint(1) DEFAULT '0',
  `is_company` tinyint(1) DEFAULT '0',
  `is_demo` tinyint(1) DEFAULT '0',
  `demo_username` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_password` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_user_id` int DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `expire_warning_date` date DEFAULT NULL,
  `image_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_enabled` tinyint(1) DEFAULT '0',
  `zoom_enabled` tinyint(1) DEFAULT '0',
  `language_enabled` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `language_settings` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `email_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `active` tinyint DEFAULT '1',
  `deleted` tinyint DEFAULT '0',
  `no_series` int DEFAULT '0' COMMENT 'depreached',
  `reservation_group` int DEFAULT '0',
  `open_for_registration` tinyint DEFAULT '0',
  `registration_option` int NOT NULL DEFAULT '0',
  `blocked` tinyint DEFAULT NULL,
  `blocked_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `mailserver_id` int DEFAULT '1',
  `language_id` int DEFAULT '1' COMMENT 'default language id',
  `shipment_date` date DEFAULT NULL,
  `location_attribute_id` int DEFAULT '0',
  `location_type` int NOT NULL DEFAULT '0',
  `edit_allowed` tinyint DEFAULT '0',
  `show_price` tinyint(1) NOT NULL DEFAULT '0',
  `subscribe_gaveklubben` int DEFAULT '0',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `is_norwegian` tinyint(1) DEFAULT '0',
  `receipt_link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `receipt_recipent` varbinary(100) DEFAULT '',
  `close_date` datetime DEFAULT NULL,
  `soft_close` int DEFAULT '0',
  `card_value` int DEFAULT NULL,
  `report_attributes` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `rapport_email` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `login_design` int NOT NULL DEFAULT '10',
  `show_tree_front` tinyint(1) NOT NULL DEFAULT '0',
  `show_qr` tinyint(1) NOT NULL DEFAULT '1',
  `kundepanel_email_regel` int NOT NULL DEFAULT '2',
  `qr_menu_settings` int NOT NULL DEFAULT '0',
  `pt_pdf` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `ptUpdate` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_saleperson` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_shopName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_frontpage` int NOT NULL DEFAULT '1',
  `pt_mere_at_give` int NOT NULL DEFAULT '1',
  `pt_tree` tinyint(1) NOT NULL DEFAULT '1',
  `pt_bag_page` tinyint NOT NULL DEFAULT '1',
  `pt_voucher_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_saleperson_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_layout_style` int NOT NULL DEFAULT '0',
  `pt_language` int NOT NULL DEFAULT '0',
  `pt_brands_united` int DEFAULT '0',
  `pt_show_frontpage_design` tinyint NOT NULL DEFAULT '0',
  `has_login_splash` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `shop_mode` int NOT NULL DEFAULT '2',
  `localisation` int NOT NULL DEFAULT '1',
  `dbcalc_budget` int DEFAULT NULL,
  `dbcalc_standard` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `saleperson` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `manager_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `gdpr` tinyint(1) NOT NULL DEFAULT '1',
  `reservation_state` int DEFAULT '0' COMMENT '0: reservations not active, 1: reservations active (send to nav)',
  `reservation_language` int DEFAULT NULL,
  `reservation_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `reservation_foreign_language` int DEFAULT '0',
  `reservation_foreign_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `vip_shop` tinyint(1) DEFAULT '0',
  `login_check_strict` int NOT NULL DEFAULT '0',
  `final_finished` tinyint(1) NOT NULL DEFAULT '0',
  `partial_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `sold` int DEFAULT '0',
  `welcome_mail` datetime DEFAULT NULL,
  `status_id` bigint UNSIGNED DEFAULT NULL,
  `paper_settings` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `updated_datetime` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `singleselect` int NOT NULL DEFAULT '0',
  `login_background` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_0408`
--

CREATE TABLE `shop_0408` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `alias` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `in_shopboard` tinyint(1) NOT NULL DEFAULT '0',
  `is_gift_certificate` tinyint(1) DEFAULT '0',
  `is_company` tinyint(1) DEFAULT '0',
  `is_demo` tinyint(1) DEFAULT '0',
  `demo_username` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_password` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_user_id` int DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `expire_warning_date` date DEFAULT NULL,
  `image_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_enabled` tinyint(1) DEFAULT '0',
  `zoom_enabled` tinyint(1) DEFAULT '0',
  `language_enabled` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `language_settings` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `email_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `active` tinyint DEFAULT '1',
  `deleted` tinyint DEFAULT '0',
  `no_series` int DEFAULT '0' COMMENT 'depreached',
  `reservation_group` int DEFAULT '0',
  `open_for_registration` tinyint DEFAULT '0',
  `registration_option` int NOT NULL DEFAULT '0',
  `blocked` tinyint DEFAULT NULL,
  `blocked_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `mailserver_id` int DEFAULT '1',
  `language_id` int DEFAULT '1' COMMENT 'default language id',
  `shipment_date` date DEFAULT NULL,
  `location_attribute_id` int DEFAULT '0',
  `location_type` int NOT NULL DEFAULT '0',
  `edit_allowed` tinyint DEFAULT '0',
  `show_price` tinyint(1) NOT NULL DEFAULT '0',
  `subscribe_gaveklubben` int DEFAULT '0',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `is_norwegian` tinyint(1) DEFAULT '0',
  `receipt_link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `receipt_recipent` varbinary(100) DEFAULT '',
  `close_date` datetime DEFAULT NULL,
  `soft_close` int DEFAULT '0',
  `card_value` int DEFAULT NULL,
  `report_attributes` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `rapport_email` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `login_design` int NOT NULL DEFAULT '10',
  `show_tree_front` tinyint(1) NOT NULL DEFAULT '0',
  `show_qr` tinyint(1) NOT NULL DEFAULT '1',
  `kundepanel_email_regel` int NOT NULL DEFAULT '2',
  `qr_menu_settings` int NOT NULL DEFAULT '0',
  `pt_pdf` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `ptUpdate` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_saleperson` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_shopName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_frontpage` int NOT NULL DEFAULT '1',
  `pt_mere_at_give` int NOT NULL DEFAULT '1',
  `pt_tree` tinyint(1) NOT NULL DEFAULT '1',
  `pt_bag_page` tinyint NOT NULL DEFAULT '1',
  `pt_voucher_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_saleperson_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_layout_style` int NOT NULL DEFAULT '0',
  `pt_language` int NOT NULL DEFAULT '0',
  `pt_brands_united` int DEFAULT '0',
  `pt_show_frontpage_design` tinyint NOT NULL DEFAULT '0',
  `has_login_splash` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `shop_mode` int NOT NULL DEFAULT '2',
  `localisation` int NOT NULL DEFAULT '1',
  `dbcalc_budget` int DEFAULT NULL,
  `dbcalc_standard` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `saleperson` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `manager_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `gdpr` tinyint(1) NOT NULL DEFAULT '1',
  `reservation_state` int DEFAULT '0' COMMENT '0: reservations not active, 1: reservations active (send to nav)',
  `reservation_language` int DEFAULT NULL,
  `reservation_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `reservation_foreign_language` int DEFAULT '0',
  `reservation_foreign_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `vip_shop` tinyint(1) DEFAULT '0',
  `login_check_strict` int NOT NULL DEFAULT '0',
  `final_finished` tinyint(1) NOT NULL DEFAULT '0',
  `partial_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `sold` int DEFAULT '0',
  `welcome_mail` datetime DEFAULT NULL,
  `status_id` bigint UNSIGNED DEFAULT NULL,
  `paper_settings` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `updated_datetime` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `singleselect` int NOT NULL DEFAULT '0',
  `login_background` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_1706`
--

CREATE TABLE `shop_1706` (
  `id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `alias` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `in_shopboard` tinyint(1) NOT NULL DEFAULT '0',
  `in_shopboard_date` date DEFAULT NULL,
  `is_gift_certificate` tinyint(1) DEFAULT '0',
  `is_company` tinyint(1) DEFAULT '0',
  `is_demo` tinyint(1) DEFAULT '0',
  `demo_username` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_password` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `demo_user_id` int DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `expire_warning_date` date DEFAULT NULL,
  `image_path` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `logo_enabled` tinyint(1) DEFAULT '0',
  `zoom_enabled` tinyint(1) DEFAULT '0',
  `language_enabled` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `language_settings` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `email_list` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `active` tinyint DEFAULT '1',
  `deleted` tinyint DEFAULT '0',
  `no_series` int DEFAULT '0' COMMENT 'depreached',
  `reservation_group` int DEFAULT '0',
  `open_for_registration` tinyint DEFAULT '0',
  `registration_option` int NOT NULL DEFAULT '0',
  `blocked` tinyint DEFAULT NULL,
  `blocked_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `mailserver_id` int DEFAULT '1',
  `language_id` int DEFAULT '1' COMMENT 'default language id',
  `shipment_date` varchar(50) COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `location_attribute_id` int DEFAULT '0',
  `location_type` int NOT NULL DEFAULT '0',
  `edit_allowed` tinyint DEFAULT '0',
  `show_price` tinyint(1) NOT NULL DEFAULT '0',
  `subscribe_gaveklubben` int DEFAULT '0',
  `created_datetime` datetime DEFAULT NULL,
  `modified_datetime` datetime DEFAULT NULL,
  `is_norwegian` tinyint(1) DEFAULT '0',
  `receipt_link` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `receipt_recipent` varbinary(100) DEFAULT '',
  `close_date` datetime DEFAULT NULL,
  `soft_close` int DEFAULT '0',
  `card_value` int DEFAULT NULL,
  `report_attributes` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `rapport_email` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `login_design` int NOT NULL DEFAULT '10',
  `show_tree_front` tinyint(1) NOT NULL DEFAULT '0',
  `show_qr` tinyint(1) NOT NULL DEFAULT '1',
  `kundepanel_email_regel` int NOT NULL DEFAULT '2',
  `qr_menu_settings` int NOT NULL DEFAULT '0',
  `pt_pdf` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `ptUpdate` varchar(16) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_saleperson` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_shopName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `pt_frontpage` int NOT NULL DEFAULT '1',
  `pt_mere_at_give` int NOT NULL DEFAULT '1',
  `pt_tree` tinyint(1) NOT NULL DEFAULT '1',
  `pt_bag_page` tinyint NOT NULL DEFAULT '1',
  `pt_voucher_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_saleperson_page` tinyint(1) NOT NULL DEFAULT '1',
  `pt_layout_style` int NOT NULL DEFAULT '0',
  `pt_language` int NOT NULL DEFAULT '0',
  `pt_brands_united` int DEFAULT '0',
  `pt_show_frontpage_design` tinyint NOT NULL DEFAULT '0',
  `has_login_splash` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `shop_mode` int NOT NULL DEFAULT '2',
  `localisation` int NOT NULL DEFAULT '1',
  `dbcalc_budget` int DEFAULT NULL,
  `dbcalc_standard` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `saleperson` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `manager_code` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `gdpr` tinyint(1) NOT NULL DEFAULT '1',
  `reservation_state` int DEFAULT '0' COMMENT '0: reservations not active, 1: reservations active (send to nav)',
  `reservation_language` int DEFAULT NULL,
  `reservation_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `reservation_foreign_language` int DEFAULT '0',
  `reservation_foreign_code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `vip_shop` tinyint(1) DEFAULT '0',
  `login_check_strict` int NOT NULL DEFAULT '0',
  `final_finished` tinyint(1) NOT NULL DEFAULT '0',
  `partial_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `sold` int DEFAULT '0',
  `welcome_mail` datetime DEFAULT NULL,
  `status_id` bigint UNSIGNED DEFAULT NULL,
  `paper_settings` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `updated_datetime` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `singleselect` int NOT NULL DEFAULT '0',
  `login_background` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_address`
--

CREATE TABLE `shop_address` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `shop_invoice_id` int NOT NULL DEFAULT '0',
  `index` int NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `zip` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `locations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `att` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `country` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `vatno` varchar(100) NOT NULL DEFAULT '',
  `freight_note` text,
  `dot` int NOT NULL DEFAULT '0',
  `dot_date` varchar(25) DEFAULT NULL,
  `carryup` int NOT NULL DEFAULT '0',
  `carryup_type` int NOT NULL DEFAULT '0' COMMENT '0: Ikke aktivt valgt, 1: Har ikke elevator, 2: plads til halvpalle, 3: plads til helpalle'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_address_0307`
--

CREATE TABLE `shop_address_0307` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `shop_invoice_id` int NOT NULL DEFAULT '0',
  `index` int NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `zip` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `locations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `att` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `country` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `vatno` varchar(100) NOT NULL DEFAULT '',
  `freight_note` text,
  `dot` int NOT NULL DEFAULT '0',
  `dot_date` varchar(25) DEFAULT NULL,
  `carryup` int NOT NULL DEFAULT '0',
  `carryup_type` int NOT NULL DEFAULT '0' COMMENT '0: Ikke aktivt valgt, 1: Har ikke elevator, 2: plads til halvpalle, 3: plads til helpalle'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_approval`
--

CREATE TABLE `shop_approval` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `pt_approval` datetime DEFAULT NULL,
  `orderdata_approval` int NOT NULL DEFAULT '0' COMMENT '0 ikke godkendt fra sælger\r\n1 godkendt af sælger, ikke godkendt af regnskab\r\n2 godkendt af regnskab\r\n3 afvist af regnskab, skal godkendes af sælger',
  `orderdata_approval_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_approval` int NOT NULL DEFAULT '0' COMMENT '0 ikke godkendt fra sælger 1 godkendt af sælger, ikke godkendt af regnskab 2 godkendt af regnskab 3 afvist af regnskab, skal godkendes af sælger	',
  `shop_start` int DEFAULT '1',
  `shop_end` int DEFAULT '1',
  `shop_delivery` int DEFAULT '1',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_attribute`
--

CREATE TABLE `shop_attribute` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL DEFAULT '0',
  `index` int DEFAULT '0',
  `name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `data_type` int DEFAULT '0',
  `is_username` tinyint(1) DEFAULT '0',
  `is_password` tinyint(1) DEFAULT '0',
  `is_email` tinyint(1) DEFAULT '0',
  `is_name` tinyint(1) DEFAULT '0',
  `is_locked` tinyint(1) DEFAULT '0',
  `is_mandatory` tinyint(1) DEFAULT '0',
  `is_visible` tinyint(1) DEFAULT '0',
  `is_list` tinyint(1) DEFAULT '0',
  `is_delivery` tinyint DEFAULT '0',
  `list_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `languages` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_block_message`
--

CREATE TABLE `shop_block_message` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL DEFAULT '0',
  `shop_invoice_id` int NOT NULL DEFAULT '0',
  `block_type` varchar(150) NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_by` int NOT NULL DEFAULT '0',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `release_status` int NOT NULL DEFAULT '0',
  `release_date` timestamp NULL DEFAULT NULL,
  `release_user` int DEFAULT NULL,
  `release_message` text,
  `tech_block` int NOT NULL DEFAULT '0',
  `silent` int NOT NULL DEFAULT '0',
  `debug_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_board`
--

CREATE TABLE `shop_board` (
  `id` int NOT NULL,
  `fk_shop` int DEFAULT NULL,
  `shop_navn` varchar(150) NOT NULL,
  `salger` varchar(256) NOT NULL,
  `valgshopansvarlig` varchar(256) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `ordretype` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `salgsordrenummer` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `rammeordrenummer` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `reservationsordrenummer` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kunde` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `kontaktperson` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `mail` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `telefon` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `antal_gaver` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '0',
  `antal_gavevalg` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '0',
  `shop_aabner` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `shop_lukker` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `levering` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `flere_leveringsadresser` tinyint(1) DEFAULT '0',
  `autogave` varchar(510) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `sprog_lag` tinyint(1) DEFAULT NULL,
  `reminder` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `login` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `indpakning` tinyint(1) DEFAULT '0',
  `navn_paa_gaver` tinyint(1) DEFAULT '0',
  `julekort` tinyint(1) DEFAULT '0',
  `privatlevering` tinyint(1) DEFAULT '0',
  `fane` int DEFAULT '1',
  `demo` tinyint(1) DEFAULT '0',
  `afventer_info` tinyint(1) DEFAULT '0',
  `shop_i_gang` tinyint(1) DEFAULT '0',
  `udland` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `shop_lukket` tinyint(1) DEFAULT '0',
  `fordelingsliste` tinyint(1) DEFAULT '0',
  `indkob` tinyint(1) DEFAULT '0',
  `reserveret` tinyint(1) DEFAULT '0',
  `demoshop` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `pakkeri` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '',
  `shop_afsluttet` tinyint(1) DEFAULT NULL,
  `info` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `localization` int NOT NULL DEFAULT '1',
  `nav_synced` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_company_return_adress`
--

CREATE TABLE `shop_company_return_adress` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `address2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `postal_code` int DEFAULT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `country` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_phone` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_data_types`
--

CREATE TABLE `shop_data_types` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `show_list` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_delivery`
--

CREATE TABLE `shop_delivery` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `shop_order_id` int NOT NULL,
  `shop_invoice_id` int NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `ship_to_company` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `ship_to_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `ship_to_address_2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `ship_to_postal_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `ship_to_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `ship_to_country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `contact_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `dot` int NOT NULL DEFAULT '0',
  `carryup` int NOT NULL DEFAULT '0',
  `note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_date` date DEFAULT NULL,
  `dot_time` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_description`
--

CREATE TABLE `shop_description` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `language_id` int NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `headline` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '###'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_documents`
--

CREATE TABLE `shop_documents` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `document_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `document_category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT 'general',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `original_filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `file_size` int DEFAULT NULL,
  `file_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_date` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_invoice`
--

CREATE TABLE `shop_invoice` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `invoice_index` int NOT NULL DEFAULT '0',
  `is_foreign` int NOT NULL DEFAULT '0',
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `invoice_fee_value` int NOT NULL DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `invoice_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_invoice_2`
--

CREATE TABLE `shop_invoice_2` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_special_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `multiple_deliveries_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `plant_tree_price` int DEFAULT NULL,
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `multiple_budgets_data` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `economy_info` int DEFAULT NULL,
  `economy_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `order_info` int DEFAULT NULL,
  `order_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'fast',
  `deliverydate_receipt_toggle` int DEFAULT '0',
  `deliverydate_receipt_custom_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_index` int NOT NULL DEFAULT '0',
  `invoice_state` int NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_loan`
--

CREATE TABLE `shop_loan` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `present_model_id` int NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `pickup_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata`
--

CREATE TABLE `shop_metadata` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_special_note` text COLLATE utf8mb4_danish_ci,
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `multiple_deliveries_data` text COLLATE utf8mb4_danish_ci,
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `plant_tree_price` int DEFAULT NULL,
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `multiple_budgets_data` varchar(400) COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `economy_info` int DEFAULT NULL,
  `economy_info_note` text COLLATE utf8mb4_danish_ci,
  `order_info` int DEFAULT NULL,
  `order_info_note` text COLLATE utf8mb4_danish_ci,
  `delivery_type` varchar(10) COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'fast',
  `deliverydate_receipt_toggle` int DEFAULT '0',
  `deliverydate_receipt_custom_text` text COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata_1906`
--

CREATE TABLE `shop_metadata_1906` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata_2006_2`
--

CREATE TABLE `shop_metadata_2006_2` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata_2306`
--

CREATE TABLE `shop_metadata_2306` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_special_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `multiple_deliveries_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `plant_tree_price` int DEFAULT NULL,
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `multiple_budgets_data` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `economy_info` int DEFAULT NULL,
  `economy_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `order_info` int DEFAULT NULL,
  `order_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'fast',
  `deliverydate_receipt_toggle` int DEFAULT '0',
  `deliverydate_receipt_custom_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata_2506`
--

CREATE TABLE `shop_metadata_2506` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `so_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'valgshop',
  `salesperson_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `is_foreign` tinyint(1) NOT NULL DEFAULT '0',
  `is_foreign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `payment_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_special` int NOT NULL DEFAULT '0',
  `payment_special_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `payment_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `invoice_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `invoice_fee_value` int DEFAULT '0',
  `environment_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `discount_option` tinyint(1) DEFAULT '0',
  `discount_value` int DEFAULT '0',
  `valgshop_fee` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_date` date DEFAULT NULL,
  `handover_date` date DEFAULT NULL,
  `multiple_deliveries` int NOT NULL DEFAULT '0',
  `multiple_deliveries_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `private_delivery` int NOT NULL DEFAULT '0',
  `privatedelivery_price` int DEFAULT NULL,
  `foreign_delivery` int NOT NULL DEFAULT '0',
  `foreign_delivery_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_delivery_date` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `foreign_names` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `delivery_terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliveryprice_option` int DEFAULT '0',
  `deliveryprice_amount` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `deliveryprice_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_internal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_note_external` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `dot_use` int NOT NULL DEFAULT '0',
  `dot_amount` int NOT NULL DEFAULT '0',
  `dot_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `dot_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `carryup_use` int NOT NULL DEFAULT '0',
  `carryup_amount` int NOT NULL DEFAULT '0',
  `carryup_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `carryup_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `budget` int DEFAULT NULL,
  `flex_budget` int DEFAULT '0',
  `user_count` int NOT NULL DEFAULT '0',
  `present_count` int NOT NULL DEFAULT '0',
  `autogave_use` int NOT NULL DEFAULT '-1',
  `autogave_itemno` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `plant_tree` int NOT NULL DEFAULT '0',
  `plant_tree_price` int DEFAULT NULL,
  `present_nametag` int NOT NULL DEFAULT '0',
  `present_nametag_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_papercard` int NOT NULL DEFAULT '0',
  `present_papercard_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `present_wrap` int NOT NULL DEFAULT '0',
  `present_wrap_price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `handling_special` int NOT NULL DEFAULT '0',
  `handling_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `loan_use` int NOT NULL DEFAULT '0',
  `loan_deliverydate` date DEFAULT NULL,
  `loan_pickupdate` date DEFAULT NULL,
  `loan_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deadline_testshop` datetime DEFAULT NULL,
  `deadline_changes` datetime DEFAULT NULL,
  `deadline_customerdata` datetime DEFAULT NULL,
  `deadline_listconfirm` datetime DEFAULT NULL,
  `reminder_use` int NOT NULL DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `other_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `user_username` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_username_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `user_password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `user_password_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `gaveklubben_link` int NOT NULL DEFAULT '0',
  `language` int NOT NULL DEFAULT '0',
  `language_names` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `otheragreements_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `deliverydate_receipt` date DEFAULT NULL,
  `nav_debitor_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '',
  `mail_welcome_sent` datetime DEFAULT NULL,
  `requisition_no` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `prepayment` int NOT NULL DEFAULT '1',
  `prepayment_postingdate` datetime DEFAULT NULL,
  `prepayment_duedate` datetime DEFAULT NULL,
  `prepayment_reissue` int NOT NULL DEFAULT '0',
  `order_state` int NOT NULL DEFAULT '0',
  `nav_syncdate` datetime DEFAULT NULL,
  `suppress_orderconf` int NOT NULL DEFAULT '0',
  `packing_state` int NOT NULL DEFAULT '0',
  `shopboard_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `has_contract` int DEFAULT '0',
  `flex_start_delivery_date` date DEFAULT NULL,
  `flex_end_delivery_date` date DEFAULT NULL,
  `early_delivery` int DEFAULT '0',
  `private_retur_type` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `multiple_budgets_data` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `economy_info` int DEFAULT NULL,
  `economy_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `order_info` int DEFAULT NULL,
  `order_info_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `delivery_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT 'fast',
  `deliverydate_receipt_toggle` int DEFAULT '0',
  `deliverydate_receipt_custom_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_metadata_versions`
--

CREATE TABLE `shop_metadata_versions` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `version_number` int NOT NULL,
  `metadata_data` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON data with all form fields',
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_autosave` tinyint(1) DEFAULT '0' COMMENT '1 for autosave, 0 for manual save',
  `field_changes_count` int DEFAULT '0' COMMENT 'Number of fields changed from previous version'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores versions of shop metadata for history tracking';

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_order`
--

CREATE TABLE `shop_order` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `company_id` int NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `order_state` int NOT NULL DEFAULT '0',
  `order_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `navsync_date` datetime DEFAULT NULL,
  `navsync_changes` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_present`
--

CREATE TABLE `shop_present` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `properties` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `index_` int DEFAULT '0',
  `active` tinyint DEFAULT '1',
  `alias` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `modelalias` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_present_0906`
--

CREATE TABLE `shop_present_0906` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `properties` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `index_` int DEFAULT '0',
  `active` tinyint DEFAULT '1',
  `alias` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `modelalias` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_present_category`
--

CREATE TABLE `shop_present_category` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `name_dk` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `name_no` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `name_en` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `name_se` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `name_de` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_present_company_rules`
--

CREATE TABLE `shop_present_company_rules` (
  `company_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int NOT NULL,
  `rules` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_present_rules`
--

CREATE TABLE `shop_present_rules` (
  `id` int NOT NULL,
  `present_id` int NOT NULL,
  `card_id` varchar(12) NOT NULL,
  `rules` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_report`
--

CREATE TABLE `shop_report` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `profile_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_responsible`
--

CREATE TABLE `shop_responsible` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_statuses`
--

CREATE TABLE `shop_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_user`
--

CREATE TABLE `shop_user` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `company_id` int NOT NULL,
  `username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `expire_date` date DEFAULT NULL,
  `is_demo` tinyint DEFAULT '0',
  `token` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `token_created` datetime DEFAULT NULL,
  `is_giftcertificate` int DEFAULT '0',
  `blocked` tinyint DEFAULT '0',
  `shutdown` tinyint(1) NOT NULL DEFAULT '0',
  `is_delivery` tinyint DEFAULT '0',
  `delivery_printed` tinyint DEFAULT '0',
  `company_order_id` int DEFAULT NULL,
  `freight_calculated` tinyint(1) DEFAULT '0',
  `delivery_print_date` datetime DEFAULT NULL,
  `delivery_state` int NOT NULL DEFAULT '0' COMMENT '0 not processed, 1: waiting to be sent, 2: sent, 3: error in delivery, 4: blocked, 5: processed externally, 6: sent externally, 9: waiting manual check',
  `navsync_date` datetime DEFAULT NULL,
  `navsync_status` int NOT NULL DEFAULT '0',
  `navsync_response` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `navsync_error` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_replaced` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_id` int DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `onpluk` int NOT NULL DEFAULT '0',
  `gdpr` timestamp NULL DEFAULT NULL,
  `basket_id` int DEFAULT '0',
  `card_values` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_user_0207`
--

CREATE TABLE `shop_user_0207` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `company_id` int NOT NULL,
  `username` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
  `expire_date` date DEFAULT NULL,
  `is_demo` tinyint DEFAULT '0',
  `token` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `token_created` datetime DEFAULT NULL,
  `is_giftcertificate` int DEFAULT '0',
  `blocked` tinyint DEFAULT '0',
  `shutdown` tinyint(1) NOT NULL DEFAULT '0',
  `is_delivery` tinyint DEFAULT '0',
  `delivery_printed` tinyint DEFAULT '0',
  `company_order_id` int DEFAULT NULL,
  `freight_calculated` tinyint(1) DEFAULT '0',
  `delivery_print_date` datetime DEFAULT NULL,
  `delivery_state` int NOT NULL DEFAULT '0' COMMENT '0 not processed, 1: waiting to be sent, 2: sent, 3: error in delivery, 4: blocked, 5: processed externally, 6: sent externally, 9: waiting manual check',
  `navsync_date` datetime DEFAULT NULL,
  `navsync_status` int NOT NULL DEFAULT '0',
  `navsync_response` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `navsync_error` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `is_replaced` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_id` int DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `onpluk` int NOT NULL DEFAULT '0',
  `gdpr` timestamp NULL DEFAULT NULL,
  `basket_id` int DEFAULT '0',
  `card_values` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_user_autoselect`
--

CREATE TABLE `shop_user_autoselect` (
  `id` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `company_id` int DEFAULT NULL,
  `shopuser_id` int DEFAULT NULL,
  `present_id` int DEFAULT NULL,
  `present_model_id` int DEFAULT '0',
  `created_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `shop_user_log`
--

CREATE TABLE `shop_user_log` (
  `id` int NOT NULL,
  `shop_user_id` int NOT NULL,
  `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_grp`
--

CREATE TABLE `sms_grp` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_jobs`
--

CREATE TABLE `sms_jobs` (
  `id` int NOT NULL,
  `job_name` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `title` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `body` varchar(2000) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `grp_send_to` int NOT NULL,
  `is_finish` tinyint(1) NOT NULL DEFAULT '0',
  `finish_time` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_log`
--

CREATE TABLE `sms_log` (
  `id` int NOT NULL,
  `msg` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `type` int NOT NULL,
  `job_id` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_queue`
--

CREATE TABLE `sms_queue` (
  `id` int NOT NULL,
  `fk_sms_job_id` int NOT NULL,
  `fk_sms_user_id` int NOT NULL,
  `send_tlf` varchar(24) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `send_from_title` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `send_body` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `is_send` tinyint(1) NOT NULL DEFAULT '0',
  `send_time` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_unsubscribe`
--

CREATE TABLE `sms_unsubscribe` (
  `id` int NOT NULL,
  `tlf` int NOT NULL,
  `active` int NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_unsubscribe_mail`
--

CREATE TABLE `sms_unsubscribe_mail` (
  `id` int NOT NULL,
  `shop_user` int NOT NULL,
  `mail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `tlf` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `sms_user`
--

CREATE TABLE `sms_user` (
  `id` int NOT NULL,
  `shopuser_id` int DEFAULT NULL,
  `tlf` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `fornavn` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `efternavn` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `grp_id` int NOT NULL DEFAULT '9',
  `ref` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system`
--

CREATE TABLE `system` (
  `id` int NOT NULL,
  `order_nos_id` int DEFAULT NULL,
  `present_nos_id` int DEFAULT NULL,
  `demo_order_nos_id` int DEFAULT NULL,
  `gift_certificate_nos_id` int DEFAULT NULL,
  `company_order_nos_id` int DEFAULT NULL,
  `is_production` tinyint(1) DEFAULT NULL,
  `full_trace` tinyint(1) DEFAULT NULL,
  `is_mailing` tinyint DEFAULT '0',
  `smtp_server` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `smtp_username` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `smtp_password` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `smtp_port` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `last_order_update` datetime DEFAULT NULL COMMENT 'sidste gang ordre blev opdateret via batch k�rsel',
  `dummy_present` int DEFAULT NULL,
  `test_email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL COMMENT 'All emails sent here in test environment',
  `is_mailing_withids` int NOT NULL DEFAULT '0',
  `is_mailing_withoutids` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_log`
--

CREATE TABLE `system_log` (
  `id` int NOT NULL,
  `user_id` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `controller` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `created_datetime` datetime DEFAULT NULL,
  `committed` tinyint DEFAULT '0',
  `error_message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `error_trace` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
  `ip` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `browser` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `url` varchar(500) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `runtime` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_log_pdf_download`
--

CREATE TABLE `system_log_pdf_download` (
  `id` int NOT NULL,
  `token` varchar(40) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_session`
--

CREATE TABLE `system_session` (
  `id` int NOT NULL,
  `session` varchar(150) NOT NULL,
  `user_id` int NOT NULL,
  `created_datetime` datetime DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `browser` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_user`
--

CREATE TABLE `system_user` (
  `id` int NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `username` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `password` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `userlevel` int DEFAULT '0',
  `hash` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `token` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT '',
  `token_created` datetime DEFAULT NULL,
  `is_service_user` tinyint DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `salespersoncode` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `language` int NOT NULL DEFAULT '1',
  `saleportale_id` int DEFAULT NULL,
  `email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL DEFAULT '',
  `use_2fa` int NOT NULL DEFAULT '1',
  `sales_person_code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `localization_id` bigint UNSIGNED DEFAULT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_user_activity`
--

CREATE TABLE `system_user_activity` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `system_user_device`
--

CREATE TABLE `system_user_device` (
  `id` int NOT NULL,
  `system_user_id` int NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expire` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_count` int NOT NULL DEFAULT '0',
  `device_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT '',
  `sent` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `code_tries` int NOT NULL DEFAULT '0',
  `reason` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `tempmail`
--

CREATE TABLE `tempmail` (
  `id` int NOT NULL,
  `mail` varchar(50) NOT NULL,
  `is_check` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `tempmail_log`
--

CREATE TABLE `tempmail_log` (
  `id` int NOT NULL,
  `log` varchar(300) NOT NULL,
  `type` int NOT NULL,
  `group_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `temp_no_budget`
--

CREATE TABLE `temp_no_budget` (
  `id` int NOT NULL,
  `itemno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `budget` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `unsub`
--

CREATE TABLE `unsub` (
  `id` int NOT NULL,
  `mail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `user_attribute`
--

CREATE TABLE `user_attribute` (
  `id` int NOT NULL,
  `shopuser_id` int NOT NULL DEFAULT '0',
  `attribute_id` int NOT NULL DEFAULT '0',
  `attribute_value` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `shop_id` int NOT NULL DEFAULT '0',
  `company_id` int NOT NULL,
  `is_username` tinyint(1) DEFAULT '0',
  `is_password` tinyint(1) DEFAULT '0',
  `is_email` tinyint(1) DEFAULT '0',
  `is_name` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `user_attribute_0307`
--

CREATE TABLE `user_attribute_0307` (
  `id` int NOT NULL,
  `shopuser_id` int NOT NULL DEFAULT '0',
  `attribute_id` int NOT NULL DEFAULT '0',
  `attribute_value` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '',
  `shop_id` int NOT NULL DEFAULT '0',
  `company_id` int NOT NULL,
  `is_username` tinyint(1) DEFAULT '0',
  `is_password` tinyint(1) DEFAULT '0',
  `is_email` tinyint(1) DEFAULT '0',
  `is_name` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `user_permission`
--

CREATE TABLE `user_permission` (
  `id` int NOT NULL,
  `systemuser_id` int NOT NULL,
  `view_giftshops` tinyint(1) DEFAULT '0',
  `view_cardshops` tinyint(1) DEFAULT '0',
  `view_presentadmin` tinyint(1) DEFAULT '0',
  `view_system` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `user_tab_permission`
--

CREATE TABLE `user_tab_permission` (
  `id` int NOT NULL,
  `systemuser_id` int NOT NULL,
  `tap_id` int NOT NULL,
  `tap_group` int DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `varenrcache`
--

CREATE TABLE `varenrcache` (
  `id` int NOT NULL,
  `varenr` varchar(150) NOT NULL,
  `isvalid` int NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL,
  `lastsync` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `vlager`
--

CREATE TABLE `vlager` (
  `id` int NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `location` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `language_id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `active` int NOT NULL DEFAULT '0',
  `ecoreport_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `vlager_incoming`
--

CREATE TABLE `vlager_incoming` (
  `id` int NOT NULL,
  `vlager_id` int NOT NULL,
  `sono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `created` datetime NOT NULL,
  `received` datetime DEFAULT NULL,
  `sender_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `receiver_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `vlager_incoming_line`
--

CREATE TABLE `vlager_incoming_line` (
  `id` int NOT NULL,
  `vlager_id` int NOT NULL,
  `vlager_incoming_id` int NOT NULL,
  `itemno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity_order` int NOT NULL,
  `quantity_received` int NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `vlager_item`
--

CREATE TABLE `vlager_item` (
  `id` int NOT NULL,
  `vlager_id` int NOT NULL,
  `itemno` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `quantity_available` int NOT NULL,
  `quantity_incoming` int NOT NULL,
  `quantity_outgoing` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `vlager_item_log`
--

CREATE TABLE `vlager_item_log` (
  `id` int NOT NULL,
  `vlager_id` int NOT NULL,
  `vlager_item_id` int NOT NULL,
  `shipment_id` int DEFAULT NULL,
  `vlager_incoming_line_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `balance` int NOT NULL,
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `log_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `voucher`
--

CREATE TABLE `voucher` (
  `id` int NOT NULL,
  `voucher` varchar(15) NOT NULL,
  `shop_user_id` int NOT NULL DEFAULT '0',
  `company_id` int NOT NULL,
  `is_send` tinyint(1) NOT NULL DEFAULT '0',
  `send_data` datetime NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `voucher_quick`
--

CREATE TABLE `voucher_quick` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `voucher_number` varchar(10) NOT NULL,
  `registration` varchar(40) NOT NULL,
  `is_distributed` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `warehouse_files`
--

CREATE TABLE `warehouse_files` (
  `id` int NOT NULL,
  `filename` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `real_filename` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `file_size` float DEFAULT '0',
  `shop_id` int NOT NULL,
  `user_id` int DEFAULT '0',
  `replace_file` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT '0',
  `replace_file_time` datetime DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `post_block_upload` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `warehouse_settings`
--

CREATE TABLE `warehouse_settings` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
  `packaging_status` int NOT NULL DEFAULT '0',
  `noter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `note_move_order` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `note_from_warehouse_to_gf` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
  `log_menu` timestamp NULL DEFAULT NULL,
  `log_info` timestamp NULL DEFAULT NULL,
  `log_download` timestamp NULL DEFAULT NULL,
  `log_status` datetime DEFAULT NULL,
  `pick_approval` int NOT NULL DEFAULT '0',
  `approved_count_date` datetime DEFAULT NULL,
  `approved_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `approved_count_date_approved_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `approved_package_instructions` int DEFAULT NULL,
  `approved_package_instructions_approved_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `approved_ontime` int DEFAULT NULL,
  `approved_ontime_approved_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `weborderlog`
--

CREATE TABLE `weborderlog` (
  `id` int NOT NULL,
  `error` varchar(200) NOT NULL,
  `input` text NOT NULL,
  `output` varchar(250) NOT NULL,
  `orderid` int NOT NULL DEFAULT '0',
  `shop_id` int NOT NULL DEFAULT '0',
  `url` varchar(200) NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `accesstoken`
--
ALTER TABLE `accesstoken`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `token_2` (`token`);

--
-- Indeks for tabel `actionlog`
--
ALTER TABLE `actionlog`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `airfryer`
--
ALTER TABLE `airfryer`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `app_log`
--
ALTER TABLE `app_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `app_users`
--
ALTER TABLE `app_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks for tabel `blockmessage`
--
ALTER TABLE `blockmessage`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `cardshop_expiredate`
--
ALTER TABLE `cardshop_expiredate`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `cardshop_freight`
--
ALTER TABLE `cardshop_freight`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `cardshop_freight_09072025`
--
ALTER TABLE `cardshop_freight_09072025`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `cardshop_settings`
--
ALTER TABLE `cardshop_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`contact_email`,`token`) USING BTREE,
  ADD KEY `pid_index` (`pid`);

--
-- Indeks for tabel `company_2306`
--
ALTER TABLE `company_2306`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token` (`contact_email`,`token`) USING BTREE,
  ADD KEY `pid_index` (`pid`);

--
-- Indeks for tabel `company_notes`
--
ALTER TABLE `company_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `company_order`
--
ALTER TABLE `company_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`);

--
-- Indeks for tabel `company_order_history`
--
ALTER TABLE `company_order_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`,`version_no`);

--
-- Indeks for tabel `company_order_item`
--
ALTER TABLE `company_order_item`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `company_shipping_cost`
--
ALTER TABLE `company_shipping_cost`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `company_shipping_cost_040825`
--
ALTER TABLE `company_shipping_cost_040825`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `company_shop`
--
ALTER TABLE `company_shop`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_shop` (`company_id`,`shop_id`),
  ADD KEY `company` (`company_id`),
  ADD KEY `shop` (`shop_id`);

--
-- Indeks for tabel `cronlog`
--
ALTER TABLE `cronlog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cronlog_jobname` (`jobname`),
  ADD KEY `cronlog_created` (`created`);

--
-- Indeks for tabel `debug_log`
--
ALTER TABLE `debug_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `dsvstatus`
--
ALTER TABLE `dsvstatus`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `dsvstatus_log`
--
ALTER TABLE `dsvstatus_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `dsv_input`
--
ALTER TABLE `dsv_input`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `early_present`
--
ALTER TABLE `early_present`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `expire_date`
--
ALTER TABLE `expire_date`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `ftp_download`
--
ALTER TABLE `ftp_download`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `ftp_queue`
--
ALTER TABLE `ftp_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `ftp_server`
--
ALTER TABLE `ftp_server`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `gaveklubben`
--
ALTER TABLE `gaveklubben`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik mail` (`email`),
  ADD UNIQUE KEY `mobil unik` (`mobil`);

--
-- Indeks for tabel `gaveklubben_2022`
--
ALTER TABLE `gaveklubben_2022`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indeks for tabel `gift_certificate`
--
ALTER TABLE `gift_certificate`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_no` (`certificate_no`),
  ADD UNIQUE KEY `password` (`password`),
  ADD KEY `shop` (`shop_id`),
  ADD KEY `sort` (`no_series`,`certificate_no`),
  ADD KEY `sort2` (`no_series`,`id`),
  ADD KEY `shop_id` (`shop_id`,`company_id`,`no_series`,`value`,`expire_date`),
  ADD KEY `ny` (`reservation_group`,`expire_date`,`company_id`,`shop_id`,`is_printed`,`is_emailed`,`blocked`,`certificate_no`);

--
-- Indeks for tabel `homerunner_log`
--
ALTER TABLE `homerunner_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `homerunner_webhook`
--
ALTER TABLE `homerunner_webhook`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `kontainer_queue`
--
ALTER TABLE `kontainer_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `done_job_id` (`job_id`,`done`);

--
-- Indeks for tabel `kontainer_sync_job`
--
ALTER TABLE `kontainer_sync_job`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `lookupdata`
--
ALTER TABLE `lookupdata`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `magento_pim_category_index`
--
ALTER TABLE `magento_pim_category_index`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `magento_pim_suppliers_index`
--
ALTER TABLE `magento_pim_suppliers_index`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `magento_stock_change`
--
ALTER TABLE `magento_stock_change`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `magento_stock_total`
--
ALTER TABLE `magento_stock_total`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `magento_vip_stock`
--
ALTER TABLE `magento_vip_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `mail_event`
--
ALTER TABLE `mail_event`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `mail_library`
--
ALTER TABLE `mail_library`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maillibrary_unique` (`handle`,`language_id`,`shop_id`,`company_id`),
  ADD KEY `maillibrary_handle_language` (`handle`,`language_id`);

--
-- Indeks for tabel `mail_queue`
--
ALTER TABLE `mail_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `sent` (`sent`,`error`,`id`),
  ADD KEY `reciepnt` (`recipent_email`),
  ADD KEY `subject` (`subject`),
  ADD KEY `company_order_id` (`company_order_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `send_group` (`send_group`),
  ADD KEY `idx_sent_datetime_sent` (`sent_datetime`,`sent`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks for tabel `mail_queue_block`
--
ALTER TABLE `mail_queue_block`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `mail_server`
--
ALTER TABLE `mail_server`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `mail_template`
--
ALTER TABLE `mail_template`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `mail_track`
--
ALTER TABLE `mail_track`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `moms`
--
ALTER TABLE `moms`
  ADD UNIQUE KEY `varenr_unik` (`varenr`);

--
-- Indeks for tabel `monitor_itemno`
--
ALTER TABLE `monitor_itemno`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_bomitem`
--
ALTER TABLE `navision_bomitem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no` (`no`),
  ADD KEY `parent` (`parent_item_no`);

--
-- Indeks for tabel `navision_call_log`
--
ALTER TABLE `navision_call_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created` (`created`),
  ADD KEY `idx_service_url` (`service`,`url`),
  ADD KEY `idx_service_url_created` (`service`,`url`,`created`),
  ADD KEY `idx_service_created` (`service`,`created`);

--
-- Indeks for tabel `navision_choice_doc`
--
ALTER TABLE `navision_choice_doc`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_company_no`
--
ALTER TABLE `navision_company_no`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_item`
--
ALTER TABLE `navision_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nav_item_no` (`no`,`deleted`) USING BTREE;

--
-- Indeks for tabel `navision_itemrename`
--
ALTER TABLE `navision_itemrename`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_location`
--
ALTER TABLE `navision_location`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_order_doc`
--
ALTER TABLE `navision_order_doc`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_order_line`
--
ALTER TABLE `navision_order_line`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_reservation_done`
--
ALTER TABLE `navision_reservation_done`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_done_sono` (`sono`);

--
-- Indeks for tabel `navision_reservation_done_item`
--
ALTER TABLE `navision_reservation_done_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doneitem_reservation_done_id` (`reservation_done_id`);

--
-- Indeks for tabel `navision_reservation_log`
--
ALTER TABLE `navision_reservation_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_salesperson`
--
ALTER TABLE `navision_salesperson`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_salesprice`
--
ALTER TABLE `navision_salesprice`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_stock_total`
--
ALTER TABLE `navision_stock_total`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `navision_vs_state`
--
ALTER TABLE `navision_vs_state`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `navision_sv_state_shop_id` (`shop_id`);

--
-- Indeks for tabel `navision_vs_version`
--
ALTER TABLE `navision_vs_version`
  ADD PRIMARY KEY (`id`),
  ADD KEY `navvsv_shopid` (`shop_id`);

--
-- Indeks for tabel `number_series`
--
ALTER TABLE `number_series`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indeks for tabel `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indeks for tabel `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indeks for tabel `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indeks for tabel `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`shopuser_id`),
  ADD UNIQUE KEY `order_no_int` (`order_no`),
  ADD KEY `shopiddemo` (`shop_id`,`is_demo`),
  ADD KEY `presentid` (`present_id`),
  ADD KEY `shop_id` (`shop_id`,`present_id`,`present_model_present_no`),
  ADD KEY `user_username` (`user_username`),
  ADD KEY `companyID` (`company_id`);

--
-- Indeks for tabel `order_0307`
--
ALTER TABLE `order_0307`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`shopuser_id`),
  ADD UNIQUE KEY `order_no_int` (`order_no`),
  ADD KEY `shopiddemo` (`shop_id`,`is_demo`),
  ADD KEY `presentid` (`present_id`),
  ADD KEY `shop_id` (`shop_id`,`present_id`,`present_model_present_no`),
  ADD KEY `user_username` (`user_username`),
  ADD KEY `companyID` (`company_id`);

--
-- Indeks for tabel `order_attribute`
--
ALTER TABLE `order_attribute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopuser_id` (`shopuser_id`),
  ADD KEY `orderid` (`order_id`),
  ADD KEY `att_val` (`attribute_value`);

--
-- Indeks for tabel `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `shopuser` (`shopuser_id`,`order_no`);

--
-- Indeks for tabel `order_history_attribute`
--
ALTER TABLE `order_history_attribute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopuser_id` (`shopuser_id`);

--
-- Indeks for tabel `order_present_complaint`
--
ALTER TABLE `order_present_complaint`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `order_present_entry`
--
ALTER TABLE `order_present_entry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopuser` (`shopuser_id`),
  ADD KEY `orderid` (`order_id`);

--
-- Indeks for tabel `order_test`
--
ALTER TABLE `order_test`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`shopuser_id`),
  ADD UNIQUE KEY `order_no_int` (`order_no`),
  ADD KEY `shopiddemo` (`shop_id`,`is_demo`),
  ADD KEY `presentid` (`present_id`),
  ADD KEY `shop_id` (`shop_id`,`present_id`,`present_model_present_no`),
  ADD KEY `user_username` (`user_username`),
  ADD KEY `companyID` (`company_id`),
  ADD KEY `present_model_id` (`present_model_id`);

--
-- Indeks for tabel `paper_log`
--
ALTER TABLE `paper_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `paper_order`
--
ALTER TABLE `paper_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `user_id_shop` (`user_id`,`shop_id`);

--
-- Indeks for tabel `paper_user_attribute`
--
ALTER TABLE `paper_user_attribute`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`shop_id`) USING BTREE;

--
-- Indeks for tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indeks for tabel `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indeks for tabel `permission_user`
--
ALTER TABLE `permission_user`
  ADD PRIMARY KEY (`user_id`,`permission_id`,`user_type`),
  ADD KEY `permission_user_permission_id_foreign` (`permission_id`);

--
-- Indeks for tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks for tabel `pim_logo`
--
ALTER TABLE `pim_logo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `logo_id_unik` (`logo_id`);

--
-- Indeks for tabel `pim_logo_log`
--
ALTER TABLE `pim_logo_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `pim_nav_itemnr_sync`
--
ALTER TABLE `pim_nav_itemnr_sync`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `pim_sync`
--
ALTER TABLE `pim_sync`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `pim_sync_queue`
--
ALTER TABLE `pim_sync_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_handled` (`is_handled`),
  ADD KEY `itennr` (`item_nr`);

--
-- Indeks for tabel `postnord_orderreport`
--
ALTER TABLE `postnord_orderreport`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `postnord_varenr`
--
ALTER TABLE `postnord_varenr`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `postnord_varenrlog`
--
ALTER TABLE `postnord_varenrlog`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present`
--
ALTER TABLE `present`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `crossstats` (`copy_of`,`shop_id`),
  ADD KEY `childpresentId` (`pchild`),
  ADD KEY `pchild` (`pchild`);

--
-- Indeks for tabel `presentation_group`
--
ALTER TABLE `presentation_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group` (`group_id`);

--
-- Indeks for tabel `presentation_group_0906`
--
ALTER TABLE `presentation_group_0906`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group` (`group_id`);

--
-- Indeks for tabel `presentation_sale`
--
ALTER TABLE `presentation_sale`
  ADD UNIQUE KEY `_id` (`id`);

--
-- Indeks for tabel `presentation_sale_log`
--
ALTER TABLE `presentation_sale_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `presentation_sale_pdf`
--
ALTER TABLE `presentation_sale_pdf`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `presentation_sale_present`
--
ALTER TABLE `presentation_sale_present`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `presentation_sale_profile`
--
ALTER TABLE `presentation_sale_profile`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `presentation_sale_profile_2105`
--
ALTER TABLE `presentation_sale_profile_2105`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present_0307_2`
--
ALTER TABLE `present_0307_2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `crossstats` (`copy_of`,`shop_id`),
  ADD KEY `childpresentId` (`pchild`),
  ADD KEY `pchild` (`pchild`);

--
-- Indeks for tabel `present_0906`
--
ALTER TABLE `present_0906`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `crossstats` (`copy_of`,`shop_id`),
  ADD KEY `childpresentId` (`pchild`),
  ADD KEY `pchild` (`pchild`);

--
-- Indeks for tabel `present_2406`
--
ALTER TABLE `present_2406`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `crossstats` (`copy_of`,`shop_id`),
  ADD KEY `childpresentId` (`pchild`),
  ADD KEY `pchild` (`pchild`);

--
-- Indeks for tabel `present_20250705`
--
ALTER TABLE `present_20250705`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `crossstats` (`copy_of`,`shop_id`),
  ADD KEY `childpresentId` (`pchild`),
  ADD KEY `pchild` (`pchild`);

--
-- Indeks for tabel `present_description`
--
ALTER TABLE `present_description`
  ADD PRIMARY KEY (`id`,`present_id`),
  ADD UNIQUE KEY `present_language` (`present_id`,`language_id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indeks for tabel `present_log`
--
ALTER TABLE `present_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present_media`
--
ALTER TABLE `present_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present_id` (`present_id`);

--
-- Indeks for tabel `present_model`
--
ALTER TABLE `present_model`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`),
  ADD KEY `jobupdate` (`original_model_id`,`language_id`);

--
-- Indeks for tabel `present_model_0307`
--
ALTER TABLE `present_model_0307`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_0307_2`
--
ALTER TABLE `present_model_0307_2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_0707`
--
ALTER TABLE `present_model_0707`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_2406`
--
ALTER TABLE `present_model_2406`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_20250705`
--
ALTER TABLE `present_model_20250705`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_allan`
--
ALTER TABLE `present_model_allan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`),
  ADD KEY `jobupdate` (`original_model_id`,`language_id`);

--
-- Indeks for tabel `present_model_kim`
--
ALTER TABLE `present_model_kim`
  ADD PRIMARY KEY (`id`),
  ADD KEY `present` (`present_id`),
  ADD KEY `present_id` (`present_id`,`language_id`),
  ADD KEY `model_id_lang` (`model_id`,`language_id`),
  ADD KEY `sam` (`sampak_items`),
  ADD KEY `model_present_no` (`model_present_no`);

--
-- Indeks for tabel `present_model_options`
--
ALTER TABLE `present_model_options`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present_model_sampak`
--
ALTER TABLE `present_model_sampak`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `model_id` (`model_id`);
ALTER TABLE `present_model_sampak` ADD FULLTEXT KEY `sampak` (`item_list`);

--
-- Indeks for tabel `present_options`
--
ALTER TABLE `present_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_name` (`present_id`,`option_name`);

--
-- Indeks for tabel `present_reservation`
--
ALTER TABLE `present_reservation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_id` (`shop_id`,`present_id`,`model_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `pr_shopid` (`shop_id`);

--
-- Indeks for tabel `present_reservation_2807`
--
ALTER TABLE `present_reservation_2807`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_id` (`shop_id`,`present_id`,`model_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `pr_shopid` (`shop_id`);

--
-- Indeks for tabel `present_reservation_close_log`
--
ALTER TABLE `present_reservation_close_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present_reservation_forecast`
--
ALTER TABLE `present_reservation_forecast`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `present_reservation_log`
--
ALTER TABLE `present_reservation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indeks for tabel `present_test`
--
ALTER TABLE `present_test`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `copy_of` (`copy_of`),
  ADD KEY `deleted2` (`deleted`,`shop_id`,`copy_of`),
  ADD KEY `modified` (`modified_datetime`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `pt_image`
--
ALTER TABLE `pt_image`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `pwc`
--
ALTER TABLE `pwc`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `receipt_additions`
--
ALTER TABLE `receipt_additions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_lang` (`company_id`,`language`),
  ADD KEY `idx_shop_lang` (`shop_id`,`language`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_company_shop_lang_active` (`company_id`,`shop_id`,`language`,`active`);

--
-- Indeks for tabel `receipt_custom_part`
--
ALTER TABLE `receipt_custom_part`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `receipt_custom_text`
--
ALTER TABLE `receipt_custom_text`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `reservation_group`
--
ALTER TABLE `reservation_group`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `rm_data`
--
ALTER TABLE `rm_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`,`is_exceeded`,`is_exceeded_forecast`) USING BTREE;

--
-- Indeks for tabel `rm_job`
--
ALTER TABLE `rm_job`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `rm_shop_data`
--
ALTER TABLE `rm_shop_data`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indeks for tabel `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`,`user_type`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indeks for tabel `salesperson_shop`
--
ALTER TABLE `salesperson_shop`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shipment`
--
ALTER TABLE `shipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipment_companyorder_id` (`companyorder_id`),
  ADD KEY `shipment_from_certificate_no` (`from_certificate_no`),
  ADD KEY `shipment_shipment_type` (`shipment_type`),
  ADD KEY `shipment_shipment_state` (`shipment_state`);

--
-- Indeks for tabel `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link` (`link`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `expire` (`end_date`),
  ADD KEY `name` (`name`);

--
-- Indeks for tabel `shop_0408`
--
ALTER TABLE `shop_0408`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link` (`link`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `expire` (`end_date`),
  ADD KEY `name` (`name`);

--
-- Indeks for tabel `shop_1706`
--
ALTER TABLE `shop_1706`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `link` (`link`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `expire` (`end_date`),
  ADD KEY `name` (`name`);

--
-- Indeks for tabel `shop_address`
--
ALTER TABLE `shop_address`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_address_0307`
--
ALTER TABLE `shop_address_0307`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_approval`
--
ALTER TABLE `shop_approval`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_attribute`
--
ALTER TABLE `shop_attribute`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_name` (`shop_id`,`name`),
  ADD KEY `index` (`index`);

--
-- Indeks for tabel `shop_block_message`
--
ALTER TABLE `shop_block_message`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_board`
--
ALTER TABLE `shop_board`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fane` (`fane`,`fk_shop`),
  ADD KEY `fane_ansvar` (`valgshopansvarlig`,`fane`),
  ADD KEY `active` (`active`),
  ADD KEY `fk_shop` (`fk_shop`);

--
-- Indeks for tabel `shop_company_return_adress`
--
ALTER TABLE `shop_company_return_adress`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_data_types`
--
ALTER TABLE `shop_data_types`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_delivery`
--
ALTER TABLE `shop_delivery`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_description`
--
ALTER TABLE `shop_description`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniqe` (`language_id`,`shop_id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_documents`
--
ALTER TABLE `shop_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shop_id` (`shop_id`),
  ADD KEY `idx_document_type` (`document_type`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `idx_shop_type_deleted` (`shop_id`,`document_type`,`deleted`);

--
-- Indeks for tabel `shop_invoice`
--
ALTER TABLE `shop_invoice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`);

--
-- Indeks for tabel `shop_invoice_2`
--
ALTER TABLE `shop_invoice_2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_loan`
--
ALTER TABLE `shop_loan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_metadata`
--
ALTER TABLE `shop_metadata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_metadata_1906`
--
ALTER TABLE `shop_metadata_1906`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_metadata_2006_2`
--
ALTER TABLE `shop_metadata_2006_2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_metadata_2306`
--
ALTER TABLE `shop_metadata_2306`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_metadata_2506`
--
ALTER TABLE `shop_metadata_2506`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_shop_id` (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_metadata_versions`
--
ALTER TABLE `shop_metadata_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `version_number` (`version_number`),
  ADD KEY `created_date` (`created_date`);

--
-- Indeks for tabel `shop_order`
--
ALTER TABLE `shop_order`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_present`
--
ALTER TABLE `shop_present`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_present` (`shop_id`,`present_id`),
  ADD KEY `shop` (`shop_id`),
  ADD KEY `present` (`present_id`);

--
-- Indeks for tabel `shop_present_0906`
--
ALTER TABLE `shop_present_0906`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_present` (`shop_id`,`present_id`),
  ADD KEY `shop` (`shop_id`),
  ADD KEY `present` (`present_id`);

--
-- Indeks for tabel `shop_present_category`
--
ALTER TABLE `shop_present_category`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_present_company_rules`
--
ALTER TABLE `shop_present_company_rules`
  ADD UNIQUE KEY `unique_present_company` (`company_id`,`present_id`,`model_id`) USING BTREE,
  ADD KEY `present_id` (`present_id`);

--
-- Indeks for tabel `shop_present_rules`
--
ALTER TABLE `shop_present_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index_present_card` (`present_id`,`card_id`) USING BTREE,
  ADD KEY `index_card_id` (`id`);

--
-- Indeks for tabel `shop_report`
--
ALTER TABLE `shop_report`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop_id` (`shop_id`);

--
-- Indeks for tabel `shop_responsible`
--
ALTER TABLE `shop_responsible`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_statuses`
--
ALTER TABLE `shop_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_user`
--
ALTER TABLE `shop_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_user` (`company_id`,`username`,`password`),
  ADD UNIQUE KEY `shop_id_2` (`shop_id`,`username`,`password`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `shoopid,demo` (`shop_id`,`is_demo`),
  ADD KEY `username` (`username`),
  ADD KEY `blockeddevl` (`blocked`,`is_delivery`,`delivery_printed`),
  ADD KEY `i_token` (`token`),
  ADD KEY `index_order_id` (`company_order_id`),
  ADD KEY `company_id` (`company_id`,`replacement_id`) USING BTREE,
  ADD KEY `replace` (`id`,`replacement_id`) USING BTREE;

--
-- Indeks for tabel `shop_user_0207`
--
ALTER TABLE `shop_user_0207`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_user` (`company_id`,`username`,`password`),
  ADD UNIQUE KEY `shop_id_2` (`shop_id`,`username`,`password`),
  ADD KEY `shop_id` (`shop_id`),
  ADD KEY `shoopid,demo` (`shop_id`,`is_demo`),
  ADD KEY `username` (`username`),
  ADD KEY `blockeddevl` (`blocked`,`is_delivery`,`delivery_printed`),
  ADD KEY `i_token` (`token`),
  ADD KEY `index_order_id` (`company_order_id`),
  ADD KEY `company_id` (`company_id`,`replacement_id`) USING BTREE,
  ADD KEY `replace` (`id`,`replacement_id`) USING BTREE;

--
-- Indeks for tabel `shop_user_autoselect`
--
ALTER TABLE `shop_user_autoselect`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `shop_user_log`
--
ALTER TABLE `shop_user_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `sms_grp`
--
ALTER TABLE `sms_grp`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `sms_jobs`
--
ALTER TABLE `sms_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `sms_log`
--
ALTER TABLE `sms_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `sms_queue`
--
ALTER TABLE `sms_queue`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asdf` (`send_tlf`),
  ADD KEY `index_job` (`is_send`,`send_tlf`);

--
-- Indeks for tabel `sms_unsubscribe`
--
ALTER TABLE `sms_unsubscribe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_tlf` (`tlf`),
  ADD KEY `sdaf` (`tlf`);

--
-- Indeks for tabel `sms_unsubscribe_mail`
--
ALTER TABLE `sms_unsubscribe_mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dasf` (`tlf`);

--
-- Indeks for tabel `sms_user`
--
ALTER TABLE `sms_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_tfl` (`tlf`,`active`) USING BTREE,
  ADD KEY `index_val` (`tlf`);

--
-- Indeks for tabel `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `system_log`
--
ALTER TABLE `system_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `committed` (`committed`),
  ADD KEY `date` (`created_datetime`),
  ADD KEY `controller` (`controller`),
  ADD KEY `aontroller_action` (`controller`,`action`,`created_datetime`) USING BTREE;

--
-- Indeks for tabel `system_log_pdf_download`
--
ALTER TABLE `system_log_pdf_download`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `system_session`
--
ALTER TABLE `system_session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session` (`session`);

--
-- Indeks for tabel `system_user`
--
ALTER TABLE `system_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `all` (`username`,`active`,`deleted`),
  ADD KEY `token` (`token`);

--
-- Indeks for tabel `system_user_activity`
--
ALTER TABLE `system_user_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date` (`created`);

--
-- Indeks for tabel `system_user_device`
--
ALTER TABLE `system_user_device`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `tempmail`
--
ALTER TABLE `tempmail`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `tempmail_log`
--
ALTER TABLE `tempmail_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `temp_no_budget`
--
ALTER TABLE `temp_no_budget`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `unsub`
--
ALTER TABLE `unsub`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mail` (`mail`);

--
-- Indeks for tabel `user_attribute`
--
ALTER TABLE `user_attribute`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_attribute` (`shopuser_id`,`attribute_id`),
  ADD KEY `shop_attribute` (`shopuser_id`,`attribute_id`),
  ADD KEY `attval` (`attribute_value`),
  ADD KEY `sog_shop_id` (`shop_id`);

--
-- Indeks for tabel `user_attribute_0307`
--
ALTER TABLE `user_attribute_0307`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_attribute` (`shopuser_id`,`attribute_id`),
  ADD KEY `shop_attribute` (`shopuser_id`,`attribute_id`),
  ADD KEY `attval` (`attribute_value`),
  ADD KEY `sog_shop_id` (`shop_id`);

--
-- Indeks for tabel `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `user_tab_permission`
--
ALTER TABLE `user_tab_permission`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `varenrcache`
--
ALTER TABLE `varenrcache`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `vlager`
--
ALTER TABLE `vlager`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `vlager_incoming`
--
ALTER TABLE `vlager_incoming`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `vlager_incoming_line`
--
ALTER TABLE `vlager_incoming_line`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `vlager_item`
--
ALTER TABLE `vlager_item`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `vlager_item_log`
--
ALTER TABLE `vlager_item_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_id` (`voucher`),
  ADD UNIQUE KEY `shop_user` (`voucher`);

--
-- Indeks for tabel `voucher_quick`
--
ALTER TABLE `voucher_quick`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `warehouse_files`
--
ALTER TABLE `warehouse_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shop` (`shop_id`,`active`),
  ADD KEY `token` (`token`);

--
-- Indeks for tabel `warehouse_settings`
--
ALTER TABLE `warehouse_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks for tabel `weborderlog`
--
ALTER TABLE `weborderlog`
  ADD PRIMARY KEY (`id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `accesstoken`
--
ALTER TABLE `accesstoken`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `actionlog`
--
ALTER TABLE `actionlog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `airfryer`
--
ALTER TABLE `airfryer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `app_log`
--
ALTER TABLE `app_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `app_users`
--
ALTER TABLE `app_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `blockmessage`
--
ALTER TABLE `blockmessage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `cardshop_expiredate`
--
ALTER TABLE `cardshop_expiredate`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `cardshop_freight`
--
ALTER TABLE `cardshop_freight`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `cardshop_freight_09072025`
--
ALTER TABLE `cardshop_freight_09072025`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `cardshop_settings`
--
ALTER TABLE `cardshop_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company`
--
ALTER TABLE `company`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_2306`
--
ALTER TABLE `company_2306`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_notes`
--
ALTER TABLE `company_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_order`
--
ALTER TABLE `company_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_order_history`
--
ALTER TABLE `company_order_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_order_item`
--
ALTER TABLE `company_order_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_shipping_cost`
--
ALTER TABLE `company_shipping_cost`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_shipping_cost_040825`
--
ALTER TABLE `company_shipping_cost_040825`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `company_shop`
--
ALTER TABLE `company_shop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `cronlog`
--
ALTER TABLE `cronlog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `debug_log`
--
ALTER TABLE `debug_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `dsvstatus`
--
ALTER TABLE `dsvstatus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `dsvstatus_log`
--
ALTER TABLE `dsvstatus_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `dsv_input`
--
ALTER TABLE `dsv_input`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `early_present`
--
ALTER TABLE `early_present`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `expire_date`
--
ALTER TABLE `expire_date`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `ftp_download`
--
ALTER TABLE `ftp_download`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `ftp_queue`
--
ALTER TABLE `ftp_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `ftp_server`
--
ALTER TABLE `ftp_server`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `gaveklubben`
--
ALTER TABLE `gaveklubben`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `gaveklubben_2022`
--
ALTER TABLE `gaveklubben_2022`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `gift_certificate`
--
ALTER TABLE `gift_certificate`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `homerunner_log`
--
ALTER TABLE `homerunner_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `homerunner_webhook`
--
ALTER TABLE `homerunner_webhook`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `kontainer_queue`
--
ALTER TABLE `kontainer_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `kontainer_sync_job`
--
ALTER TABLE `kontainer_sync_job`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `language`
--
ALTER TABLE `language`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `lookupdata`
--
ALTER TABLE `lookupdata`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `magento_pim_category_index`
--
ALTER TABLE `magento_pim_category_index`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `magento_pim_suppliers_index`
--
ALTER TABLE `magento_pim_suppliers_index`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `magento_stock_change`
--
ALTER TABLE `magento_stock_change`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `magento_stock_total`
--
ALTER TABLE `magento_stock_total`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `magento_vip_stock`
--
ALTER TABLE `magento_vip_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_event`
--
ALTER TABLE `mail_event`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_library`
--
ALTER TABLE `mail_library`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_queue`
--
ALTER TABLE `mail_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_queue_block`
--
ALTER TABLE `mail_queue_block`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_server`
--
ALTER TABLE `mail_server`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_template`
--
ALTER TABLE `mail_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `mail_track`
--
ALTER TABLE `mail_track`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `media`
--
ALTER TABLE `media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `monitor_itemno`
--
ALTER TABLE `monitor_itemno`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_bomitem`
--
ALTER TABLE `navision_bomitem`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_call_log`
--
ALTER TABLE `navision_call_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_choice_doc`
--
ALTER TABLE `navision_choice_doc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_company_no`
--
ALTER TABLE `navision_company_no`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_item`
--
ALTER TABLE `navision_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_itemrename`
--
ALTER TABLE `navision_itemrename`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_location`
--
ALTER TABLE `navision_location`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_order_doc`
--
ALTER TABLE `navision_order_doc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_order_line`
--
ALTER TABLE `navision_order_line`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_reservation_done`
--
ALTER TABLE `navision_reservation_done`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_reservation_done_item`
--
ALTER TABLE `navision_reservation_done_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_reservation_log`
--
ALTER TABLE `navision_reservation_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_salesperson`
--
ALTER TABLE `navision_salesperson`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_salesprice`
--
ALTER TABLE `navision_salesprice`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_stock_total`
--
ALTER TABLE `navision_stock_total`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_vs_state`
--
ALTER TABLE `navision_vs_state`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `navision_vs_version`
--
ALTER TABLE `navision_vs_version`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `number_series`
--
ALTER TABLE `number_series`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order`
--
ALTER TABLE `order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_0307`
--
ALTER TABLE `order_0307`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_attribute`
--
ALTER TABLE `order_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_history_attribute`
--
ALTER TABLE `order_history_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_present_complaint`
--
ALTER TABLE `order_present_complaint`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_present_entry`
--
ALTER TABLE `order_present_entry`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `order_test`
--
ALTER TABLE `order_test`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `paper_log`
--
ALTER TABLE `paper_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `paper_order`
--
ALTER TABLE `paper_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `paper_user_attribute`
--
ALTER TABLE `paper_user_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pim_logo`
--
ALTER TABLE `pim_logo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pim_logo_log`
--
ALTER TABLE `pim_logo_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pim_nav_itemnr_sync`
--
ALTER TABLE `pim_nav_itemnr_sync`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pim_sync`
--
ALTER TABLE `pim_sync`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pim_sync_queue`
--
ALTER TABLE `pim_sync_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `postnord_orderreport`
--
ALTER TABLE `postnord_orderreport`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `postnord_varenr`
--
ALTER TABLE `postnord_varenr`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `postnord_varenrlog`
--
ALTER TABLE `postnord_varenrlog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present`
--
ALTER TABLE `present`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_group`
--
ALTER TABLE `presentation_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_group_0906`
--
ALTER TABLE `presentation_group_0906`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_sale_log`
--
ALTER TABLE `presentation_sale_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_sale_pdf`
--
ALTER TABLE `presentation_sale_pdf`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_sale_present`
--
ALTER TABLE `presentation_sale_present`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_sale_profile`
--
ALTER TABLE `presentation_sale_profile`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `presentation_sale_profile_2105`
--
ALTER TABLE `presentation_sale_profile_2105`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_0307_2`
--
ALTER TABLE `present_0307_2`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_0906`
--
ALTER TABLE `present_0906`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_2406`
--
ALTER TABLE `present_2406`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_20250705`
--
ALTER TABLE `present_20250705`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_description`
--
ALTER TABLE `present_description`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_log`
--
ALTER TABLE `present_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_media`
--
ALTER TABLE `present_media`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model`
--
ALTER TABLE `present_model`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_0307`
--
ALTER TABLE `present_model_0307`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_0307_2`
--
ALTER TABLE `present_model_0307_2`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_0707`
--
ALTER TABLE `present_model_0707`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_2406`
--
ALTER TABLE `present_model_2406`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_20250705`
--
ALTER TABLE `present_model_20250705`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_allan`
--
ALTER TABLE `present_model_allan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_kim`
--
ALTER TABLE `present_model_kim`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_options`
--
ALTER TABLE `present_model_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_model_sampak`
--
ALTER TABLE `present_model_sampak`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_options`
--
ALTER TABLE `present_options`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation`
--
ALTER TABLE `present_reservation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation_2807`
--
ALTER TABLE `present_reservation_2807`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation_close_log`
--
ALTER TABLE `present_reservation_close_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation_forecast`
--
ALTER TABLE `present_reservation_forecast`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation_log`
--
ALTER TABLE `present_reservation_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `present_test`
--
ALTER TABLE `present_test`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pt_image`
--
ALTER TABLE `pt_image`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `pwc`
--
ALTER TABLE `pwc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `receipt_additions`
--
ALTER TABLE `receipt_additions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `receipt_custom_part`
--
ALTER TABLE `receipt_custom_part`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `receipt_custom_text`
--
ALTER TABLE `receipt_custom_text`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `reservation_group`
--
ALTER TABLE `reservation_group`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `rm_data`
--
ALTER TABLE `rm_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `rm_job`
--
ALTER TABLE `rm_job`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `rm_shop_data`
--
ALTER TABLE `rm_shop_data`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `salesperson_shop`
--
ALTER TABLE `salesperson_shop`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shipment`
--
ALTER TABLE `shipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_0408`
--
ALTER TABLE `shop_0408`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_1706`
--
ALTER TABLE `shop_1706`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_address`
--
ALTER TABLE `shop_address`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_address_0307`
--
ALTER TABLE `shop_address_0307`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_approval`
--
ALTER TABLE `shop_approval`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_attribute`
--
ALTER TABLE `shop_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_block_message`
--
ALTER TABLE `shop_block_message`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_board`
--
ALTER TABLE `shop_board`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_company_return_adress`
--
ALTER TABLE `shop_company_return_adress`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_data_types`
--
ALTER TABLE `shop_data_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_delivery`
--
ALTER TABLE `shop_delivery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_description`
--
ALTER TABLE `shop_description`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_documents`
--
ALTER TABLE `shop_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_invoice`
--
ALTER TABLE `shop_invoice`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_invoice_2`
--
ALTER TABLE `shop_invoice_2`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_loan`
--
ALTER TABLE `shop_loan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata`
--
ALTER TABLE `shop_metadata`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata_1906`
--
ALTER TABLE `shop_metadata_1906`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata_2006_2`
--
ALTER TABLE `shop_metadata_2006_2`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata_2306`
--
ALTER TABLE `shop_metadata_2306`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata_2506`
--
ALTER TABLE `shop_metadata_2506`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_metadata_versions`
--
ALTER TABLE `shop_metadata_versions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_order`
--
ALTER TABLE `shop_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_present`
--
ALTER TABLE `shop_present`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_present_0906`
--
ALTER TABLE `shop_present_0906`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_present_category`
--
ALTER TABLE `shop_present_category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_present_rules`
--
ALTER TABLE `shop_present_rules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_report`
--
ALTER TABLE `shop_report`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_responsible`
--
ALTER TABLE `shop_responsible`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_statuses`
--
ALTER TABLE `shop_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_user`
--
ALTER TABLE `shop_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_user_0207`
--
ALTER TABLE `shop_user_0207`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_user_autoselect`
--
ALTER TABLE `shop_user_autoselect`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `shop_user_log`
--
ALTER TABLE `shop_user_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_grp`
--
ALTER TABLE `sms_grp`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_jobs`
--
ALTER TABLE `sms_jobs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_log`
--
ALTER TABLE `sms_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_queue`
--
ALTER TABLE `sms_queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_unsubscribe`
--
ALTER TABLE `sms_unsubscribe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_unsubscribe_mail`
--
ALTER TABLE `sms_unsubscribe_mail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `sms_user`
--
ALTER TABLE `sms_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system`
--
ALTER TABLE `system`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_log`
--
ALTER TABLE `system_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_log_pdf_download`
--
ALTER TABLE `system_log_pdf_download`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_session`
--
ALTER TABLE `system_session`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_user`
--
ALTER TABLE `system_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_user_activity`
--
ALTER TABLE `system_user_activity`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `system_user_device`
--
ALTER TABLE `system_user_device`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `tempmail`
--
ALTER TABLE `tempmail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `tempmail_log`
--
ALTER TABLE `tempmail_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `temp_no_budget`
--
ALTER TABLE `temp_no_budget`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `unsub`
--
ALTER TABLE `unsub`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `user_attribute`
--
ALTER TABLE `user_attribute`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `user_attribute_0307`
--
ALTER TABLE `user_attribute_0307`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `user_tab_permission`
--
ALTER TABLE `user_tab_permission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `varenrcache`
--
ALTER TABLE `varenrcache`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `vlager`
--
ALTER TABLE `vlager`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `vlager_incoming`
--
ALTER TABLE `vlager_incoming`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `vlager_incoming_line`
--
ALTER TABLE `vlager_incoming_line`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `vlager_item`
--
ALTER TABLE `vlager_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `vlager_item_log`
--
ALTER TABLE `vlager_item_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `voucher_quick`
--
ALTER TABLE `voucher_quick`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `warehouse_files`
--
ALTER TABLE `warehouse_files`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `warehouse_settings`
--
ALTER TABLE `warehouse_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Tilføj AUTO_INCREMENT i tabel `weborderlog`
--
ALTER TABLE `weborderlog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `permission_user`
--
ALTER TABLE `permission_user`
  ADD CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
