-- phpMyAdmin SQL Dump
-- version 5.1.3-2.el8.remi
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 05. 08 2025 kl. 12:16:31
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

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `present_reservation`
--
ALTER TABLE `present_reservation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_id` (`shop_id`,`present_id`,`model_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `pr_shopid` (`shop_id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `present_reservation`
--
ALTER TABLE `present_reservation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
