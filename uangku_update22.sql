-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Des 2023 pada 04.23
-- Versi server: 10.4.25-MariaDB
-- Versi PHP: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uangku`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `camp`
--

CREATE TABLE `camp` (
  `id` bigint(225) UNSIGNED NOT NULL,
  `user_id` bigint(225) UNSIGNED NOT NULL,
  `id_transaksi` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `camp_ke` varchar(300) DEFAULT NULL,
  `uang_masuk` varchar(300) DEFAULT NULL,
  `lain_lain` varchar(300) DEFAULT NULL,
  `total_uang_masuk` varchar(300) DEFAULT NULL,
  `gaji_trainer` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama` text DEFAULT NULL,
  `gaji_trainer1` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama1` text DEFAULT NULL,
  `gaji_trainer2` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama2` text DEFAULT NULL,
  `gaji_trainer3` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama3` text DEFAULT NULL,
  `gaji_trainer4` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama4` text DEFAULT NULL,
  `gaji_trainer5` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama5` text DEFAULT NULL,
  `gaji_trainer6` varchar(300) DEFAULT NULL,
  `gaji_trainer_nama6` text DEFAULT NULL,
  `total_gaji_trainer` varchar(300) DEFAULT NULL,
  `gaji_team` varchar(300) DEFAULT NULL,
  `gaji_team_nama` text DEFAULT NULL,
  `gaji_team1` varchar(300) DEFAULT NULL,
  `gaji_team_nama1` text DEFAULT NULL,
  `gaji_team2` varchar(300) DEFAULT NULL,
  `gaji_team_nama2` text DEFAULT NULL,
  `gaji_team3` varchar(300) DEFAULT NULL,
  `gaji_team_nama3` text DEFAULT NULL,
  `gaji_team4` varchar(300) DEFAULT NULL,
  `gaji_team_nama4` text DEFAULT NULL,
  `gaji_team5` varchar(300) DEFAULT NULL,
  `gaji_team_nama5` text DEFAULT NULL,
  `gaji_team6` varchar(300) DEFAULT NULL,
  `gaji_team_nama6` text DEFAULT NULL,
  `gaji_team7` varchar(300) DEFAULT NULL,
  `gaji_team_nama7` text DEFAULT NULL,
  `gaji_team8` varchar(300) DEFAULT NULL,
  `gaji_team_nama8` text DEFAULT NULL,
  `gaji_team9` varchar(300) DEFAULT NULL,
  `gaji_team_nama9` text DEFAULT NULL,
  `gaji_team10` varchar(300) DEFAULT NULL,
  `gaji_team_nama10` text DEFAULT NULL,
  `total_gaji_team` varchar(300) DEFAULT NULL,
  `team_cabang` varchar(300) DEFAULT NULL,
  `booknote` varchar(300) DEFAULT NULL,
  `grammarly` varchar(300) DEFAULT NULL,
  `peserta` text DEFAULT NULL,
  `tiket_trainer` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama` text DEFAULT NULL,
  `tiket_trainer1` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama1` text DEFAULT NULL,
  `tiket_trainer2` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama2` text DEFAULT NULL,
  `tiket_trainer3` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama3` text DEFAULT NULL,
  `tiket_trainer4` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama4` text DEFAULT NULL,
  `tiket_trainer5` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama5` text DEFAULT NULL,
  `tiket_trainer6` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama6` text DEFAULT NULL,
  `tiket_trainer7` varchar(300) DEFAULT NULL,
  `tiket_trainer_nama7` text DEFAULT NULL,
  `total_tiket_trainer_berangkat` text DEFAULT NULL,
  `tiket_trainer_pulang` text DEFAULT NULL,
  `tiket_trainer_pulang_nama` text DEFAULT NULL,
  `tiket_trainer_pulang1` text DEFAULT NULL,
  `tiket_trainer_pulang_nama1` text DEFAULT NULL,
  `tiket_trainer_pulang2` text DEFAULT NULL,
  `tiket_trainer_pulang_nama2` text DEFAULT NULL,
  `tiket_trainer_pulang3` text DEFAULT NULL,
  `tiket_trainer_pulang_nama3` text DEFAULT NULL,
  `tiket_trainer_pulang4` text DEFAULT NULL,
  `tiket_trainer_pulang_nama4` text DEFAULT NULL,
  `tiket_trainer_pulang5` text DEFAULT NULL,
  `tiket_trainer_pulang_nama5` text DEFAULT NULL,
  `tiket_trainer_pulang6` text DEFAULT NULL,
  `tiket_trainer_pulang_nama6` text DEFAULT NULL,
  `tiket_trainer_pulang7` text DEFAULT NULL,
  `tiket_trainer_pulang_nama7` text DEFAULT NULL,
  `total_tiket_trainer_pulang` text DEFAULT NULL,
  `tiket_team` varchar(300) DEFAULT NULL,
  `tiket_team_nama` text DEFAULT NULL,
  `tiket_team1` varchar(300) DEFAULT NULL,
  `tiket_team_nama1` text DEFAULT NULL,
  `tiket_team2` varchar(300) DEFAULT NULL,
  `tiket_team_nama2` text DEFAULT NULL,
  `tiket_team3` varchar(300) DEFAULT NULL,
  `tiket_team_nama3` text DEFAULT NULL,
  `tiket_team4` varchar(300) DEFAULT NULL,
  `tiket_team_nama4` text DEFAULT NULL,
  `tiket_team5` varchar(300) DEFAULT NULL,
  `tiket_team_nama5` text DEFAULT NULL,
  `tiket_team6` varchar(300) DEFAULT NULL,
  `tiket_team_nama6` text DEFAULT NULL,
  `tiket_team7` varchar(300) DEFAULT NULL,
  `tiket_team_nama7` text DEFAULT NULL,
  `total_tiket_team_berangkat` text DEFAULT NULL,
  `tiket_team_pulang` text DEFAULT NULL,
  `tiket_team_pulang_nama` text DEFAULT NULL,
  `tiket_team_pulang1` text DEFAULT NULL,
  `tiket_team_pulang_nama1` text DEFAULT NULL,
  `tiket_team_pulang2` text DEFAULT NULL,
  `tiket_team_pulang_nama2` text DEFAULT NULL,
  `tiket_team_pulang3` text DEFAULT NULL,
  `tiket_team_pulang_nama3` text DEFAULT NULL,
  `tiket_team_pulang4` text DEFAULT NULL,
  `tiket_team_pulang_nama4` text DEFAULT NULL,
  `tiket_team_pulang5` text DEFAULT NULL,
  `tiket_team_pulang_nama5` text DEFAULT NULL,
  `tiket_team_pulang6` text DEFAULT NULL,
  `tiket_team_pulang_nama6` text DEFAULT NULL,
  `tiket_team_pulang7` text DEFAULT NULL,
  `tiket_team_pulang_nama7` text DEFAULT NULL,
  `total_tiket_team_pulang` text DEFAULT NULL,
  `hotel` varchar(300) DEFAULT NULL,
  `marketing` varchar(300) DEFAULT NULL,
  `konsumsi_tambahan` varchar(300) DEFAULT NULL,
  `lainnya` varchar(300) DEFAULT NULL,
  `total` varchar(300) DEFAULT NULL,
  `keuntungan` varchar(300) DEFAULT NULL,
  `persentase_keuntungan` varchar(300) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `tanggal_akhir` datetime DEFAULT NULL,
  `status` varchar(300) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `camp`
--

INSERT INTO `camp` (`id`, `user_id`, `id_transaksi`, `title`, `camp_ke`, `uang_masuk`, `lain_lain`, `total_uang_masuk`, `gaji_trainer`, `gaji_trainer_nama`, `gaji_trainer1`, `gaji_trainer_nama1`, `gaji_trainer2`, `gaji_trainer_nama2`, `gaji_trainer3`, `gaji_trainer_nama3`, `gaji_trainer4`, `gaji_trainer_nama4`, `gaji_trainer5`, `gaji_trainer_nama5`, `gaji_trainer6`, `gaji_trainer_nama6`, `total_gaji_trainer`, `gaji_team`, `gaji_team_nama`, `gaji_team1`, `gaji_team_nama1`, `gaji_team2`, `gaji_team_nama2`, `gaji_team3`, `gaji_team_nama3`, `gaji_team4`, `gaji_team_nama4`, `gaji_team5`, `gaji_team_nama5`, `gaji_team6`, `gaji_team_nama6`, `gaji_team7`, `gaji_team_nama7`, `gaji_team8`, `gaji_team_nama8`, `gaji_team9`, `gaji_team_nama9`, `gaji_team10`, `gaji_team_nama10`, `total_gaji_team`, `team_cabang`, `booknote`, `grammarly`, `peserta`, `tiket_trainer`, `tiket_trainer_nama`, `tiket_trainer1`, `tiket_trainer_nama1`, `tiket_trainer2`, `tiket_trainer_nama2`, `tiket_trainer3`, `tiket_trainer_nama3`, `tiket_trainer4`, `tiket_trainer_nama4`, `tiket_trainer5`, `tiket_trainer_nama5`, `tiket_trainer6`, `tiket_trainer_nama6`, `tiket_trainer7`, `tiket_trainer_nama7`, `total_tiket_trainer_berangkat`, `tiket_trainer_pulang`, `tiket_trainer_pulang_nama`, `tiket_trainer_pulang1`, `tiket_trainer_pulang_nama1`, `tiket_trainer_pulang2`, `tiket_trainer_pulang_nama2`, `tiket_trainer_pulang3`, `tiket_trainer_pulang_nama3`, `tiket_trainer_pulang4`, `tiket_trainer_pulang_nama4`, `tiket_trainer_pulang5`, `tiket_trainer_pulang_nama5`, `tiket_trainer_pulang6`, `tiket_trainer_pulang_nama6`, `tiket_trainer_pulang7`, `tiket_trainer_pulang_nama7`, `total_tiket_trainer_pulang`, `tiket_team`, `tiket_team_nama`, `tiket_team1`, `tiket_team_nama1`, `tiket_team2`, `tiket_team_nama2`, `tiket_team3`, `tiket_team_nama3`, `tiket_team4`, `tiket_team_nama4`, `tiket_team5`, `tiket_team_nama5`, `tiket_team6`, `tiket_team_nama6`, `tiket_team7`, `tiket_team_nama7`, `total_tiket_team_berangkat`, `tiket_team_pulang`, `tiket_team_pulang_nama`, `tiket_team_pulang1`, `tiket_team_pulang_nama1`, `tiket_team_pulang2`, `tiket_team_pulang_nama2`, `tiket_team_pulang3`, `tiket_team_pulang_nama3`, `tiket_team_pulang4`, `tiket_team_pulang_nama4`, `tiket_team_pulang5`, `tiket_team_pulang_nama5`, `tiket_team_pulang6`, `tiket_team_pulang_nama6`, `tiket_team_pulang7`, `tiket_team_pulang_nama7`, `total_tiket_team_pulang`, `hotel`, `marketing`, `konsumsi_tambahan`, `lainnya`, `total`, `keuntungan`, `persentase_keuntungan`, `tanggal`, `tanggal_akhir`, `status`, `note`, `created_at`, `updated_at`) VALUES
(3, 22, 'EIMTX', 'medan', '4', '10000000', '1000000', '11000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', '10000', '10000', NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, '0', '10000', '10910000', NULL, NULL, '2023-12-17 19:33:00', NULL, 'pending', '<p>dfsdfsd</p>', '2023-12-16 12:34:08', '2023-12-16 12:34:08'),
(4, 22, 'WWIVG', 'medan', '5', '10000000', '1000000', '11000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', '10000', '10000', NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, '0', '10000', '10910000', NULL, NULL, '2023-12-16 19:37:00', '2023-12-17 19:37:00', 'pending', '<p>fdfsdfdsf</p>', '2023-12-16 12:38:11', '2023-12-16 12:38:11'),
(5, 22, '82XMH', 'medan', '6', '28000000', '0', '28000000', '3000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '962500', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13760000', '350000', '385000', NULL, '4533268', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1298658', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, '0', '480000', '24769426', '3230574', NULL, '2023-12-16 19:48:00', '2023-12-17 19:48:00', 'pending', '<p>jhjhk</p>', '2023-12-16 12:50:13', '2023-12-16 12:50:13'),
(6, 22, 'PIFGQ', 'jogja', '7', '30000000', '10000000', '40000000', '5000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '15000000', '500000', '500000', NULL, '5000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2000000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '500000', NULL, '500000', '500000', '30500000', '9500000', '23.75', '2023-12-01 19:58:00', '2023-12-05 19:58:00', 'terbayar', '<p>dsfdsfsdfs123456789</p>', '2023-12-16 12:59:34', '2023-12-19 11:15:37'),
(7, 22, 'X9VOO', 'bandung', '3', '40000000', '10000000', '50000000', '1000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1500000', '500000', '500000', NULL, '2000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, '500000', '500000', '13500000', '36500000', '73', '2023-11-01 20:31:00', '2023-12-09 20:31:00', 'pending', '<p>gygyyu</p>', '2023-12-19 13:33:04', '2023-12-19 13:34:08'),
(8, 22, 'ZHMQB', 'jaktim', '1', '40000000', '10000000', '50000000', '5000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '15000000', '500000', '500000', NULL, '5000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2000000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', '5000000', '500000', '500000', '31000000', '19000000', '38', '2023-12-28 08:08:00', '2023-12-30 08:08:00', 'pending', '<p>dfasdasda</p>', '2023-12-20 01:09:15', '2023-12-20 01:09:15'),
(10, 22, '9M7SV', 'jogja', '10', '30000000', '20000000', '50000000', '100000', 'sfddsfsdf', '100000', 'sdfsdfsdf', '100000', 'dfsffsfs', '100000', 'sdfsdfsdf', '100000', 'sdfsdfsdf', '100000', 'asdasdasd', '100000', 'asdasdasdas', NULL, '10000', 'eqweqweqwe', '10000', 'eqweqweqweqwe', '10000', 'eqweqweqweqweqwe', '10000', 'eqwewqeqweqweqweqw', '10000', 'qwewqeqweqweqweq', '10000', 'eqw eqweqwedqw', '10000', 'ewqewqeqweddqdqwdq', '10000', 'qdqwdqdqwdqwdq', '10000', 'dqwdqwdqwdqwdq', '10000', 'qdqdqwdqwdqwdqwdq', '10000', 'dqwdqwdwqdqwd', NULL, '10000', '10000', '500000', NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', '5000000', '10000', '10000', '2370000', '47630000', '95.26', '2023-12-21 15:14:00', '2023-12-23 15:14:00', 'pending', '<p>asdasdas</p>', '2023-12-20 08:16:13', '2023-12-20 08:16:13'),
(12, 22, '3JSYC', 'jogja', '6', '30000000', '20000000', '50000000', '100000', 'dfgdfgdfg', '100000', 'gdrgrdgdrg', '100000', 'drgdrgdrgdr', '100000', 'gdrgdrgdrg', '100000', 'sdfsdfsdf', '100000', 'eqweqweqweqw', '0', NULL, '600000', '100000', 'dgdrgdrgdrgd', '100000', 'drgdrgrdgdrg', '100000', 'gdrgdrgdrgdg', '100000', 'fsefsefsefsefse', '100000', 'qwewqeqweqweqweq', '100000', 'eqw eqweqwedqw', '100000', 'ewqewqeqweddqdqwdq', '100000', 'qdqwdqdqwdqwdq', '0', NULL, '0', NULL, '0', NULL, '800000', '15000000', '500000', '500000', NULL, '2000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', '5000000', '500000', '500000', '22400000', '27600000', '55.2', '2023-12-21 15:35:00', '2023-12-24 15:36:00', 'terbayar', '<p>gdrgdrgdr</p>', '2023-12-20 08:37:07', '2023-12-20 12:33:36'),
(13, 22, 'S39FP', 'hogja', '1', '30000000', '20000000', '50000000', '10000', 'qweqweqw', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '10000', '962500', 'eqweqweqwe', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '962500', '10000', '10000', '500000', '2', '10000', 'sdfsdfsdfs', '10000', 'dfasdasdasd', '10000', 'dasdasdasda', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, '10000', 'sadasdasdas', '10000', 'adsadasda', '10000', 'adsadasdasda', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, '10000', 'jhjklhjklh', '10000', 'dfasdasdas', '10000', 'dadawdawd', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, '10000', 'dawdawda', '10000', 'dawdawda', '10000', 'dawdawdawd', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, NULL, '0', '5000000', '0', '0', '1612500', '48387500', '96.775', '2023-12-25 22:58:00', '2023-12-26 22:58:00', 'pending', NULL, '2023-12-25 16:00:15', '2023-12-25 16:00:15'),
(14, 22, 'PQ94W', 'yogya', '1', '30000000', '10000000', '40000000', '10000', 'qwe', '10000', 'qwe', '10000', 'qwe', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '30000', '10000', 'rty', '10000', 'rty', '10000', 'rty', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '30000', '15000000', '500000', '500000', '20', '1000000', 'asd', '1000000', 'asd', '1000000', 'asd', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '3000000', '1000000', 'zxc', '1000000', 'zxc', '1000000', 'zxc', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '3000000', '1000000', 'fgh', '1000000', 'fgh', '1000000', 'fgh', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '3000000', '1000000', 'vbn', '1000000', 'vbn', '1000000', 'vbn', '0', NULL, '0', NULL, '0', NULL, '0', NULL, '0', NULL, '3000000', '1000000', '4000000', '500000', '500000', '30060000', '9940000', '24.85', '2023-12-26 00:00:00', '2023-12-29 00:00:00', 'pending', NULL, '2023-12-26 12:43:58', '2023-12-26 15:20:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories_credit`
--

CREATE TABLE `categories_credit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories_credit`
--

INSERT INTO `categories_credit` (`id`, `user_id`, `kode`, `name`, `created_at`, `updated_at`) VALUES
(2, 22, 'C002', 'TEST CREDIT', '2023-09-18 08:05:13', '2023-09-18 08:05:13'),
(8, 22, 'CM002', 'CREDIT MANAGER', '2023-09-21 16:36:53', '2023-09-21 16:36:53'),
(10, 54, 'CU002', 'CREDIT USER', '2023-09-21 16:37:11', '2023-09-21 16:37:11'),
(11, 54, 'CU003', 'CREDIT USER 2', '2023-09-21 16:37:19', '2023-09-21 16:37:19'),
(12, 49, 'CK002', 'CREDIT KARYAWAN', '2023-10-06 08:45:59', '2023-10-06 08:45:59'),
(13, 49, 'CK003', 'CREDIT KARYAWAN2', '2023-10-06 10:25:59', '2023-10-06 10:25:59'),
(14, 22, 'CM003', 'CREDIT MANAGER2', '2023-10-06 10:26:29', '2023-10-06 10:26:29'),
(15, 22, 'TES KODE CREDIT', 'COBA KODE CREDT', '2023-10-13 08:38:00', '2023-10-13 08:38:00'),
(16, 49, '02 NYOBA KODE', 'COBA KODE', '2023-10-13 08:38:30', '2023-10-13 08:47:37'),
(17, 49, '22', 'SDADADAS', '2023-10-13 10:43:11', '2023-10-13 10:43:11'),
(18, 59, 'TRAINER01', 'TRAIBNER', '2023-10-24 10:57:10', '2023-10-24 10:57:10'),
(19, 59, 'TRAINER', 'TESS', '2023-10-24 10:59:27', '2023-10-24 10:59:27'),
(20, 59, 'TRAINE-01', 'BARU', '2023-10-24 11:02:26', '2023-10-24 11:02:26'),
(21, 59, 'AAAA-0001', 'CREDIT', '2023-10-24 12:05:13', '2023-10-24 12:05:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories_debit`
--

CREATE TABLE `categories_debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nama_barang` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_barang` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diskon` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stok` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories_debit`
--

INSERT INTO `categories_debit` (`id`, `user_id`, `kode`, `name`, `created_at`, `updated_at`, `nama_barang`, `harga_barang`, `diskon`, `stok`) VALUES
(1, 22, 'D002', 'TESS DEBIT', '2023-09-18 06:32:11', '2023-09-18 06:32:11', NULL, NULL, NULL, NULL),
(4, 22, 'D003', 'DEBIT', '2023-09-18 08:20:01', '2023-09-18 08:20:01', NULL, NULL, NULL, NULL),
(5, 22, 'D004', 'NYOBA DEBIT', '2023-09-18 08:20:53', '2023-09-18 08:20:53', NULL, NULL, NULL, NULL),
(13, 15, 'DA002', 'DEBIT ADMIN', '2023-09-21 16:34:14', '2023-09-21 16:34:14', NULL, NULL, NULL, NULL),
(14, 54, 'DU002', 'DEBIT USER', '2023-09-21 16:34:26', '2023-09-21 16:34:26', NULL, NULL, NULL, NULL),
(15, 15, 'DA003', 'DEBIT ADMIN 2', '2023-09-21 16:34:34', '2023-09-21 16:34:34', NULL, NULL, NULL, NULL),
(18, 15, 'DA004', 'DEBIT ADMIN3', '2023-09-21 16:45:47', '2023-09-21 16:45:47', NULL, NULL, NULL, NULL),
(19, 22, 'DM002', 'MANAGER', '2023-10-05 08:01:01', '2023-10-05 08:01:01', NULL, NULL, NULL, NULL),
(23, 49, 'DK002', 'DEBIT KARYWAN', '2023-10-06 08:49:37', '2023-10-06 08:49:37', NULL, NULL, NULL, NULL),
(24, 49, 'DK003', 'DEBIT KARYWAN 2', '2023-10-06 09:15:43', '2023-10-06 09:15:43', NULL, NULL, NULL, NULL),
(25, 22, 'DM003', 'DEBIT MANAGER 10', '2023-10-06 09:16:24', '2023-10-09 07:51:25', NULL, NULL, NULL, NULL),
(26, 22, 'DM004', 'WILDAN', '2023-10-09 07:39:27', '2023-10-09 07:39:27', NULL, NULL, NULL, NULL),
(27, 22, 'DM0056', 'CAMP JOGJA', '2023-10-09 07:52:02', '2023-10-18 09:26:40', NULL, NULL, NULL, NULL),
(29, 22, 'DU001', 'NYOBA KODE', '2023-10-13 05:56:44', '2023-10-13 05:56:44', NULL, NULL, NULL, NULL),
(31, 49, '021 NYOBA KODE', 'COBA KODE', '2023-10-13 07:27:51', '2023-10-13 09:22:57', NULL, NULL, NULL, NULL),
(32, 22, 'TESS 01 MANAGER', 'TES KODE  MANAG', '2023-10-13 08:37:25', '2023-10-13 08:37:25', NULL, NULL, NULL, NULL),
(34, 59, 'TRAINER', 'TRAINER', '2023-10-24 10:51:16', '2023-10-24 10:51:16', NULL, NULL, NULL, NULL),
(35, 59, 'TRAINER002', 'PROJECT', '2023-10-24 12:00:09', '2023-10-24 12:00:09', NULL, NULL, NULL, NULL),
(36, 22, 'AAA02', 'TESSS KATEGORI', '2023-10-31 04:52:33', '2023-10-31 04:52:33', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `company`
--

CREATE TABLE `company` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `email_company` varchar(200) DEFAULT NULL,
  `telp_company` varchar(200) DEFAULT NULL,
  `alamat_company` text DEFAULT NULL,
  `logo_company` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit`
--

CREATE TABLE `credit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_date` datetime NOT NULL,
  `gambar` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `credit`
--

INSERT INTO `credit` (`id`, `user_id`, `category_id`, `id_transaksi`, `nominal`, `description`, `credit_date`, `gambar`, `created_at`, `updated_at`) VALUES
(83, 24, 2, '2DM6O', 20000, 'asdasdasdas', '2023-09-18 12:00:00', '1695024334.jpg', '2023-09-18 08:05:34', '2023-09-18 08:05:34'),
(84, 24, 2, 'AZZL9', 200000, 'dsadasdasd', '2023-09-18 12:00:00', '1695024357.jpg', '2023-09-18 08:05:57', '2023-09-18 08:05:57'),
(85, 22, 2, '2Z8CL', 50000, 'fefsefsefsfs', '2023-09-21 12:00:00', '1695263213.jpg', '2023-09-21 02:26:40', '2023-09-21 02:26:53'),
(86, 22, 2, '53AK8', 100000, 'asdasdasdasda', '2023-09-21 12:00:00', '1695263301.jpg', '2023-09-21 02:28:21', '2023-09-21 02:28:21'),
(88, 22, 8, 'OZZR9', 100000, 'sdffsdfs', '2023-10-05 12:00:00', '1696493582.png', '2023-10-05 08:13:02', '2023-10-05 08:13:02'),
(89, 49, 12, NULL, 1000123, 'hgsajhda00000000000000000', '2023-10-06 12:00:00', NULL, '2023-10-06 10:27:30', '2023-10-06 10:49:39'),
(90, 24, 14, 'GN8II', 10123, 'asbdjbasdas1234560000000tesssssssss', '2023-10-06 12:00:00', NULL, '2023-10-06 10:34:40', '2023-10-06 10:50:22'),
(91, 49, 17, NULL, 32112, 'fdsfsdf', '2023-10-14 12:00:00', NULL, '2023-10-14 10:40:41', '2023-10-14 10:40:41'),
(92, 59, 18, NULL, 50000, 'dadadad', '2023-10-24 12:00:00', NULL, '2023-10-24 10:57:25', '2023-10-24 10:57:25'),
(93, 22, 8, 'A7816', 20000, 'gfgfyu', '2023-10-31 22:21:00', '1698751310.jpg', '2023-10-31 11:21:50', '2023-10-31 11:21:50'),
(94, 49, 16, NULL, 50000, 'ergfsdgsdfsd', '2023-11-01 17:05:00', NULL, '2023-11-01 10:05:13', '2023-11-01 10:05:13'),
(95, 22, 14, 'W3W4Z', 30000, 'jhbjkghbk', '2023-11-01 19:09:00', NULL, '2023-11-01 12:09:22', '2023-11-01 12:09:22'),
(96, 22, 15, 'S0JB3', 100000, 'fasdfasdasda', '2023-11-01 19:10:00', NULL, '2023-11-01 12:10:33', '2023-11-01 12:10:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `debit`
--

CREATE TABLE `debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `debit_date` datetime NOT NULL,
  `gambar` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `debit`
--

INSERT INTO `debit` (`id`, `user_id`, `category_id`, `id_transaksi`, `nominal`, `description`, `debit_date`, `gambar`, `created_at`, `updated_at`) VALUES
(46, 24, 1, '8O4Z0', 20000, 'sdasdasdasda', '2023-09-18 12:00:00', '1695018832.png', '2023-09-18 06:33:52', '2023-09-18 06:33:52'),
(47, 24, 1, 'DVSXX', 100000, 'ijokjjo', '2023-09-18 12:00:00', '1695018877.jpg', '2023-09-18 06:34:37', '2023-09-18 06:34:37'),
(48, 22, 5, '8QCFE', 5000, 'ujicoba', '2023-08-17 12:00:00', '1695174447.jpg', '2023-09-20 01:47:27', '2023-09-20 01:47:27'),
(49, 22, 4, '37ZOS', 5000, 'dsfsdfsdfsdfsdfsdfs', '2023-09-21 12:00:00', '1695263178.jpg', '2023-09-21 02:25:24', '2023-09-21 02:26:18'),
(50, 22, 4, 'KZ2GE', 50000, 'fsdfsdfsdf', '2023-09-21 12:00:00', '1695263270.jpg', '2023-09-21 02:27:50', '2023-09-21 02:27:50'),
(52, 22, 1, 'OHC2Y', 5000, 'hjgjhgj', '2023-10-05 12:00:00', '1696491675.jpg', '2023-10-05 07:41:15', '2023-10-05 07:41:15'),
(53, 24, 1, '2H3L7', 50000, 'dsasdasd', '2023-10-05 12:00:00', '1696493618.png', '2023-10-05 08:13:38', '2023-10-05 08:13:38'),
(54, 22, 25, 'OYEJ8', 11123, 'tes0000000000000123', '2023-10-06 12:00:00', NULL, '2023-10-06 10:53:35', '2023-10-06 10:58:59'),
(55, 49, 24, NULL, 100123, 'dasdasdsadasd00000000000', '2023-10-06 12:00:00', NULL, '2023-10-06 10:59:20', '2023-10-06 10:59:53'),
(56, 49, 31, NULL, 32112, 'asdasdasda', '2023-10-14 12:00:00', NULL, '2023-10-14 10:58:03', '2023-10-14 10:58:03'),
(57, 22, 27, 'XZPGD', 500000, 'sfdsfs', '2023-10-18 12:00:00', NULL, '2023-10-18 09:24:35', '2023-10-18 09:25:32'),
(58, 59, 34, NULL, 5000, 'asdasdasd', '2023-10-24 12:00:00', NULL, '2023-10-24 10:56:41', '2023-10-24 10:56:41'),
(59, 22, 32, 'O6NZG', 50000, 'dadasd', '2023-10-31 12:00:00', '1698727444.jpg', '2023-10-31 04:44:04', '2023-10-31 04:44:04'),
(60, 22, 36, '5VLDC', 5000, 'sadasda', '2023-10-31 12:00:00', '1698727971.jpg', '2023-10-31 04:52:51', '2023-10-31 04:52:51'),
(61, 22, 32, 'N8B0Z', 100000, 'dawdada', '2023-11-01 19:10:00', NULL, '2023-11-01 12:10:52', '2023-11-01 12:10:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `gaji`
--

CREATE TABLE `gaji` (
  `id` bigint(100) UNSIGNED NOT NULL,
  `user_id` bigint(100) UNSIGNED NOT NULL,
  `presensi_id` bigint(225) UNSIGNED DEFAULT NULL,
  `id_transaksi` varchar(200) DEFAULT NULL,
  `gaji_pokok` varchar(200) NOT NULL,
  `lembur` varchar(200) NOT NULL,
  `lembur1` varchar(100) DEFAULT NULL,
  `lembur2` varchar(100) DEFAULT NULL,
  `lembur3` varchar(100) DEFAULT NULL,
  `lembur4` varchar(100) DEFAULT NULL,
  `lembur5` varchar(100) DEFAULT NULL,
  `lembur6` varchar(100) DEFAULT NULL,
  `lembur7` varchar(100) DEFAULT NULL,
  `lembur8` varchar(100) DEFAULT NULL,
  `lembur9` varchar(100) DEFAULT NULL,
  `lembur10` varchar(100) DEFAULT NULL,
  `jumlah_lembur` varchar(100) DEFAULT NULL,
  `jumlah_lembur1` varchar(100) DEFAULT NULL,
  `jumlah_lembur2` varchar(100) DEFAULT NULL,
  `jumlah_lembur3` varchar(100) DEFAULT NULL,
  `jumlah_lembur4` varchar(100) DEFAULT NULL,
  `jumlah_lembur5` varchar(100) DEFAULT NULL,
  `jumlah_lembur6` varchar(100) DEFAULT NULL,
  `jumlah_lembur7` varchar(100) DEFAULT NULL,
  `jumlah_lembur8` varchar(100) DEFAULT NULL,
  `jumlah_lembur9` varchar(100) DEFAULT NULL,
  `jumlah_lembur10` varchar(100) DEFAULT NULL,
  `total_lembur` varchar(100) DEFAULT NULL,
  `bonus` varchar(200) NOT NULL,
  `bonus1` varchar(100) DEFAULT NULL,
  `bonus2` varchar(100) DEFAULT NULL,
  `bonus3` varchar(100) DEFAULT NULL,
  `bonus4` varchar(100) DEFAULT NULL,
  `bonus5` varchar(100) DEFAULT NULL,
  `bonus6` varchar(100) DEFAULT NULL,
  `bonus7` varchar(100) DEFAULT NULL,
  `bonus8` varchar(100) DEFAULT NULL,
  `bonus9` varchar(100) DEFAULT NULL,
  `bonus10` varchar(100) DEFAULT NULL,
  `bonus_luar` varchar(100) DEFAULT NULL,
  `bonus_luar1` varchar(100) DEFAULT NULL,
  `bonus_luar2` varchar(100) DEFAULT NULL,
  `bonus_luar3` varchar(100) DEFAULT NULL,
  `bonus_luar4` varchar(100) DEFAULT NULL,
  `bonus_luar5` varchar(100) DEFAULT NULL,
  `bonus_luar6` varchar(100) DEFAULT NULL,
  `bonus_luar7` varchar(100) DEFAULT NULL,
  `bonus_luar8` varchar(100) DEFAULT NULL,
  `bonus_luar9` varchar(100) DEFAULT NULL,
  `bonus_luar10` varchar(100) DEFAULT NULL,
  `jumlah_bonus` varchar(100) DEFAULT NULL,
  `jumlah_bonus1` varchar(100) DEFAULT NULL,
  `jumlah_bonus2` varchar(100) DEFAULT NULL,
  `jumlah_bonus3` varchar(100) DEFAULT NULL,
  `jumlah_bonus4` varchar(100) DEFAULT NULL,
  `jumlah_bonus5` varchar(100) DEFAULT NULL,
  `jumlah_bonus6` varchar(100) DEFAULT NULL,
  `jumlah_bonus7` varchar(100) DEFAULT NULL,
  `jumlah_bonus8` varchar(100) DEFAULT NULL,
  `jumlah_bonus9` varchar(100) DEFAULT NULL,
  `jumlah_bonus10` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar1` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar2` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar3` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar4` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar5` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar6` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar7` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar8` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar9` varchar(100) DEFAULT NULL,
  `jumlah_bonus_luar10` varchar(100) DEFAULT NULL,
  `total_bonus` varchar(100) DEFAULT NULL,
  `tunjangan` varchar(200) NOT NULL,
  `tunjangan_bpjs` varchar(300) DEFAULT NULL,
  `tunjangan_thr` varchar(300) DEFAULT NULL,
  `tunjangan_pulsa` varchar(300) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `potongan` varchar(100) NOT NULL,
  `pph` varchar(300) DEFAULT NULL,
  `alpha` varchar(300) DEFAULT NULL,
  `total` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `gaji`
--

INSERT INTO `gaji` (`id`, `user_id`, `presensi_id`, `id_transaksi`, `gaji_pokok`, `lembur`, `lembur1`, `lembur2`, `lembur3`, `lembur4`, `lembur5`, `lembur6`, `lembur7`, `lembur8`, `lembur9`, `lembur10`, `jumlah_lembur`, `jumlah_lembur1`, `jumlah_lembur2`, `jumlah_lembur3`, `jumlah_lembur4`, `jumlah_lembur5`, `jumlah_lembur6`, `jumlah_lembur7`, `jumlah_lembur8`, `jumlah_lembur9`, `jumlah_lembur10`, `total_lembur`, `bonus`, `bonus1`, `bonus2`, `bonus3`, `bonus4`, `bonus5`, `bonus6`, `bonus7`, `bonus8`, `bonus9`, `bonus10`, `bonus_luar`, `bonus_luar1`, `bonus_luar2`, `bonus_luar3`, `bonus_luar4`, `bonus_luar5`, `bonus_luar6`, `bonus_luar7`, `bonus_luar8`, `bonus_luar9`, `bonus_luar10`, `jumlah_bonus`, `jumlah_bonus1`, `jumlah_bonus2`, `jumlah_bonus3`, `jumlah_bonus4`, `jumlah_bonus5`, `jumlah_bonus6`, `jumlah_bonus7`, `jumlah_bonus8`, `jumlah_bonus9`, `jumlah_bonus10`, `jumlah_bonus_luar`, `jumlah_bonus_luar1`, `jumlah_bonus_luar2`, `jumlah_bonus_luar3`, `jumlah_bonus_luar4`, `jumlah_bonus_luar5`, `jumlah_bonus_luar6`, `jumlah_bonus_luar7`, `jumlah_bonus_luar8`, `jumlah_bonus_luar9`, `jumlah_bonus_luar10`, `total_bonus`, `tunjangan`, `tunjangan_bpjs`, `tunjangan_thr`, `tunjangan_pulsa`, `tanggal`, `potongan`, `pph`, `alpha`, `total`, `created_at`, `status`, `note`, `gambar`, `updated_at`) VALUES
(88, 49, NULL, 'EIMFM', '3000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '0', '0', '0', '0', '2023-12-06 12:00:00', '0', '0', NULL, '3200000', '2023-12-06 02:53:37', 'pending', NULL, NULL, '2023-12-06 02:53:37'),
(89, 49, NULL, 'KDMVQ', '5000000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '200000', '10000', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '400000', '0', '0', '0', '0', '2023-11-06 12:00:00', '0', '0', NULL, '5600000', '2023-11-06 03:04:06', 'terbayar', NULL, NULL, '2023-12-06 06:15:44'),
(91, 49, NULL, 'FI69W', '5000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '300000', '0', '0', '0', '0', '2023-12-06 12:00:00', '0', '0', NULL, '5400000', '2023-12-06 03:21:31', 'terbayar', NULL, NULL, '2023-12-06 06:14:23'),
(92, 24, NULL, '0MQ4H', '3000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '0', '0', '0', '50000', '2023-12-06 12:00:00', '0', '0', NULL, '3250000', '2023-12-06 06:17:11', 'pending', NULL, NULL, '2023-12-11 15:09:29'),
(93, 49, NULL, 'VWRJT', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '0', '0', '0', '100000', '2023-12-12 12:00:00', '0', '0', NULL, '2300000', '2023-12-11 15:10:16', 'pending', NULL, NULL, '2023-12-12 04:11:18'),
(94, 22, NULL, 'OGSAU', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '100000', '100000', '100000', '50000', '2023-12-22 23:24:00', '100000', '100000', NULL, '2250000', '2023-12-22 16:24:16', 'pending', '<p>sdfasdasd</p>', NULL, '2023-12-22 16:24:16'),
(95, 22, NULL, '0DUMM', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:28:00', '0', '0', NULL, '2100000', '2023-12-22 16:28:10', 'pending', NULL, NULL, '2023-12-22 16:28:10'),
(96, 22, NULL, '0VV0W', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:29:00', '0', '0', NULL, '2100000', '2023-12-22 16:29:24', 'pending', NULL, NULL, '2023-12-22 16:29:24'),
(97, 22, NULL, 'KHBE9', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:39:00', '0', '0', NULL, '2100000', '2023-12-22 16:40:05', 'pending', NULL, NULL, '2023-12-22 16:40:05'),
(98, 22, NULL, 'HRYMC', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:44:00', '0', '0', NULL, '2100000', '2023-12-22 16:44:27', 'pending', NULL, NULL, '2023-12-22 16:44:27'),
(99, 22, NULL, 'NTJ2R', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:52:00', '0', '0', NULL, '2100000', '2023-12-22 16:52:54', 'pending', NULL, NULL, '2023-12-22 16:52:54'),
(100, 22, NULL, '0NB8M', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:55:00', '0', '0', NULL, '2100000', '2023-12-22 16:56:00', 'pending', NULL, NULL, '2023-12-22 16:56:00'),
(101, 22, NULL, '026LN', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:57:00', '0', '0', NULL, '2000000', '2023-12-22 16:57:26', 'pending', NULL, NULL, '2023-12-22 16:57:26'),
(102, 22, NULL, 'B7RXF', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-22 23:59:00', '0', '0', NULL, '2100000', '2023-12-22 16:59:53', 'pending', NULL, NULL, '2023-12-22 16:59:53'),
(103, 22, NULL, 'YAMV1', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-23 00:03:00', '0', '0', NULL, '2100000', '2023-12-22 17:03:52', 'pending', NULL, NULL, '2023-12-22 17:03:52'),
(104, 22, NULL, 'ECR03', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-23 00:03:00', '0', '0', NULL, '2100000', '2023-12-22 17:04:22', 'pending', NULL, NULL, '2023-12-22 17:04:22'),
(105, 22, NULL, '8NTYB', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '0', '0', '0', '2023-12-23 00:03:00', '0', '0', NULL, '2100000', '2023-12-22 17:06:25', 'pending', NULL, NULL, '2023-12-22 17:06:25'),
(106, 22, NULL, 'VC79Q', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '30000', '0', '0', '0', '0', '2023-12-23 00:06:00', '0', '0', NULL, '2130000', '2023-12-22 17:06:53', 'pending', NULL, NULL, '2023-12-22 17:20:23'),
(107, 22, NULL, 'Z9Y4W', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '1', '1', '1', NULL, '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '0', '0', '0', '0', '2023-12-23 18:26:00', '0', '0', NULL, '2100000', '2023-12-23 11:26:37', 'pending', NULL, NULL, '2023-12-23 11:26:37'),
(108, 22, NULL, 'N5TI1', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '2', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '95000', '0', '0', '0', '0', '2023-12-23 21:47:00', '0', '0', NULL, '2094999.99', '2023-12-23 14:47:56', 'pending', NULL, NULL, '2023-12-23 14:47:56'),
(109, 22, NULL, 'XF5Q5', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '2', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '95000', '0', '0', '0', '0', '2023-12-23 21:57:00', '0', '0', NULL, '2095000', '2023-12-23 14:57:17', 'pending', NULL, NULL, '2023-12-23 14:57:17'),
(110, 22, NULL, 'VBY8G', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '2', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '95000', '0', '0', '0', '0', '2023-12-23 22:05:00', '0', '0', NULL, '2094999.99', '2023-12-23 15:05:31', 'pending', NULL, NULL, '2023-12-23 15:05:31'),
(111, 22, NULL, '9X6O7', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '2', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '95000', '0', '0', '0', '0', '2023-12-23 22:09:00', '0', '0', NULL, '2094999.99', '2023-12-23 15:09:25', 'pending', NULL, NULL, '2023-12-23 15:09:25'),
(112, 22, NULL, 'GW43B', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '3', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '95000', '0', '0', '0', '0', '2023-12-23 22:13:00', '0', '0', NULL, '2063575', '2023-12-23 15:13:09', 'pending', NULL, NULL, '2023-12-23 15:47:32'),
(113, 22, NULL, 'MV361', '2000000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', '2', '0.5', '1', '1', '3', '1', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '75000', '100000', '0', '0', '100000', '2023-12-24 00:02:00', '0', '0', NULL, '2240875', '2023-12-23 17:02:34', 'pending', NULL, NULL, '2023-12-23 17:02:34');

-- --------------------------------------------------------

--
-- Struktur dari tabel `maintenance`
--

CREATE TABLE `maintenance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL,
  `status` varchar(300) NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `maintenance`
--

INSERT INTO `maintenance` (`id`, `user_id`, `title`, `note`, `start_date`, `end_date`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(5, NULL, 'nyoba', 'adfasdasdas', '2023-12-23 11:00:00', '2023-12-26 16:00:00', '1695610770.jpg', 'aktif', '2023-09-24 15:14:53', '2023-12-25 11:09:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(4, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(5, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(6, '2016_06_01_000004_create_oauth_clients_table', 1),
(7, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(8, '2019_08_19_000000_create_failed_jobs_table', 1),
(9, '2019_11_08_081749_create_categories_credit_table', 1),
(10, '2019_11_08_081805_create_credit_table', 1),
(11, '2019_11_08_081821_create_categories_debit_table', 1),
(12, '2019_11_08_081836_create_debit_table', 1),
(13, '2023_07_25_152808_add_company_to_categories_debit', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int(10) UNSIGNED NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`email`, `token`, `created_at`) VALUES
('bertojunikrisnanto@gmail.com', '$2y$10$VErPDUpeBmh0tXR3hUFZrODQrAdZZEa489/97vNuKaVmZ4GgjYUVq', '2023-09-24 03:53:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewaan`
--

CREATE TABLE `penyewaan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tambah_barang_id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` varchar(100) DEFAULT NULL,
  `nama` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `telp` varchar(200) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `identitas` varchar(200) DEFAULT NULL,
  `jumlah` varchar(200) DEFAULT NULL,
  `lama_peminjaman` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `pengembalian` date NOT NULL,
  `subtotal` varchar(300) DEFAULT NULL,
  `total` varchar(300) DEFAULT NULL,
  `diskon` varchar(300) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `jaminan` varchar(100) DEFAULT NULL,
  `jenis_pembayaran` varchar(100) DEFAULT NULL,
  `kekurangan_pembayaran` varchar(100) DEFAULT NULL,
  `jumlah_pembayaran` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `penyewaan`
--

INSERT INTO `penyewaan` (`id`, `user_id`, `tambah_barang_id`, `id_transaksi`, `nama`, `email`, `telp`, `alamat`, `identitas`, `jumlah`, `lama_peminjaman`, `tanggal`, `pengembalian`, `subtotal`, `total`, `diskon`, `status`, `jaminan`, `jenis_pembayaran`, `kekurangan_pembayaran`, `jumlah_pembayaran`, `created_at`, `updated_at`) VALUES
(1, 22, 2, NULL, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', '0895421735441', 'fghdfgdfgdf', '56456456', '3', NULL, '2023-08-03', '0000-00-00', NULL, NULL, NULL, 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 00:37:53', '2023-08-03 00:37:53'),
(2, 22, 3, NULL, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', '0895421735441', 'tc', '56456456', '1', NULL, '2023-08-03', '0000-00-00', NULL, NULL, NULL, 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 00:42:40', '2023-08-03 00:42:40'),
(3, 22, 3, NULL, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', '08954217354413', 'afasdasdasd', '56456456', '5', NULL, '2023-08-03', '0000-00-00', NULL, NULL, NULL, 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 01:28:41', '2023-08-03 01:28:41'),
(4, 22, 3, NULL, 'berto juni', 'bertojunikrisnanto@gmail.com', '08954217354413', 'afdasdasdas', '56456456', '3', NULL, '2023-08-03', '0000-00-00', '150000', '150000', NULL, 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 01:32:18', '2023-08-03 01:32:18'),
(5, 22, 3, NULL, 'emka digital juni', 'bertojunikrisnanto@gmail.com', '0895421735441', 'afasdasdasd', '56456456', '5', NULL, '2023-08-03', '0000-00-00', '250000', '250000', NULL, 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 01:36:43', '2023-08-03 01:36:43'),
(6, 22, 3, NULL, 'febi', 'bertojunikrisnanto@gmail.com', '0895421735441', 'sadasdasdas', '56456456', '5', NULL, '2023-08-03', '0000-00-00', '250000', '240000', '10000', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 03:06:39', '2023-08-03 03:06:39'),
(8, 22, 5, NULL, 'baim', 'bertojunikrisnanto@gmail.com', '0895421735441', 'sdafasdasdas', '56456456', '5', NULL, '2023-08-03', '0000-00-00', '1250000', '1240000', '10000', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 03:41:07', '2023-08-03 03:41:07'),
(9, 22, 5, NULL, 'berto juni', 'bertojunikrisnanto@gmail.com', '0895421735441', 'dasdasdas', '56456456', '1', NULL, '2023-08-03', '0000-00-00', '250000', '250000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 03:57:13', '2023-08-03 03:57:13'),
(10, 22, 2, NULL, 'emka digital juni', 'dajksdnklas4535@gmail.com', '0895421735441', 'dawdawdada', '56456456', '1', NULL, '2023-08-03', '0000-00-00', '50000', '50000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 03:59:20', '2023-08-03 03:59:20'),
(11, 22, 2, NULL, 'berto juni123', 'bertojunikrisnanto@gmail.com', '0895421735441', 'asdasdasd', '56456456', '3', NULL, '2023-08-03', '0000-00-00', '150000', '150000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 04:01:23', '2023-08-03 04:28:34'),
(13, 22, 5, NULL, '31 januari ujicoba part 2', 'bertojunikrisnanto@gmail.com', '08954217', 'dawdawdaw', '56456456', '1', NULL, '2023-08-03', '0000-00-00', '250000', '250000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-03 04:39:52', '2023-08-03 04:39:52'),
(15, 22, 6, NULL, 'hapid', 'bertojunikrisnanto@gmail.com', '0895421735441', 'drgrggsdgsdg', '56456456', '3', '2', '2023-08-05', '0000-00-00', '300000', '300000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-04 20:20:01', '2023-08-04 20:20:01'),
(16, 22, 3, NULL, 'ida ervi', 'bertojunikrisnanto@gmail.com', '0895421735441', 'DASDASDAS', '56456456', '1', '7', '2023-08-05', '2023-08-12', '350000', '350000', '0', 'dikembalikan', NULL, NULL, NULL, NULL, '2023-08-04 23:13:38', '2023-08-05 22:54:49'),
(17, 22, 2, NULL, 'yolanda', 'bertojunikrisnanto@gmail.com', '0895421735441', 'sdfdsfsdf', '56456456', '3', '5', '2023-08-06', '2023-08-11', '750000', '750000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-05 22:14:05', '2023-08-05 22:14:05'),
(18, 22, 5, NULL, 'berto juni', 'bertojunikrisnanto@gmail.com', '0895421735441', 'DFGSFSDF', '56456456', '3', '5', '2023-08-06', '2023-08-11', '3750000', '3750000', '0', 'dipakai', NULL, NULL, NULL, NULL, '2023-08-05 23:35:15', '2023-08-05 23:35:15'),
(19, 22, 5, NULL, 'emka digital juni', 'bertojunikrisnanto@gmail.com', '0895421735441', 'SDFSDFSDF', '56456456', '3', '5', '2023-08-06', '2023-08-11', '3750000', '3750000', '0', 'dikembalikan', 'ktm', NULL, NULL, NULL, '2023-08-05 23:36:14', '2023-08-09 10:32:36'),
(21, 22, 5, NULL, 'Berto Juni Krisnanto12345', 'bertojunikrisnanto@gmail.com', '0895421735441', 'jbjbj', '56456456', '3', '2', '2023-08-06', '2023-08-08', '1500000', '1000000', '500000', 'dikembalikan', NULL, NULL, NULL, NULL, '2023-08-06 00:21:20', '2023-08-06 00:27:39'),
(22, 22, 5, NULL, 'ipan', 'bertojunikrisnanto@gmail.com', '0895421735441', 'gfdgdgdfgdf', '56456456', '3', '3', '2023-08-06', '2023-08-09', '2250000', '2250000', '0', 'dikembalikan', 'ktm', NULL, NULL, NULL, '2023-08-06 00:56:51', '2023-08-06 00:57:46'),
(24, 22, 5, NULL, 'wildan', 'wildan@gmail.com', '0895421735441', 'adasdasd', '56456456', '3', '5', '2023-08-10', '2023-08-15', '3750000', '3650000', '100000', 'dikembalikan', 'sim', NULL, NULL, NULL, '2023-08-09 08:29:52', '2023-08-09 10:29:49'),
(29, 22, 5, NULL, 'cahyo', 'cahyo@gmail.com', '0895421735441', 'sdfsdf', '56456456', '3', '5', '2023-08-10', '2023-08-15', '3750000', '3650000', '100000', 'dikembalikan', 'sim', NULL, NULL, NULL, '2023-08-09 08:54:51', '2023-08-09 10:27:34'),
(30, 22, 5, NULL, 'baim', 'bertojunikrisnanto@gmail.com', '0895421735441', 'sgfsdfsdfsd', '56456456', '3', '5', '2023-08-10', '2023-08-15', '3750000', '3650000', '100000', 'dikembalikan', 'sim', NULL, NULL, NULL, '2023-08-09 08:59:26', '2023-08-09 10:26:29'),
(33, 22, 5, NULL, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', '0895421735441', 'sfgsdfsd', '56456456', '3', '5', '2023-08-10', '2023-08-15', '3750000', '3750000', '0', 'dikembalikan', 'ktm', NULL, NULL, NULL, '2023-08-09 10:33:12', '2023-08-09 10:33:40'),
(34, 22, 7, NULL, 'emka digital juni123', 'bertojunikrisnanto1@gmail.com', '08954217354411', 'hjvvhvjvghvj123', '56456456123', '5', '10', '2023-08-10', '2023-08-20', '2500000', '2000000', '500000', 'dikembalikan', 'sim', 'lunas', '0', '0', '2023-08-09 10:56:23', '2023-08-09 19:37:38'),
(35, 22, 7, NULL, 'baim1234567', 'bertojunikrisnanto1234@gmail.com', '0895421735441', 'fdxxfxdf', '56456456', '5', '5', '2023-08-10', '2023-08-15', '1250000', '1150000', '100000', 'dikembalikan', 'sim', 'dp', '650000', '500000', '2023-08-09 18:48:13', '2023-08-09 19:57:26'),
(36, 22, 7, NULL, 'fertika123', 'bertojunikrisnanto1@gmail.com', '08954217354412', 'sdfsdfsdf123', '56456456124', '5', '5', '2023-08-10', '2023-08-15', '1250000', '1150000', '100000', 'dikembalikan', 'motor', 'dp', '1150000', '0', '2023-08-09 19:46:43', '2023-08-09 19:54:08'),
(37, 22, 7, 'JJY3V', 'peboyy', 'bertojunikrisnanto@gmail.com', '0895421735441', 'asdadasd', '56456456', '5', '5', '2023-08-10', '2023-08-15', '1250000', '1250000', '0', 'dikembalikan', 'sim', 'lunas', '0', '0', '2023-08-09 22:58:49', '2023-08-09 23:41:24'),
(38, 22, 7, 'QW4VV', '31 januari ujicoba part 2', 'bertojunikrisnanto@gmail.com', '0895421735441', 'dasdasdasdasd', '56456456', '5', '5', '2023-08-10', '2023-08-15', '1250000', '1250000', '0', 'dikembalikan', 'motor', 'lunas', '0', '0', '2023-08-10 03:17:21', '2023-08-10 03:17:42'),
(39, 47, 8, 'V0KBG', 'wildan', 'wildan@gmail.com', '09989887677', 'hbbhjbjb', '6876785858', '3', '5', '2023-08-20', '2023-08-25', '3000000', '3000000', '0', 'dikembalikan', 'sim', 'lunas', '0', '0', '2023-08-11 21:08:45', '2023-08-11 21:09:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `presensi`
--

CREATE TABLE `presensi` (
  `id` bigint(100) UNSIGNED NOT NULL,
  `user_id` bigint(100) UNSIGNED NOT NULL,
  `status` varchar(100) DEFAULT NULL,
  `status_pulang` varchar(300) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL,
  `gambar_pulang` varchar(300) DEFAULT NULL,
  `lokasi` varchar(300) DEFAULT NULL,
  `time_pulang` datetime DEFAULT NULL,
  `latitude` varchar(300) DEFAULT NULL,
  `longitude` varchar(300) DEFAULT NULL,
  `hadir` varchar(300) DEFAULT NULL,
  `alpha` varchar(300) DEFAULT NULL,
  `camp_jogja` varchar(300) DEFAULT NULL,
  `perjalanan_jawa` varchar(300) DEFAULT NULL,
  `perjalanan_luar_jawa` varchar(300) DEFAULT NULL,
  `camp_luar_kota` varchar(300) DEFAULT NULL,
  `remote` varchar(300) DEFAULT NULL,
  `izin` varchar(300) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `presensi`
--

INSERT INTO `presensi` (`id`, `user_id`, `status`, `status_pulang`, `note`, `gambar`, `gambar_pulang`, `lokasi`, `time_pulang`, `latitude`, `longitude`, `hadir`, `alpha`, `camp_jogja`, `perjalanan_jawa`, `perjalanan_luar_jawa`, `camp_luar_kota`, `remote`, `izin`, `created_at`, `updated_at`) VALUES
(67, 24, 'izin', 'pulang', '<p>dawdawda</p>', '1695013916.png', NULL, 'Unknown', '2023-09-18 15:13:33', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-09-18 12:11:56', '2023-09-18 08:13:33'),
(68, 22, 'izin', 'pulang', '<p>adasdasdasd</p>', '1695263096.jpg', NULL, 'Unknown', '2023-09-21 09:24:56', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-09-21 09:23:15', '2023-09-21 02:24:56'),
(69, 22, 'terlambat', 'pulang', '<p>uhgiugh</p>', '1696419142.jpg', NULL, 'Unknown', '2023-10-18 09:43:41', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-04 18:32:22', '2023-10-18 02:43:41'),
(70, 22, 'terlambat', 'pulang', '<p>fhfh</p>', '1696492288.png', NULL, 'Unknown', '2023-10-09 14:30:09', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-05 14:51:28', '2023-10-09 07:30:09'),
(74, 22, 'terlambat', 'pulang', '<p>yffhfhfgfh</p>', '1696836557.jpg', NULL, 'Unknown', '2023-10-18 09:47:37', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-09 14:29:17', '2023-10-18 02:47:37'),
(80, 22, 'cuti', 'pulang', '<p>adasdasdas</p>', '1697467364.jpg', NULL, 'Unknown', '2023-10-18 09:52:40', '-7.805682594463084', '110.38436878165705', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-16 21:42:44', '2023-10-18 02:52:40'),
(81, 22, 'cuti', 'pulang', '<p>asdasdasd</p>', '1697467826.png', NULL, 'Unknown', '2023-10-18 09:43:23', '-7.802840573999901', '110.38367271423341', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-16 21:50:26', '2023-10-18 02:43:23'),
(82, 22, 'lembur', 'pulang', '<p>dwadawdawda</p>', '1697468181.png', NULL, 'Unknown', '2023-10-18 09:43:15', '-7.401962194884132', '109.69024658203126', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-16 21:56:21', '2023-10-18 02:43:15'),
(94, 22, 'tidak bisa presensi', 'pulang', NULL, '1697515103.jpg', NULL, 'Unknown', '2023-10-18 09:36:47', '-7.8057924', '110.3843605', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-17 10:58:23', '2023-10-18 02:36:47'),
(105, 49, 'tidak bisa presensi', 'pulang', NULL, '1697600525.png', NULL, 'Unknown', '2023-10-18 10:43:40', '-7.7430784', '110.4150528', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-18 10:42:05', '2023-10-18 03:43:40'),
(106, 24, 'tidak bisa presensi', 'pulang', NULL, '1697600637.png', NULL, 'Unknown', '2023-10-18 10:46:13', '-7.8057922', '110.3843608', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-18 10:43:57', '2023-10-18 03:46:13'),
(107, 59, 'lembur', 'pulang', '<p>sdasdasd</p>', '1697790183.jpg', NULL, 'Unknown', '2023-10-20 15:27:18', '-7.646194', '110.3529108', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-20 15:23:03', '2023-10-20 08:27:18'),
(109, 49, 'izin', 'pulang', NULL, '1698462957.jpg', NULL, 'Unknown', '2023-10-28 10:17:36', '-7.798784', '110.395392', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-28 10:15:57', '2023-10-28 03:17:36'),
(110, 49, 'terlambat', 'pulang', '<p>ffdsfsfsd</p>', '1698556430.jpg', '1698718202.jpg', 'Unknown', '2023-10-31 09:10:02', '-7.798784', '110.395392', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-29 12:13:50', '2023-10-31 02:10:02'),
(116, 22, 'libur', NULL, NULL, 'images/1698720073.jpg', NULL, 'Unknown', NULL, '-7.8020608', '110.3200256', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-31 09:41:13', '2023-10-31 02:41:13'),
(117, 22, 'libur', NULL, '<p>egfdgdf</p>', 'images/1698720142.jpg', NULL, 'Unknown', NULL, '-7.8020608', '110.3200256', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-31 09:42:22', '2023-10-31 02:42:22'),
(118, 22, 'libur', NULL, '<p>rthyhrty</p>', 'images/1698720214.jpg', NULL, 'Unknown', NULL, '-7.8020608', '110.3200256', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-31 09:43:34', '2023-10-31 02:43:34'),
(119, 22, 'libur', NULL, '<p>gdgdrgdrg</p>', '1698720293.jpg', NULL, 'Unknown', NULL, '-7.8020608', '110.3200256', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-31 09:44:53', '2023-10-31 02:44:53'),
(120, 22, 'libur', NULL, '<p>dgdfgdf</p>', '1698720509.jpg', NULL, 'Unknown', NULL, '-7.8020608', '110.3200256', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-10-31 09:48:29', '2023-10-31 02:48:29'),
(125, 49, 'cuti', 'pulang', '<p>asdasdasd</p>', '1698984417.jpg', '1698984430.jpg', 'Unknown', '2023-11-03 11:07:10', '-7.798784', '110.4838656', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-03 11:06:57', '2023-11-03 04:07:10'),
(126, 49, 'terlambat', 'pulang', NULL, '1699156078.jpg', '1699156092.jpg', 'Unknown', '2023-11-05 10:48:12', '-7.6461828', '110.3529002', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-05 10:47:58', '2023-11-05 03:48:12'),
(138, 49, 'tidak bisa presensi', NULL, NULL, '1699894506.png', NULL, 'Unknown', NULL, '-7.7692928', '110.4183296', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-13 23:55:06', '2023-11-13 16:55:06'),
(140, 49, 'izin', NULL, NULL, '1700837824.jpg', NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 21:57:04', '2023-11-24 14:57:04'),
(141, 49, 'izin', NULL, NULL, '1700838078.jpg', NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-20 22:01:18', '2023-11-20 15:01:18'),
(142, 49, 'izin', NULL, NULL, '1700838104.jpg', NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-21 22:01:44', '2023-11-21 15:01:44'),
(143, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:07:34', '2023-11-24 15:07:34'),
(144, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:08:38', '2023-11-24 15:08:38'),
(145, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:10:19', '2023-11-24 15:10:19'),
(146, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:10:31', '2023-11-24 15:10:31'),
(147, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:13:17', '2023-11-24 15:13:17'),
(148, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:17:12', '2023-11-24 15:17:12'),
(149, 49, 'tidak bisa presensi', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.6840848', '110.3428085', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-11-24 22:24:50', '2023-11-24 15:24:50'),
(157, 49, 'terlambat', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-05 15:47:03', '-7.7651543', '110.3666396', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-03 15:46:01', '2023-12-05 08:47:03'),
(158, 49, 'terlambat', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-05 15:52:11', '-7.7651543', '110.3666396', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-04 00:00:00', '2023-12-05 08:52:11'),
(159, 49, 'terlambat', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-05 15:55:03', '-7.7651543', '110.3666396', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-05 15:54:45', '2023-12-05 08:55:03'),
(160, 22, 'camp jogja', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-06 10:53:38', '-7.8221165', '110.4619708', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-06 10:50:27', '2023-12-06 03:53:38'),
(161, 49, 'hadir', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-07 08:44:31', '-7.8057648', '110.3843301', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-07 08:44:17', '2023-12-07 01:44:31'),
(162, 22, 'terlambat', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.3007104', '112.7284736', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-19 21:18:52', '2023-12-19 14:18:52'),
(163, 22, 'hadir', 'pulang', NULL, NULL, NULL, 'Unknown', '2023-12-20 21:46:36', '-7.8039292', '110.4143039', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-20 21:46:01', '2023-12-20 14:46:36'),
(164, 22, 'camp jogja', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.7872061', '110.3926592', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-21 20:03:26', '2023-12-21 13:03:26'),
(172, 49, 'hadir', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.8905344', '110.280704', NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, '2023-12-21 23:41:18', '2023-12-21 16:41:18'),
(193, 22, 'hadir', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.8905344', '110.280704', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-22 21:56:47', '2023-12-22 14:56:47'),
(194, 22, 'hadir', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-21 23:05:15', '2023-12-21 16:05:15'),
(195, 22, 'hadir', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-20 00:16:01', '2023-12-19 17:16:01'),
(196, 22, 'camp jogja', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, '2023-12-23 00:22:28', '2023-12-22 17:22:28'),
(197, 22, 'camp jogja', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '', NULL, NULL, NULL, NULL, '2023-12-21 15:08:47', '2023-12-21 08:08:47'),
(198, 22, 'perjalanan luar kota jawa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.25', NULL, NULL, NULL, NULL, '2023-12-22 15:18:42', '2023-12-22 08:18:42'),
(199, 22, 'perjalanan luar kota luar jawa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.5', NULL, NULL, NULL, '2023-12-20 15:19:47', '2023-12-20 08:19:47'),
(200, 22, 'remote', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2023-12-20 16:16:33', '2023-12-20 09:16:33'),
(201, 22, 'izin', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', '2023-12-21 16:16:33', '2023-12-21 09:16:33'),
(202, 22, 'camp luar kota', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, '1', NULL, NULL, '2023-12-22 16:18:20', '2023-12-22 09:18:20'),
(203, 22, 'perjalanan luar kota jawa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '0.25', NULL, NULL, NULL, NULL, '2023-12-22 21:32:19', '2023-12-22 14:32:19'),
(205, 49, 'remote', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, '2023-12-22 21:36:01', '2023-12-21 17:00:00'),
(206, 22, 'perjalanan luar kota jawa', NULL, NULL, NULL, '1703391955.png', 'Unknown', NULL, NULL, NULL, NULL, NULL, NULL, '0.25', NULL, NULL, NULL, NULL, '2023-12-24 09:34:00', '2023-12-24 05:49:18'),
(207, 24, 'hadir', NULL, NULL, NULL, NULL, 'Unknown', NULL, '-7.64617', '110.3528643', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-27 10:19:21', '2023-12-27 03:19:21'),
(208, 24, 'hadir', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2024-01-01 19:01:00', '2024-01-01 12:01:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tambah_barang`
--

CREATE TABLE `tambah_barang` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama_barang` varchar(200) DEFAULT NULL,
  `harga_barang` bigint(200) DEFAULT NULL,
  `stok` varchar(200) DEFAULT NULL,
  `diskon` bigint(200) DEFAULT NULL,
  `jenis` varchar(300) DEFAULT NULL,
  `perhari` varchar(300) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `tambah_barang`
--

INSERT INTO `tambah_barang` (`id`, `user_id`, `nama_barang`, `harga_barang`, `stok`, `diskon`, `jenis`, `perhari`, `created_at`, `updated_at`) VALUES
(2, 22, 'TESS12', 50000, '2', 2000, NULL, NULL, '2023-08-02 09:16:42', '2023-08-05 22:14:05'),
(3, 22, 'NYOBA123', 50000, '53', 5000, NULL, NULL, '2023-08-02 09:37:30', '2023-08-05 22:54:49'),
(4, 22, 'TESS123456', 50000, '20', 2000, NULL, NULL, '2023-08-02 09:53:30', '2023-08-02 09:53:30'),
(5, 22, 'TESS', 250000, '1', 5000, 'crossover', 'setengah', '2023-08-02 18:42:06', '2023-08-09 19:10:29'),
(6, 22, 'MAZDA', 50000, '0', 0, 'sedan', 'sehari', '2023-08-03 07:14:32', '2023-08-09 09:54:54'),
(7, 22, 'HRV', 50000, '25', 0, 'suv', 'sehari', '2023-08-09 10:41:08', '2023-08-10 03:17:42'),
(8, 47, 'BMW', 200000, '20', 0, 'sedan', 'sehari', '2023-08-11 21:04:46', '2023-08-11 21:09:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `total`
--

CREATE TABLE `total` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gaji_id` bigint(20) UNSIGNED DEFAULT NULL,
  `presensi_id` bigint(20) UNSIGNED DEFAULT NULL,
  `hadir` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `level` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `jenis` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'perorangan',
  `company` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_company` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_company` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_company` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_company` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pj_company` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notif` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenggat` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norek` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jobdesk` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_hadir` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `email_verified_at`, `avatar`, `remember_token`, `created_at`, `updated_at`, `level`, `jenis`, `company`, `alamat_company`, `telp_company`, `email_company`, `logo_company`, `pj_company`, `telp`, `status`, `notif`, `tenggat`, `title`, `nik`, `norek`, `bank`, `gambar`, `jobdesk`, `total_hadir`) VALUES
(1, 'Berto Juni', 'bertojuni@gmail.com', 'bertojuni', 'junijuni008', NULL, '898192462.png', NULL, NULL, NULL, 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'bertojunik', 'bertojunijuni@gmail.com', 'bertojunik', '$2y$10$wwknRoFVyEgG51kO0sxICepdHSjf5Dvd2QdxKkOexM.XyMEddihS.', '2023-07-15 22:14:15', NULL, NULL, '2023-07-15 01:15:22', '2023-07-15 22:14:15', 'admin', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'junijuni', 'juni@gmail.com', 'juni', '$2y$10$LCf6VN3S7vpf0aw87rdG7ursTKa7gide8ziPUl64P5stnbdd9CeyC', NULL, NULL, NULL, '2023-07-15 03:50:27', '2023-07-15 03:50:27', '', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'AJIROHMAT', 'aji@gmail.com', 'aji123', '$2y$10$bq1CweJhPPSviFZT7AmyFufN6J0rm2ojJJ1LTV62UCxG.QNka6l.6', NULL, NULL, NULL, '2023-07-15 04:30:17', '2023-07-15 08:16:08', 'bisnis', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'Berto Krisnanto', 'admin@gmail.com', 'admin', '$2y$10$HfeNu60ry3y0iMqe48LITubOI0dIVYZAHshOmlY6D5EF8KLl2/4hG', '2023-07-15 22:28:38', NULL, NULL, '2023-07-15 22:28:38', '2023-11-14 05:02:37', 'admin', '', 'bertojuni', NULL, NULL, NULL, NULL, NULL, '+6285155240654', 'on', NULL, NULL, NULL, '123456', '123456', '451', NULL, '<p>sdasdas</p>', NULL),
(16, 'user', 'user@gmail.com', 'user', '$2y$10$1LbprO4sqJha0rQYVq.Zp.2lZDVRXkbNqhs2qVWfY/ldSIqbTxwYu', NULL, NULL, NULL, '2023-07-15 22:29:08', '2023-07-15 22:29:08', 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'bisnis', 'bisnis@gmail.com', 'bisnis', '$2y$10$5i1VcojhcIe.d17xyxMkYu/w5B9IbxWR4YbHCAfoGb6Dm./CxM262', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:31:55', '2023-07-15 22:31:55', 'bisnis', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'user2', 'user2@gmail.com', 'user2', '$2y$10$ij09uJWwUSED1xesf2nyceme9MlQyrL7L9ObwwIxEDlpHZGRL5fKG', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:44:41', '2023-07-15 22:44:41', 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'admin2', 'admin2@gmail.com', 'admin2', '$2y$10$ZXWl8md4Tts7TtkQSBgLR.R7qmrhj6LmxgVJN9joaLHVmPQQsxNQu', '2023-07-16 07:48:39', NULL, NULL, '2023-07-16 07:48:39', '2023-07-16 07:48:39', 'admin', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'manager', 'manager@gmail.com', 'manager', '$2y$10$fIOHMi7XbpMrX1774/gEJevQiasVLwo0KMZ307nplhMioeibRNXp.', '2023-07-31 06:33:39', NULL, NULL, '2023-07-25 03:07:04', '2023-12-22 07:51:45', 'manager', 'perorangan', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895-4217-35441', 'on', NULL, NULL, NULL, '123456', '123456', '506', '1698207997.jpg', '<p>fsdfsdfdsfs</p>', '1'),
(24, 'staff', 'staff@gmail.com', 'staff', '$2y$10$h.zowDsg0SrwjE3psUAkF..YsqQjbp.dkCYo.KdtQuTYjSOuWAuNW', '2023-08-11 21:17:32', NULL, NULL, '2023-07-25 03:44:11', '2023-10-25 11:21:44', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'azlinda', 'azlinda@gmail.com', 'azlinda', '$2y$10$Yaw3qwIOAQsTMwdY0qJQn.klaPKl4geCNaSSF7jQac0yElipRNBQO', '2023-07-26 08:16:30', NULL, NULL, '2023-07-26 08:04:55', '2023-07-26 08:16:30', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', 'off', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'tessss', 'tess@gmail.com', 'tessss', '$2y$10$/UhG6LTus26zty/etTOLJuz6uQXscUje20//4G4nhkZ8Bj0jnUY5C', NULL, NULL, NULL, '2023-07-26 09:36:58', '2023-07-26 09:36:58', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'ebiiiiiii', 'ebi@gmail.com', 'ebiiiiiii', '$2y$10$5l3hOxKAKM4aVeluGt1Ibe557kCwiTT/AIJGOOv7ZyZSu2VSUVlSu', '2023-07-27 08:59:18', NULL, NULL, '2023-07-27 08:52:45', '2023-10-25 11:21:44', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'jonii', 'joni@gmail.com', 'jonii', '$2y$10$khOUus0zo4B/CqPvuNhEoOipDxBfwaDdHDezUGxTN1aUufl1VG77y', NULL, NULL, NULL, '2023-07-27 08:56:54', '2023-07-27 08:56:54', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'staff2', 'staff2@gmail.com', 'staff2', '$2y$10$OWQko./89s/ZWRBVlps0pu0ui1uvdHtH9aAurgY.IpeNbhbLn7V4.', '2023-07-28 21:47:25', NULL, NULL, '2023-07-28 21:45:31', '2023-10-25 11:21:44', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'yolanda', 'yola@gmail.com', 'yolanda', '$2y$10$eDJhpUHPafy6B.2t9Q9NEeG91IV3FF0LdNI5L6D0O.jSDIci.lDlO', '2023-08-22 08:20:24', NULL, NULL, '2023-07-29 02:03:59', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '441', NULL, NULL, NULL),
(45, 'hafid', 'hafid@gmail.com', 'hafid', '$2y$10$lEdZ9612KDsk1dlojVB/2uIy3Wy6eSLQEN.IyCftAdCL/DDGdrevW', NULL, NULL, NULL, '2023-07-29 02:45:46', '2023-07-29 02:45:46', 'users', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'ipann', 'ipan@gmai.com', 'ipann', '$2y$10$42AuxNDJ6GMD8Q6SnkM/uenpiNOVhfzfx8iNFlrpYEWs.40SXZhPu', NULL, NULL, NULL, '2023-07-29 02:50:24', '2023-10-25 11:21:44', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895421735441', NULL, NULL, NULL, NULL, '123', '123', '1234', NULL, NULL, NULL),
(47, 'rental', 'rental@gmail.com', 'rental', '$2y$10$mccAiMPIyQ9J75.0u9Aee.JiJ.aNIObmC4FTOxmKFT9Cze4/Fhl7a', '2023-08-10 22:27:13', NULL, NULL, '2023-08-10 22:26:13', '2023-08-10 22:27:13', 'manager', 'penyewaan', 'rental', NULL, NULL, NULL, NULL, NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', 'junii', '$2y$10$mj9wIGi6jEKMHx4reastsugzygw1tDDB5xhpx7GgN9pwu93.jbpP6', '2023-08-11 21:00:30', NULL, NULL, '2023-08-11 20:59:31', '2023-10-25 10:11:09', 'staff', 'penyewaan', 'rsccc', NULL, NULL, NULL, NULL, NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'Muhammad Hafidhudin Saputra', 'karyawan@gmail.com', 'karyawan', '$2y$10$y3.aLjrFU3rMMvqLOJwvuukvomUkRlrbzjqfpmbfx0LHTYAKoFXoS', '2023-10-09 14:13:13', NULL, NULL, '2023-08-12 03:36:07', '2023-12-21 16:41:18', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '+62895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '014', NULL, '<p>dasdasdasdasdasdasdasda</p>', '6'),
(50, 'baimm', 'baim@gmail.com', 'baimm', '$2y$10$7AZeYgj8Gvd8.w1C96Q.3uirSviRB0qyETZRYHEkQj.fIRpZ.sn6a', NULL, NULL, NULL, '2023-08-14 05:00:37', '2023-10-25 11:21:44', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '123456789012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'ida ervi', 'erviida@gmail.com', 'ida ervi', '$2y$10$fT6sS53/0rkj7wZJI/gv7ea3OaA1tONWWq0xOGhZ0som8UMtG2Cc2', NULL, NULL, NULL, '2023-09-21 15:29:16', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895415165136', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'fertika', 'fertika@gmail.com', 'fertika', '$2y$10$HaneVoc.ox3l2SAjhfXOJOxzTgMSzGE29Y0pkNb9z9bV5ISGlA7T.', '2023-09-21 15:55:46', NULL, NULL, '2023-09-21 15:50:47', '2023-09-21 15:55:46', 'admin', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '08951616516', 'on', NULL, NULL, NULL, '54564', '54654', '022', NULL, NULL, NULL),
(54, 'dwilestari', 'dwilestari@gmail.com', 'dwilestari', '$2y$10$iPcdzrUAHs156Fbq6.G8m.kpYvHbDIWXFTSDTjA4EaNDYxG0eVSVy', '2023-09-21 16:01:49', NULL, NULL, '2023-09-21 15:59:32', '2023-09-21 16:01:49', 'users', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '45646546', 'on', NULL, NULL, NULL, '123456', '123456', '200', NULL, NULL, NULL),
(55, 'elsii', 'elsi@gmail.com', 'elsii', '$2y$10$eYMkVKr1XcH8cDnQY1HkZuGgBh9lxZTTYPmHb2WwGepZSC0.0Srfq', '2023-10-09 07:21:22', NULL, NULL, '2023-10-09 07:19:58', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0099888979', 'on', NULL, NULL, NULL, '6675757575675756', '8687687678678', '111', NULL, NULL, NULL),
(56, 'awdawdadwada', 'berto@gmail.com', 'awdadawdaw', '$2y$10$cnsyGIBkangXYxFkYvjVpuD0MHrOLw9cNqP6vHYY0VT3406.X5A/2', NULL, NULL, NULL, '2023-10-09 13:53:46', '2023-10-09 13:53:46', 'karyawan', 'bisnis', 'dawdawdawdaw', NULL, NULL, NULL, NULL, NULL, '13231231231', 'off', NULL, NULL, NULL, '123456', '123456', '009', NULL, NULL, NULL),
(57, 'tessssssssssssssssssssssssss', 'fsdfsdfsdfsdfsdfsd@gmail.com', 'fsefsfdfsdfsdfs', '$2y$10$FwEvny9cPKx58JHwg6gD/eCKXgk3ZK481uCgjPKu.vDRxt4kgHvCi', NULL, NULL, NULL, '2023-10-09 14:14:53', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0896842903641', 'off', NULL, NULL, NULL, '123456', '123456', '484', NULL, '<p>adwdawdawdawd</p>', NULL),
(58, 'nyobbbbbbbb', 'nyobbbbbbb@gmail.com', 'nyobbbbbbbb', '$2y$10$AcnINV7OE0Ug1LTJE.7n0emdnhlwPousVIwOkBZBdlxvxj8V6ESPS', NULL, NULL, NULL, '2023-10-15 04:40:14', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0896842903641', 'off', NULL, NULL, NULL, '123456', '123456', '153', NULL, '<p>asdsadsadasdas</p>', NULL),
(59, 'ludiro', 'ludiro@gmail.com', 'ludiro', '$2y$10$DlKIqqmBaAvKFLZ3Ic0gXOuuzC7S1DJaV4FF5sL0JZxiKaIgXJtma', '2023-10-25 06:08:39', NULL, NULL, '2023-10-20 04:55:39', '2023-10-25 11:21:44', 'trainer', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895-4217-3544', 'on', NULL, NULL, NULL, '2313221312', '123121232423', '022', NULL, '<p>fsdfsdfsdfsdfsd</p>', NULL),
(60, 'desiii', 'desii@gmail.com', 'desiiii', '$2y$10$enj3TjJosVHrrjdTaO6HneYul/IQ8kvza6xYrm7e97Nnrw9qst1Bu', '2023-10-25 06:12:21', NULL, NULL, '2023-10-25 04:20:58', '2023-10-25 11:21:44', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '0895-4217-35441', 'bertojunikrisnanto@gmail.com', '1698213665.png', NULL, '0895-4217-35441', 'on', NULL, NULL, NULL, '2313221312', '12323232', '028', NULL, '<p>adsdasdasd</p>', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `camp`
--
ALTER TABLE `camp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `categories_credit`
--
ALTER TABLE `categories_credit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_credit_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_debit_user_id_foreign` (`user_id`);

--
-- Indeks untuk tabel `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `credit`
--
ALTER TABLE `credit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credit_user_id_foreign` (`user_id`),
  ADD KEY `credit_category_id_foreign` (`category_id`);

--
-- Indeks untuk tabel `debit`
--
ALTER TABLE `debit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debit_user_id_foreign` (`user_id`),
  ADD KEY `debit_category_id_foreign` (`category_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`id_transaksi`),
  ADD KEY `presensi_id` (`presensi_id`);

--
-- Indeks untuk tabel `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indeks untuk tabel `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indeks untuk tabel `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indeks untuk tabel `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indeks untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tambah_barang_id` (`tambah_barang_id`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indeks untuk tabel `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `tambah_barang`
--
ALTER TABLE `tambah_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `total`
--
ALTER TABLE `total`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`gaji_id`,`presensi_id`),
  ADD KEY `presensi_id` (`presensi_id`),
  ADD KEY `gaji_id` (`gaji_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `company` (`company`),
  ADD KEY `company_2` (`company`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `camp`
--
ALTER TABLE `camp`
  MODIFY `id` bigint(225) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `categories_credit`
--
ALTER TABLE `categories_credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT untuk tabel `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit`
--
ALTER TABLE `credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT untuk tabel `debit`
--
ALTER TABLE `debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT untuk tabel `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT untuk tabel `tambah_barang`
--
ALTER TABLE `tambah_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `total`
--
ALTER TABLE `total`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `camp`
--
ALTER TABLE `camp`
  ADD CONSTRAINT `camp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `categories_credit`
--
ALTER TABLE `categories_credit`
  ADD CONSTRAINT `categories_credit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  ADD CONSTRAINT `categories_debit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `company`
--
ALTER TABLE `company`
  ADD CONSTRAINT `company_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `credit`
--
ALTER TABLE `credit`
  ADD CONSTRAINT `credit_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories_credit` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `credit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `debit`
--
ALTER TABLE `debit`
  ADD CONSTRAINT `debit_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories_debit` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `debit_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `gaji`
--
ALTER TABLE `gaji`
  ADD CONSTRAINT `gaji_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gaji_ibfk_2` FOREIGN KEY (`presensi_id`) REFERENCES `presensi` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `maintenance`
--
ALTER TABLE `maintenance`
  ADD CONSTRAINT `maintenance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `penyewaan`
--
ALTER TABLE `penyewaan`
  ADD CONSTRAINT `penyewaan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `penyewaan_ibfk_2` FOREIGN KEY (`tambah_barang_id`) REFERENCES `tambah_barang` (`id`);

--
-- Ketidakleluasaan untuk tabel `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `presensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tambah_barang`
--
ALTER TABLE `tambah_barang`
  ADD CONSTRAINT `tambah_barang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `total`
--
ALTER TABLE `total`
  ADD CONSTRAINT `total_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `total_ibfk_2` FOREIGN KEY (`presensi_id`) REFERENCES `presensi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `total_ibfk_3` FOREIGN KEY (`gaji_id`) REFERENCES `gaji` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
