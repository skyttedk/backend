-- phpMyAdmin SQL Dump
-- version 5.1.3-2.el8.remi
-- https://www.phpmyadmin.net/
--
-- Vært: localhost
-- Genereringstid: 05. 08 2025 kl. 12:41:54
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

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `shop_present`
--
ALTER TABLE `shop_present`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shop_present` (`shop_id`,`present_id`),
  ADD KEY `shop` (`shop_id`),
  ADD KEY `present` (`present_id`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `shop_present`
--
ALTER TABLE `shop_present`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
