-- phpMyAdmin SQL Dump
-- version 5.1.3-2.el8.remi
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 05. 08 2025 kl. 13:44:24
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

--
-- Begrænsninger for dumpede tabeller
--

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
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `order`
--
ALTER TABLE `order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
