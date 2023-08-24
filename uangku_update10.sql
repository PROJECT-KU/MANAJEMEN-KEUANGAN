-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Agu 2023 pada 18.00
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
-- Struktur dari tabel `categories_credit`
--

CREATE TABLE `categories_credit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories_credit`
--

INSERT INTO `categories_credit` (`id`, `user_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'makan', '2023-07-13 00:58:56', '2023-07-13 00:58:56'),
(2, 1, 'minum', '2023-07-13 08:51:37', '2023-07-13 08:51:37'),
(4, 22, 'Evoluigi', '2023-07-25 08:46:35', '2023-07-25 08:46:35'),
(5, 22, 'manangercredit', '2023-07-25 19:02:27', '2023-07-25 20:09:12'),
(11, 22, 'credit staff', '2023-07-25 20:11:16', '2023-07-25 20:11:32'),
(12, 22, 'buat staff', '2023-07-25 20:11:51', '2023-07-25 20:11:51'),
(13, 3, 'KENDARAAN', '2023-07-27 00:46:20', '2023-07-28 22:02:10'),
(14, 3, 'PROJECT', '2023-07-27 22:32:13', '2023-07-27 22:32:13'),
(15, 3, 'MAKAN & MINUM', '2023-07-27 22:32:24', '2023-07-27 22:32:24'),
(16, 3, 'TAGIHAN', '2023-07-28 22:00:49', '2023-07-28 22:00:49'),
(17, 3, 'KESEHATAN', '2023-07-28 22:01:57', '2023-07-28 22:01:57'),
(18, 3, 'SKINCARE', '2023-07-30 20:01:39', '2023-07-30 20:01:39'),
(19, 3, 'UTANG PIUTANG', '2023-08-03 21:24:42', '2023-08-03 21:24:42'),
(20, 3, 'SMOKE', '2023-08-07 00:21:19', '2023-08-07 00:21:19'),
(21, 3, 'IURAN', '2023-08-07 00:22:08', '2023-08-07 00:22:08'),
(22, 3, 'BELANJA', '2023-08-09 07:43:22', '2023-08-09 07:43:22'),
(23, 49, 'KARYAWAN', '2023-08-13 03:19:54', '2023-08-13 03:19:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories_debit`
--

CREATE TABLE `categories_debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
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

INSERT INTO `categories_debit` (`id`, `user_id`, `name`, `created_at`, `updated_at`, `nama_barang`, `harga_barang`, `diskon`, `stok`) VALUES
(1, 1, 'makan', '2023-07-12 23:14:29', '2023-07-12 23:14:29', NULL, NULL, NULL, NULL),
(2, 1, 'minum', '2023-07-13 07:50:41', '2023-07-13 07:50:41', NULL, NULL, NULL, NULL),
(5, 24, 'berto', '2023-07-25 04:17:20', '2023-07-25 04:17:20', NULL, NULL, NULL, NULL),
(6, 24, 'staff', '2023-07-25 04:24:03', '2023-07-25 04:24:03', NULL, NULL, NULL, NULL),
(7, 22, 'nyoba', '2023-07-25 16:18:14', '2023-07-25 16:18:14', NULL, NULL, NULL, NULL),
(8, 22, 'manager', '2023-07-25 18:59:26', '2023-07-25 18:59:26', NULL, NULL, NULL, NULL),
(10, 22, 'staff new', '2023-07-25 19:57:58', '2023-07-25 19:57:58', NULL, NULL, NULL, NULL),
(12, 22, 'buat staf', '2023-07-25 20:07:05', '2023-07-25 20:07:05', NULL, NULL, NULL, NULL),
(14, 3, 'PROJECT', '2023-07-27 00:43:58', '2023-07-27 00:43:58', NULL, NULL, NULL, NULL),
(16, 3, 'BONUS GAJI', '2023-07-28 21:21:12', '2023-07-28 21:59:11', NULL, NULL, NULL, NULL),
(18, 3, 'HUTANG PIUTANG', '2023-07-30 21:07:00', '2023-07-30 21:07:00', NULL, NULL, NULL, NULL),
(19, 22, 'TESS', '2023-08-02 08:34:19', '2023-08-02 08:34:19', NULL, NULL, NULL, NULL),
(20, 3, 'GAJI', '2023-08-03 18:56:41', '2023-08-03 18:56:41', NULL, NULL, NULL, NULL),
(21, 49, 'SDASD', '2023-08-12 03:55:25', '2023-08-12 03:55:25', NULL, NULL, NULL, NULL),
(22, 49, 'KARYAWAN', '2023-08-13 02:18:18', '2023-08-13 02:18:18', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit`
--

CREATE TABLE `credit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `credit`
--

INSERT INTO `credit` (`id`, `user_id`, `category_id`, `nominal`, `description`, `credit_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10000, 'makan olive', '2023-07-13 12:00:00', '2023-07-13 00:59:11', '2023-07-13 00:59:11'),
(2, 1, 1, 5000, 'ngopi', '2023-07-13 12:00:00', '2023-07-13 00:59:24', '2023-07-13 00:59:24'),
(3, 1, 2, 50000, 'drgdrgdrg', '2023-07-13 08:00:00', '2023-07-13 08:51:51', '2023-07-13 08:51:51'),
(4, 1, 2, 10000, 'awdawdqawd', '2023-07-14 12:00:00', '2023-07-13 08:53:34', '2023-07-13 08:53:34'),
(6, 22, 5, 50000, 'wdadawdawdawd', '2023-07-26 12:00:00', '2023-07-25 19:19:27', '2023-07-25 19:19:27'),
(7, 22, 4, 10000, 'fsefsefsefs', '2023-07-26 12:00:00', '2023-07-25 19:24:32', '2023-07-25 19:24:32'),
(8, 22, 11, 50000, 'asdasdasda', '2023-07-26 12:00:00', '2023-07-25 20:11:43', '2023-07-25 20:11:43'),
(9, 24, 12, 2000, 'asdasdasdasd', '2023-07-26 12:00:00', '2023-07-25 20:12:10', '2023-07-25 20:12:10'),
(10, 3, 13, 25000, 'buat beli bensin', '2023-07-27 11:00:00', '2023-07-27 00:46:54', '2023-07-27 00:46:54'),
(11, 3, 14, 180500, 'komisi project ke febi', '2023-07-27 08:00:00', '2023-07-27 22:33:32', '2023-07-27 22:33:32'),
(12, 3, 13, 20000, 'beli bensin', '2023-07-28 07:00:00', '2023-07-27 22:34:02', '2023-07-27 22:34:02'),
(13, 3, 15, 10000, 'beliin ibu gorengan', '2023-07-27 09:00:00', '2023-07-27 22:34:35', '2023-07-27 22:34:35'),
(14, 3, 15, 60000, 'makan malam di pak gun sama febi', '2023-07-27 08:30:00', '2023-07-27 22:35:09', '2023-07-27 22:35:09'),
(15, 3, 16, 9500000, 'DP motor baru', '2023-07-28 12:00:00', '2023-07-28 22:01:22', '2023-07-28 22:01:22'),
(16, 3, 17, 35500, 'beli obat di k24', '2023-07-28 09:30:00', '2023-07-28 22:02:56', '2023-07-28 22:02:56'),
(17, 3, 15, 50000, 'kasih uang buat makan peby', '2023-07-28 09:30:00', '2023-07-28 22:03:35', '2023-07-28 22:03:35'),
(18, 3, 13, 2000, 'parkir di k24', '2023-07-28 09:30:00', '2023-07-28 22:04:08', '2023-07-28 22:04:08'),
(19, 3, 15, 11000, 'beli bear brand', '2023-07-29 08:00:00', '2023-07-29 02:52:27', '2023-07-29 02:52:27'),
(20, 3, 18, 206000, 'beli sabun muka dan mousterayzer skintific', '2023-07-31 06:36:00', '2023-07-30 20:03:00', '2023-07-30 20:03:00'),
(21, 3, 13, 30000, 'beli bensin motor baru #1', '2023-07-29 08:00:00', '2023-07-31 07:51:16', '2023-07-31 07:51:16'),
(22, 3, 13, 50000, 'beli bensin motor baru #2', '2023-07-31 12:00:00', '2023-07-31 07:51:45', '2023-07-31 07:51:45'),
(23, 3, 15, 19000, 'beli kopi kenangan', '2023-07-31 12:00:00', '2023-07-31 07:52:08', '2023-07-31 07:52:08'),
(33, 3, 15, 30000, 'beli bakso bakar nitip hapid', '2023-08-01 09:00:00', '2023-08-01 08:42:41', '2023-08-01 08:42:41'),
(34, 3, 13, 2000, 'parkir motor di jolie', '2023-08-01 07:00:00', '2023-08-01 08:43:07', '2023-08-01 08:43:07'),
(35, 3, 17, 40100, 'beli obat batuk di viva apotek', '2023-08-01 08:00:00', '2023-08-01 08:44:15', '2023-08-01 08:44:15'),
(36, 3, 19, 100000, 'utang ke feby', '2023-08-04 10:00:00', '2023-08-03 21:26:06', '2023-08-03 21:26:06'),
(37, 3, 15, 50000, 'kasih uang makan ke feby', '2023-08-04 10:00:00', '2023-08-03 21:26:37', '2023-08-03 21:26:37'),
(38, 3, 16, 70000, 'pembayaran spinjam', '2023-08-04 09:00:00', '2023-08-04 18:52:02', '2023-08-04 18:52:02'),
(39, 3, 13, 50000, 'beli bensin motor baru #3', '2023-08-05 08:00:00', '2023-08-04 18:52:36', '2023-08-08 21:47:35'),
(40, 3, 16, 45000, 'tagihan bulanan pembayaran icloud agustus', '2023-08-06 03:00:00', '2023-08-06 18:37:57', '2023-08-06 18:37:57'),
(41, 3, 20, 24000, 'beli rokok', '2023-08-07 11:00:00', '2023-08-07 00:21:39', '2023-08-07 00:21:39'),
(42, 3, 21, 200000, 'iuran buat beli kado anak nya mas cahyo', '2023-08-07 12:00:00', '2023-08-07 00:22:40', '2023-08-07 00:22:40'),
(43, 3, 16, 100000, 'buat bayar BPJS Agustus', '2023-08-07 01:00:00', '2023-08-07 00:23:14', '2023-08-07 00:23:14'),
(44, 3, 15, 3000, 'beli leker', '2023-08-07 02:00:00', '2023-08-07 00:23:32', '2023-08-07 00:23:32'),
(45, 3, 15, 76000, 'makan di richeese sama peby', '2023-08-07 06:50:00', '2023-08-08 21:45:14', '2023-08-08 21:45:14'),
(46, 3, 13, 2000, 'parkir motor di richeese', '2023-08-07 07:30:00', '2023-08-08 21:46:20', '2023-08-08 21:46:20'),
(47, 3, 13, 50000, 'beli bensin motor baru #4', '2023-08-08 07:00:00', '2023-08-08 21:47:11', '2023-08-08 21:48:08'),
(48, 3, 15, 20000, 'beli kopi kenangan di solo deket masjid al-aqsa mau ke karanganyar nengok anak mas cahyo', '2023-08-08 10:00:00', '2023-08-08 21:49:36', '2023-08-08 21:49:36'),
(49, 3, 15, 20000, 'beli buah stroberry di sarangan', '2023-08-08 01:00:00', '2023-08-08 21:50:17', '2023-08-08 21:50:17'),
(50, 3, 15, 10000, 'beli kripik di sarangan', '2023-08-08 01:00:00', '2023-08-08 21:50:50', '2023-08-08 21:50:50'),
(51, 3, 15, 34000, 'beli makan di pecel lele sidokabol sama peby', '2023-08-09 07:00:00', '2023-08-09 07:42:18', '2023-08-09 07:42:18'),
(52, 3, 13, 2000, 'parkir di pecel lele sidokabol', '2023-08-09 07:00:00', '2023-08-09 07:42:45', '2023-08-09 07:42:45'),
(53, 3, 22, 50000, 'beli sayuran di pasar giwangan', '2023-08-09 09:00:00', '2023-08-09 07:43:57', '2023-08-09 07:43:57'),
(54, 3, 16, 947500, 'cicilan motor #1', '2023-08-08 12:00:00', '2023-08-09 18:30:17', '2023-08-09 18:30:17'),
(55, 3, 15, 2500, 'beliin mba es teh', '2023-08-10 01:00:00', '2023-08-10 04:07:07', '2023-08-10 04:07:07'),
(56, 3, 13, 2000, 'parkir motor di lapangan karang makan mie ayam pak pendek', '2023-08-10 09:00:00', '2023-08-10 04:07:38', '2023-08-10 04:07:38'),
(57, 3, 15, 6000, 'buat beliin mba jus', '2023-08-10 12:00:00', '2023-08-12 04:54:08', '2023-08-12 04:54:08'),
(58, 3, 13, 2000, 'parkir motor di hartono', '2023-08-10 09:00:00', '2023-08-12 04:54:44', '2023-08-12 04:54:44'),
(59, 3, 15, 16000, 'beli roti bakar', '2023-08-10 09:00:00', '2023-08-12 04:55:17', '2023-08-12 04:55:17'),
(60, 3, 15, 32000, 'beliin baim chatime', '2023-08-10 09:00:00', '2023-08-12 04:56:21', '2023-08-12 04:56:21'),
(61, 3, 21, 40000, 'kekurangan iuran ke karanganyar', '2023-08-11 12:00:00', '2023-08-12 04:56:52', '2023-08-12 04:56:52'),
(62, 3, 13, 44000, 'beli bensin motor baru #5', '2023-08-12 07:00:00', '2023-08-12 04:57:41', '2023-08-12 04:57:41'),
(63, 49, 23, 10000, 'asfadsfdf', '2023-08-13 12:00:00', '2023-08-13 03:20:11', '2023-08-13 03:20:11'),
(64, 3, 15, 15000, 'beli leker', '2023-08-14 01:00:00', '2023-08-14 04:31:27', '2023-08-14 04:31:27'),
(65, 3, 15, 6000, 'beli tahu gejrot', '2023-08-14 09:00:00', '2023-08-14 08:03:39', '2023-08-14 08:03:39'),
(66, 3, 15, 9000, 'makan di angkringan', '2023-08-14 08:00:00', '2023-08-15 18:04:50', '2023-08-15 18:04:50'),
(67, 3, 22, 48000, 'isi ulang parfum', '2023-08-14 09:00:00', '2023-08-15 18:07:39', '2023-08-15 18:07:39'),
(68, 3, 22, 127517, 'belanja di pamella', '2023-08-14 08:00:00', '2023-08-15 18:09:18', '2023-08-15 18:09:18'),
(69, 3, 22, 45000, 'belanja cemilan di bintang niaga', '2023-08-15 08:00:00', '2023-08-15 18:10:59', '2023-08-15 18:10:59'),
(70, 3, 13, 2000, 'parkir motor di bintang niaga', '2023-08-15 08:00:00', '2023-08-15 18:12:33', '2023-08-15 18:12:33'),
(71, 3, 15, 20000, 'ngopi di westpash coffe kota gedhe', '2023-08-15 06:11:00', '2023-08-15 18:14:02', '2023-08-15 18:14:02'),
(72, 3, 13, 2000, 'parkir di westpash coffe kota gedhe', '2023-08-15 07:00:00', '2023-08-15 18:14:33', '2023-08-15 18:14:33'),
(73, 3, 18, 35000, 'potong rambut di barber os jec', '2023-08-15 08:15:00', '2023-08-15 18:16:39', '2023-08-15 18:16:39'),
(74, 3, 20, 24600, 'beli rokok di indomaret', '2023-08-15 06:00:00', '2023-08-15 18:17:10', '2023-08-15 18:17:10'),
(75, 3, 15, 12000, 'beli nasi goreng', '2023-08-15 09:00:00', '2023-08-15 18:17:55', '2023-08-15 18:17:55'),
(76, 22, 4, 50000, 'dfdfdsfsd', '2023-08-20 12:00:00', '2023-08-19 21:25:47', '2023-08-19 21:25:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `debit`
--

CREATE TABLE `debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `debit_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `debit`
--

INSERT INTO `debit` (`id`, `user_id`, `category_id`, `nominal`, `description`, `debit_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 50000, 'buat makana', '2023-07-13 01:14:00', '2023-07-12 23:15:03', '2023-07-12 23:15:03'),
(2, 1, 2, 5000, 'fasfasfas', '2023-07-13 12:00:00', '2023-07-13 07:50:54', '2023-07-13 07:50:54'),
(3, 1, 2, 50000, 'dasdasdasd', '2023-07-13 12:00:00', '2023-07-13 07:51:10', '2023-07-13 07:51:10'),
(4, 1, 1, 50000, 'grzgdrgrdgdrg', '2023-07-15 12:00:00', '2023-07-13 08:59:57', '2023-07-13 08:59:57'),
(5, 1, 2, 5000, 'rthsxhhrhrh', '2023-07-14 12:00:00', '2023-07-13 09:00:11', '2023-07-13 09:00:11'),
(6, 1, 1, 10000, 'jcgyjgyjgyjgy', '2023-07-15 12:00:00', '2023-07-13 09:00:24', '2023-07-13 09:00:24'),
(7, 1, 1, 5000, 'cjgjgyjgjgj', '2023-07-15 12:00:00', '2023-07-13 09:00:38', '2023-07-13 09:00:38'),
(8, 1, 2, 5000, 'jtjtdjtjtjjt', '2023-07-14 12:00:00', '2023-07-13 09:00:54', '2023-07-13 09:00:54'),
(9, 1, 1, 10000, 'tyjdjtjytjjt', '2023-07-15 12:00:00', '2023-07-13 09:01:08', '2023-07-13 09:01:08'),
(10, 1, 1, 10000, 'fykykkykyky', '2023-07-14 12:00:00', '2023-07-13 09:01:21', '2023-07-13 09:01:21'),
(11, 1, 2, 50000, 'jghjghjghjg', '2023-07-14 12:00:00', '2023-07-13 09:01:36', '2023-07-13 09:01:36'),
(12, 1, 1, 50000, 'hthdrthshshtrh', '2023-07-13 11:18:00', '2023-07-13 09:08:58', '2023-07-13 09:08:58'),
(15, 24, 5, 10000, 'awdawdaw', '2023-07-25 12:00:00', '2023-07-25 04:17:31', '2023-07-25 04:17:31'),
(16, 24, 6, 5000, 'hjfhjashdfjashjkdhkasjhdas', '2023-07-25 12:00:00', '2023-07-25 04:24:20', '2023-07-25 04:24:20'),
(17, 22, 6, 5000, 'eawerfawff', '2023-07-26 12:00:00', '2023-07-25 18:08:30', '2023-07-25 18:10:29'),
(18, 22, 8, 10000, 'dasdadasda', '2023-07-26 12:00:00', '2023-07-25 19:22:22', '2023-07-25 19:22:22'),
(20, 22, 10, 10000, 'dadasdas', '2023-07-26 12:00:00', '2023-07-25 20:06:05', '2023-07-25 20:06:16'),
(21, 24, 12, 100000, 'adfsdasdasdas1233585', '2023-07-26 12:00:00', '2023-07-25 20:07:23', '2023-07-25 20:07:34'),
(23, 3, 14, 500000, 'hasil uang project dari temen febi', '2023-07-26 08:00:00', '2023-07-27 00:45:54', '2023-07-27 00:45:54'),
(24, 3, 16, 300000, 'bonus gaji rumah scopus camp #48', '2023-07-29 12:00:00', '2023-07-28 21:27:52', '2023-07-28 21:27:52'),
(27, 3, 16, 300000, 'bonus gaji rumah scopus camp #49', '2023-07-31 10:00:00', '2023-07-30 20:00:55', '2023-07-30 20:00:55'),
(28, 3, 18, 100000, 'utang mba ria ganti uang kontrol bapak', '2023-07-31 11:00:00', '2023-07-30 21:07:31', '2023-07-30 21:07:31'),
(39, 3, 20, 2150000, 'gaji scopus bulan Agustus', '2023-08-03 12:00:00', '2023-08-03 18:57:34', '2023-08-03 18:57:34'),
(40, 3, 16, 300000, 'bonus gaji rumah scopus camp #50', '2023-08-08 10:00:00', '2023-08-08 21:44:02', '2023-08-08 21:44:02'),
(41, 22, 5, 10000, 'jjknkj', '2023-08-12 12:00:00', '2023-08-11 21:12:24', '2023-08-11 21:12:24'),
(42, 49, 21, 50000, 'sdsadsadasd', '2023-08-12 12:00:00', '2023-08-12 03:55:35', '2023-08-12 03:55:35'),
(43, 49, 22, 10000, 'bdhabdasd', '2023-08-13 12:00:00', '2023-08-13 02:18:31', '2023-08-13 02:18:31');

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
  `tanggal` datetime DEFAULT NULL,
  `potongan` varchar(100) NOT NULL,
  `total` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `gaji`
--

INSERT INTO `gaji` (`id`, `user_id`, `id_transaksi`, `gaji_pokok`, `lembur`, `lembur1`, `lembur2`, `lembur3`, `lembur4`, `lembur5`, `lembur6`, `lembur7`, `lembur8`, `lembur9`, `lembur10`, `jumlah_lembur`, `jumlah_lembur1`, `jumlah_lembur2`, `jumlah_lembur3`, `jumlah_lembur4`, `jumlah_lembur5`, `jumlah_lembur6`, `jumlah_lembur7`, `jumlah_lembur8`, `jumlah_lembur9`, `jumlah_lembur10`, `total_lembur`, `bonus`, `bonus1`, `bonus2`, `bonus3`, `bonus4`, `bonus5`, `bonus6`, `bonus7`, `bonus8`, `bonus9`, `bonus10`, `bonus_luar`, `bonus_luar1`, `bonus_luar2`, `bonus_luar3`, `bonus_luar4`, `bonus_luar5`, `bonus_luar6`, `bonus_luar7`, `bonus_luar8`, `bonus_luar9`, `bonus_luar10`, `jumlah_bonus`, `jumlah_bonus1`, `jumlah_bonus2`, `jumlah_bonus3`, `jumlah_bonus4`, `jumlah_bonus5`, `jumlah_bonus6`, `jumlah_bonus7`, `jumlah_bonus8`, `jumlah_bonus9`, `jumlah_bonus10`, `jumlah_bonus_luar`, `jumlah_bonus_luar1`, `jumlah_bonus_luar2`, `jumlah_bonus_luar3`, `jumlah_bonus_luar4`, `jumlah_bonus_luar5`, `jumlah_bonus_luar6`, `jumlah_bonus_luar7`, `jumlah_bonus_luar8`, `jumlah_bonus_luar9`, `jumlah_bonus_luar10`, `total_bonus`, `tunjangan`, `tanggal`, `potongan`, `total`, `created_at`, `status`, `note`, `updated_at`) VALUES
(1, 40, 'ZHR11', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, '', NULL, '2023-08-11 02:39:35', 'pending', NULL, '2023-08-11 02:39:35'),
(2, 44, '74HRV', '50000', '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, '', NULL, '2023-08-11 03:05:30', 'pending', NULL, '2023-08-11 03:05:30'),
(3, 46, 'GI430', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, '', NULL, '2023-08-11 03:43:07', 'pending', NULL, '2023-08-11 03:43:07'),
(4, 40, 'MHXBJ', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', '2011-08-11 12:00:00', '', NULL, '2023-08-11 03:53:56', 'pending', NULL, '2023-08-11 03:53:56'),
(5, 44, 'ZZJK9', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '130000', '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '150000', '100000', '2011-08-11 08:00:00', '100000', '2280000', '2023-08-11 05:14:01', 'pending', NULL, '2023-08-11 05:14:01'),
(6, 48, 'UN9EL', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '130000', '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '150000', '100000', '2012-08-20 12:00:00', '100000', '2280000', '2023-08-11 21:03:08', 'pending', NULL, '2023-08-11 21:03:08'),
(7, 46, 'NR950', '2000000', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '60000', '100000', '2012-08-20 12:00:00', '10000', '2150000', '2023-08-11 22:17:22', 'pending', NULL, '2023-08-11 22:17:22'),
(8, 46, 'GYMEL', '2500000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '40000', '100000', '2012-08-20 12:00:00', '100000', '3540000', '2023-08-11 22:42:03', 'pending', NULL, '2023-08-12 03:31:20'),
(14, 49, 'RZEA2', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '120000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '450000', '100000', '2012-08-12 08:00:00', '100000', '2570000', '2023-08-12 03:58:49', 'pending', NULL, '2023-08-12 03:58:49'),
(15, 49, 'W87N3', '3000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2013-08-13 01:00:00', '10000', '3990000', '2023-08-12 20:42:45', 'pending', NULL, '2023-08-12 20:42:45'),
(16, 49, 'INO7L', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '350000', '100000', '2023-08-14 12:00:00', '100000', '2950000', '2023-08-14 08:00:57', 'pending', NULL, '2023-08-14 08:00:57'),
(17, 49, 'NV07W', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '100000', '3650000', '2023-08-14 18:47:24', 'pending', NULL, '2023-08-14 18:47:24'),
(18, 49, 'WX24U', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1030000', '100000', '2023-08-15 12:00:00', '50000', '3680000', '2023-08-14 18:54:58', 'pending', NULL, '2023-08-14 18:54:58'),
(19, 49, 'X0TZW', '3000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1250000', '10000', '2023-08-15 12:00:00', '50000', '4310000', '2023-08-14 18:56:12', 'pending', NULL, '2023-08-14 18:56:12'),
(20, 49, 'IUP0O', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1030000', '10000', '2023-08-15 12:00:00', '10000', '3630000', '2023-08-14 18:59:03', 'pending', NULL, '2023-08-14 18:59:03'),
(21, 49, 'Y768O', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '50000', '1120000', '2023-08-14 18:59:53', 'pending', NULL, '2023-08-14 18:59:53'),
(22, 49, 'Q0UD3', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '10000', '3650000', '2023-08-14 19:03:45', 'pending', NULL, '2023-08-14 19:03:45'),
(23, 49, '3BUN4', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '100000', '3650000', '2023-08-14 19:06:56', 'pending', NULL, '2023-08-14 19:06:56'),
(24, 49, 'A5T81', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1030000', '10000', '2023-08-15 12:00:00', '50000', '3590000', '2023-08-14 19:10:11', 'pending', NULL, '2023-08-14 19:10:11'),
(25, 49, 'H9WV1', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '100000', '3150000', '2023-08-14 19:12:20', 'pending', NULL, '2023-08-14 19:12:20'),
(26, 49, '0GE84', '50000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '350000', '10000', '2023-08-15 12:00:00', '10000', '500000', '2023-08-14 19:13:40', 'pending', NULL, '2023-08-14 19:13:40'),
(27, 49, 'OPEFG', '3000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '10000', '4150000', '2023-08-14 19:14:14', 'pending', NULL, '2023-08-14 19:14:14'),
(28, 49, 'K1B2G', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1030000', '10000', '2023-08-15 12:00:00', '100000', '3540000', '2023-08-14 19:18:41', 'pending', NULL, '2023-08-14 19:18:41'),
(29, 49, 'BAYK7', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '50000', '3200000', '2023-08-14 19:23:54', 'pending', NULL, '2023-08-14 19:23:54'),
(30, 49, 'C4ECN', '3000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '50000', '4200000', '2023-08-14 19:27:21', 'pending', NULL, '2023-08-14 19:27:21'),
(31, 49, 'ZBG4G', '3000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10000', '10000', '2023-08-15 12:00:00', '10000', '3110000', '2023-08-14 19:28:31', 'pending', NULL, '2023-08-14 19:31:45'),
(32, 49, 'KZIMW', '2500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '50000', '3610000', '2023-08-15 00:34:04', 'pending', NULL, '2023-08-15 00:34:04'),
(33, 49, 'VKQDW', '2500000', '50000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '500000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '100000', '3960000', '2023-08-15 00:35:16', 'pending', NULL, '2023-08-15 00:35:16'),
(34, 49, 'MA57U', '2000000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '100000', '3060000', '2023-08-15 00:52:52', 'pending', NULL, '2023-08-15 00:52:52'),
(35, 49, 'CKGGR', '2000000', '\"10,000\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '\"10\"', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', '0', '2023-08-15 12:00:00', '0', '2100000', '2023-08-15 01:05:29', 'pending', NULL, '2023-08-15 01:05:29'),
(36, 49, 'X7QQ9', '2000000', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '100000', '3160000', '2023-08-15 08:36:33', 'pending', NULL, '2023-08-15 08:36:33'),
(37, 49, 'X6AQ6', '2000000', '10000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '200000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '10000', '2023-08-15 12:00:00', '100000', '3160000', '2023-08-15 08:38:20', 'pending', NULL, '2023-08-15 08:38:20'),
(38, 49, '0N2DY', '2000000', '10000', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', '0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '100000', '3150000', '2023-08-15 08:40:50', 'pending', NULL, '2023-08-15 08:40:50'),
(39, 49, 'WT59K', '2000000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '1100000', '10000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1050000', '100000', '2023-08-15 12:00:00', '100000', '4150000', '2023-08-15 09:48:03', 'pending', NULL, '2023-08-15 09:48:03'),
(41, 49, 'ULZDR', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2023-08-16 12:00:00', '100000', '2900000', '2023-08-15 18:36:20', 'pending', NULL, '2023-08-15 18:36:20'),
(42, 49, 'Q6HXU', '2000000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '0', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '0', '1000000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2023-08-16 12:00:00', '100000', '3800000', '2023-08-15 18:40:59', 'pending', NULL, '2023-08-15 18:40:59'),
(43, 49, 'PUDPV', '2000000', '10000', '0', '0', '10000', '10000', '10000', '10000', '0', '0', '10000', '0', '10', '0', '0', '10', '10', '10', '10', '0', '0', '10', '0', '600000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2023-08-16 12:00:00', '100000', '3400000', '2023-08-15 18:43:15', 'pending', NULL, '2023-08-15 18:43:15'),
(44, 49, 'BR2W3', '2000000', '10000', '0', '0', '10000', '10000', '10000', '10000', '10000', '10000', '0', '0', '10', '0', '0', '10', '10', '10', '10', '10', '10', '0', '0', '700000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2023-08-16 12:00:00', '100000', '3500000', '2023-08-15 20:42:09', 'pending', NULL, '2023-08-15 20:42:09'),
(45, 49, 'GAE6I', '2000000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '650000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '800000', '100000', '2023-08-16 12:00:00', '100000', '3450000', '2023-08-15 21:09:49', 'pending', NULL, '2023-08-15 21:09:49'),
(46, 49, 'ZRREA', '2000000', '10000', '10000', '10000', '0', '10000', '10000', '0', '0', '10000', '10000', '10000', '10', '1', '2', '0', '4', '5', '0', '0', '8', '9', '10', '490000', '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '100000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1500000', '100000', '2023-08-16 12:00:00', '100000', '3990000', '2023-08-15 21:11:39', 'pending', NULL, '2023-08-15 21:11:39'),
(47, 49, 'BGDB7', '2000000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '100000', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '2000000', '100000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '3', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '5', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '800000', '100000', '2023-08-16 12:00:00', '100000', '4800000', '2023-08-15 21:19:59', 'terbayar', NULL, '2023-08-22 08:15:14'),
(48, 49, 'Q5X9F', '2000000', '10000', '0', '10000', '10000', '0', '0', '10000', '10000', '100000', '100000', '0', '10', '0', '10', '10', '0', '0', '10', '10', '10', '10', '0', '2500000', '100000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '3', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '5', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '800000', '100000', '2023-08-16 12:00:00', '100000', '5300000', '2023-08-15 21:21:20', 'terbayar', NULL, '2023-08-22 08:15:01'),
(49, 49, '0I3LX', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '2200000', '100000', '2023-08-16 12:00:00', '100000', '4300000', '2023-08-16 05:03:19', 'terbayar', NULL, '2023-08-22 08:14:48'),
(50, 49, '8G5D9', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '10000', '10000', '10000', '0', '10000', '10000', '10000', '10000', '0', '10000', '0', '10000', '10000', '10000', '0', '10000', '10000', '10000', '10000', '0', '10', '0', '10', '10', '10', '0', '10', '10', '10', '10', '0', '10', '0', '10', '10', '10', '0', '10', '10', '10', '10', '0', '1600000', '100000', '2023-08-16 12:00:00', '100000', '3700000', '2023-08-16 05:06:16', 'terbayar', NULL, '2023-08-22 08:14:33'),
(51, 49, '8P8JO', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '10000', '0', '10000', '0', '0', '0', '10000', '10000', '0', '10000', '0', '10000', '0', '10000', '0', '0', '0', '10000', '10000', '0', '10', '0', '10', '0', '10', '0', '0', '0', '10', '10', '0', '10', '0', '10', '0', '10', '0', '0', '0', '10', '10', '0', '1000000', '100000', '2023-08-16 12:00:00', '100000', '3100000', '2023-08-16 05:09:01', 'terbayar', NULL, '2023-08-22 08:14:25'),
(52, 49, 'WM48Z', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '20000', '100000', '2023-08-17 12:00:00', '100000', '2030000', '2023-08-16 18:42:17', 'terbayar', NULL, '2023-08-22 08:14:16'),
(53, 49, 'JJNSP', '2000000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '0', '0', '0', '0', '0', '0', '0', '0', '0', '30000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '0', '0', '0', '0', '0', '0', '0', '0', '0', '60000', '10000', '2023-08-17 12:00:00', '10000', '2090000', '2023-08-16 18:43:10', 'terbayar', NULL, '2023-08-22 08:14:07'),
(54, 49, '4Y2ZI', '0', '10000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '3', '0', '0', '0', '0', '0', '0', '0', '0', '60000', '10000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '10000', '10000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '3', '0', '0', '0', '0', '0', '0', '0', '0', '1', '2', '3', '0', '0', '0', '0', '0', '0', '0', '0', '120000', '0', '2023-08-17 12:00:00', '0', '180000', '2023-08-16 18:46:19', 'terbayar', NULL, '2023-08-22 08:13:57'),
(55, 49, 'YICQS', '0', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '110000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '10000', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '220000', '0', '2023-08-17 12:00:00', '0', '330000', '2023-08-16 18:49:08', 'terbayar', NULL, '2023-08-22 08:13:45'),
(56, 49, '96YCA', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '100000', '2023-08-20 12:00:00', '100000', '2200000', '2023-08-19 20:41:06', 'terbayar', NULL, '2023-08-22 08:13:33'),
(57, 50, 'ZQ8DP', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '100000', '2023-08-21 12:00:00', '100000', '2200000', '2023-08-21 01:01:44', 'pending', NULL, '2023-08-20 01:01:44'),
(58, 46, 'DTB3I', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '100000', '2023-08-01 12:00:00', '100000', '2200000', '2023-08-20 01:26:20', 'pending', NULL, '2023-08-20 01:26:20'),
(59, 49, 'I0HLF', '2000000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '10000', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '10', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '100000', '100000', '2023-08-22 12:00:00', '100000', '2200000', '2023-08-22 04:53:03', 'terbayar', '<ul>\r\n	<li>dawdawdawdaw</li>\r\n	<li>dawdawdawda</li>\r\n	<li>adawdawdawdawdawdada</li>\r\n	<li>dfsdfgsdg123</li>\r\n</ul>', '2023-08-22 07:43:20');

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
('bertojunikrisnanto@gmail.com', '$2y$10$j0TR5.S5PmNwxX4CRsEi0.N5lDT7caU36wkwIyUbeCn5ccY34gKEa', '2023-08-13 20:54:36');

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
  `note` text DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL,
  `lokasi` varchar(300) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `presensi`
--

INSERT INTO `presensi` (`id`, `user_id`, `status`, `note`, `gambar`, `lokasi`, `created_at`, `updated_at`) VALUES
(1, 22, 'pulang cepat', '<p>adadasdasdasdasdasd</p>', '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-08-23 08:07:33', '2023-08-23 01:07:33'),
(2, 22, 'hadir', '<p>jkkgkgkhgjhgj</p>', '9d2c70b4e3768970941f067f8b96288b.jpg', NULL, '2023-08-23 08:10:20', '2023-08-23 01:10:20'),
(3, 22, 'luar kota', '\r\n	ffdgdfgdfgdfdg\r\n	dfgdfgfdgdf\r\n', '9d2c70b4e3768970941f067f8b96288b.jpg', NULL, '2023-08-23 08:12:07', '2023-08-23 01:12:07'),
(4, 22, 'remote', '<p>dssadasdasdasda</p>', NULL, NULL, '2023-08-23 08:19:41', '2023-08-23 01:19:41'),
(5, 22, 'hadir', '<p>dfsdfdsf</p>', '9d2c70b4e3768970941f067f8b96288b.jpg', NULL, '2023-08-23 08:22:10', '2023-08-23 01:22:10'),
(6, 22, 'cuti', '<p>adsadasdasd</p>', NULL, NULL, '2023-08-23 08:26:32', '2023-08-23 01:26:32'),
(7, 22, 'pulang cepat', '<p>sdfsdfdsfdsfsdfsd</p>', 'assets/img/presensi/1692779515.jpg', NULL, '2023-08-23 08:31:56', '2023-08-23 01:31:56'),
(8, 22, 'hadir', '<p>tes</p>', 'assets/img/presensi/1692779736.jpg', NULL, '2023-08-23 08:35:36', '2023-08-23 01:35:36'),
(9, 22, 'hadir', '<p>nyoba</p>', 'storage/assets/img/presensi/1692780071.png', NULL, '2023-08-23 08:41:11', '2023-08-23 01:41:11'),
(10, 22, 'hadir', '<p>adasda123</p>', '1692780777.png', NULL, '2023-08-23 08:52:57', '2023-08-23 01:52:57'),
(11, 22, 'pulang cepat', '<p>dzdfsfsdfsdfsd</p>', '1692780844.jpg', NULL, '2023-08-23 08:54:04', '2023-08-23 01:54:04'),
(12, 22, 'hadir', '<p>HADIR YA</p>', '1692781163.jpg', NULL, '2023-08-23 08:59:23', '2023-08-23 01:59:23'),
(13, 22, 'remote', '<p>ghjghjghjghj 123</p>', '1692801619.jpg', 'Unknown', '2023-08-23 13:53:06', '2023-08-23 07:40:19'),
(15, 49, 'pulang', '<p>sdfsdfsdfsdf</p>', '1692802152.png', 'Unknown', '2023-08-23 14:49:12', '2023-08-23 07:49:12'),
(16, 49, 'izin', '<p>sasdasd</p>', '1692802702.jpg', 'Unknown', '2023-08-23 14:58:22', '2023-08-23 07:58:22'),
(17, 49, 'pulang cepat', '<p>adsadasdasd</p>', '1692802823.jpg', 'Unknown', '2023-08-23 15:00:23', '2023-08-23 08:00:23'),
(18, 49, 'luar kota', '<p>sdasdasdasd</p>', '1692802867.jpg', 'Unknown', '2023-08-23 15:01:07', '2023-08-23 08:01:07'),
(20, 49, 'terlambat', '<p>cuti</p>', '1692806187.jpg', 'Unknown', '2023-08-23 22:56:27', '2023-08-23 15:56:27'),
(21, 49, 'terlambat', '<p>adasdasdasdasd</p>', '1692806375.jpg', 'Unknown', '2023-08-23 22:59:35', '2023-08-23 15:59:35');

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
  `telp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notif` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenggat` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norek` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `email_verified_at`, `avatar`, `remember_token`, `created_at`, `updated_at`, `level`, `jenis`, `company`, `telp`, `status`, `notif`, `tenggat`, `title`, `nik`, `norek`, `bank`, `gambar`) VALUES
(1, 'Berto Juni', 'bertojuni@gmail.com', 'bertojuni', 'junijuni008', NULL, '898192462.png', NULL, NULL, NULL, 'user', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'bertojunik', 'bertojunijuni@gmail.com', 'bertojunik', '$2y$10$wwknRoFVyEgG51kO0sxICepdHSjf5Dvd2QdxKkOexM.XyMEddihS.', '2023-07-15 22:14:15', NULL, NULL, '2023-07-15 01:15:22', '2023-07-15 22:14:15', 'admin', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'junijuni', 'juni@gmail.com', 'juni', '$2y$10$LCf6VN3S7vpf0aw87rdG7ursTKa7gide8ziPUl64P5stnbdd9CeyC', NULL, NULL, NULL, '2023-07-15 03:50:27', '2023-07-15 03:50:27', '', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'AJIROHMAT', 'aji@gmail.com', 'aji123', '$2y$10$bq1CweJhPPSviFZT7AmyFufN6J0rm2ojJJ1LTV62UCxG.QNka6l.6', NULL, NULL, NULL, '2023-07-15 04:30:17', '2023-07-15 08:16:08', 'bisnis', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'admin', 'admin@gmail.com', 'admin', '$2y$10$HfeNu60ry3y0iMqe48LITubOI0dIVYZAHshOmlY6D5EF8KLl2/4hG', '2023-07-15 22:28:38', NULL, NULL, '2023-07-15 22:28:38', '2023-07-15 22:28:38', 'admin', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'user', 'user@gmail.com', 'user', '$2y$10$1LbprO4sqJha0rQYVq.Zp.2lZDVRXkbNqhs2qVWfY/ldSIqbTxwYu', NULL, NULL, NULL, '2023-07-15 22:29:08', '2023-07-15 22:29:08', 'user', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'bisnis', 'bisnis@gmail.com', 'bisnis', '$2y$10$5i1VcojhcIe.d17xyxMkYu/w5B9IbxWR4YbHCAfoGb6Dm./CxM262', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:31:55', '2023-07-15 22:31:55', 'bisnis', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'user2', 'user2@gmail.com', 'user2', '$2y$10$ij09uJWwUSED1xesf2nyceme9MlQyrL7L9ObwwIxEDlpHZGRL5fKG', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:44:41', '2023-07-15 22:44:41', 'user', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'admin2', 'admin2@gmail.com', 'admin2', '$2y$10$ZXWl8md4Tts7TtkQSBgLR.R7qmrhj6LmxgVJN9joaLHVmPQQsxNQu', '2023-07-16 07:48:39', NULL, NULL, '2023-07-16 07:48:39', '2023-07-16 07:48:39', 'admin', '', '', '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'manager', 'manager@gmail.com', 'manager', '$2y$10$fIOHMi7XbpMrX1774/gEJevQiasVLwo0KMZ307nplhMioeibRNXp.', '2023-07-31 06:33:39', NULL, NULL, '2023-07-25 03:07:04', '2023-08-23 03:34:09', 'manager', 'perorangan', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '506', '1692786849.jpg'),
(24, 'staff', 'staff@gmail.com', 'staff', '$2y$10$h.zowDsg0SrwjE3psUAkF..YsqQjbp.dkCYo.KdtQuTYjSOuWAuNW', '2023-08-11 21:17:32', NULL, NULL, '2023-07-25 03:44:11', '2023-08-11 21:17:32', 'staff', 'bisnis', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'azlinda', 'azlinda@gmail.com', 'azlinda', '$2y$10$Yaw3qwIOAQsTMwdY0qJQn.klaPKl4geCNaSSF7jQac0yElipRNBQO', '2023-07-26 08:16:30', NULL, NULL, '2023-07-26 08:04:55', '2023-07-26 08:16:30', 'user', 'perorangan', NULL, '0895421735441', 'off', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'tessss', 'tess@gmail.com', 'tessss', '$2y$10$/UhG6LTus26zty/etTOLJuz6uQXscUje20//4G4nhkZ8Bj0jnUY5C', NULL, NULL, NULL, '2023-07-26 09:36:58', '2023-07-26 09:36:58', 'user', 'perorangan', NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'ebiiiiiii', 'ebi@gmail.com', 'ebiiiiiii', '$2y$10$5l3hOxKAKM4aVeluGt1Ibe557kCwiTT/AIJGOOv7ZyZSu2VSUVlSu', '2023-07-27 08:59:18', NULL, NULL, '2023-07-27 08:52:45', '2023-07-27 08:59:18', 'staff', 'bisnis', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'jonii', 'joni@gmail.com', 'jonii', '$2y$10$khOUus0zo4B/CqPvuNhEoOipDxBfwaDdHDezUGxTN1aUufl1VG77y', NULL, NULL, NULL, '2023-07-27 08:56:54', '2023-07-27 08:56:54', 'user', 'perorangan', NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'staff2', 'staff2@gmail.com', 'staff2', '$2y$10$OWQko./89s/ZWRBVlps0pu0ui1uvdHtH9aAurgY.IpeNbhbLn7V4.', '2023-07-28 21:47:25', NULL, NULL, '2023-07-28 21:45:31', '2023-07-28 21:47:25', 'staff', 'bisnis', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'yolanda', 'yola@gmail.com', 'yolanda', '$2y$10$eDJhpUHPafy6B.2t9Q9NEeG91IV3FF0LdNI5L6D0O.jSDIci.lDlO', '2023-08-22 08:20:24', NULL, NULL, '2023-07-29 02:03:59', '2023-08-22 08:20:24', 'karyawan', 'bisnis', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '441', NULL),
(45, 'hafid', 'hafid@gmail.com', 'hafid', '$2y$10$lEdZ9612KDsk1dlojVB/2uIy3Wy6eSLQEN.IyCftAdCL/DDGdrevW', NULL, NULL, NULL, '2023-07-29 02:45:46', '2023-07-29 02:45:46', 'users', 'perorangan', NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'ipann', 'ipan@gmai.com', 'ipann', '$2y$10$42AuxNDJ6GMD8Q6SnkM/uenpiNOVhfzfx8iNFlrpYEWs.40SXZhPu', NULL, NULL, NULL, '2023-07-29 02:50:24', '2023-07-29 02:50:24', 'staff', 'bisnis', 'rumahscopus', '0895421735441', NULL, NULL, NULL, NULL, '123', '123', '1234', NULL),
(47, 'rental', 'rental@gmail.com', 'rental', '$2y$10$mccAiMPIyQ9J75.0u9Aee.JiJ.aNIObmC4FTOxmKFT9Cze4/Fhl7a', '2023-08-10 22:27:13', NULL, NULL, '2023-08-10 22:26:13', '2023-08-10 22:27:13', 'manager', 'penyewaan', 'rental', '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', 'junii', '$2y$10$mj9wIGi6jEKMHx4reastsugzygw1tDDB5xhpx7GgN9pwu93.jbpP6', '2023-08-11 21:00:30', NULL, NULL, '2023-08-11 20:59:31', '2023-08-11 21:00:30', 'staff', 'penyewaan', 'rsccc', '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'karyawan', 'karyawan@gmail.com', 'karyawan', '$2y$10$y3.aLjrFU3rMMvqLOJwvuukvomUkRlrbzjqfpmbfx0LHTYAKoFXoS', '2023-08-21 09:28:08', NULL, NULL, '2023-08-12 03:36:07', '2023-08-21 09:28:08', 'karyawan', 'bisnis', 'rumahscopus', '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '014', NULL),
(50, 'baimm', 'baim@gmail.com', 'baimm', '$2y$10$7AZeYgj8Gvd8.w1C96Q.3uirSviRB0qyETZRYHEkQj.fIRpZ.sn6a', NULL, NULL, NULL, '2023-08-14 05:00:37', '2023-08-14 05:00:37', 'staff', 'bisnis', 'rumahscopus', '123456789012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

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
  ADD KEY `user_id` (`user_id`,`id_transaksi`);

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
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `categories_credit`
--
ALTER TABLE `categories_credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `credit`
--
ALTER TABLE `credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT untuk tabel `debit`
--
ALTER TABLE `debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

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
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `tambah_barang`
--
ALTER TABLE `tambah_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

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
  ADD CONSTRAINT `gaji_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
