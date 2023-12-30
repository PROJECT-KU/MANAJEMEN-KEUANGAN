-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Des 2023 pada 04.24
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
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `camp`
--
ALTER TABLE `camp`
  MODIFY `id` bigint(225) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `camp`
--
ALTER TABLE `camp`
  ADD CONSTRAINT `camp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
