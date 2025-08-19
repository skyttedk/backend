-- phpMyAdmin SQL Dump
-- version 5.1.3-2.el8.remi
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 04. 08 2025 kl. 11:07:32
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

--
-- Begrænsninger for dumpede tabeller
--

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
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
