-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Okt 2023 pada 09.22
-- Versi server: 10.4.28-MariaDB
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
  `kode` varchar(300) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
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
(14, 22, 'CM003', 'CREDIT MANAGER2', '2023-10-06 10:26:29', '2023-10-06 10:26:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories_debit`
--

CREATE TABLE `categories_debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kode` varchar(300) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nama_barang` varchar(300) DEFAULT NULL,
  `harga_barang` varchar(300) DEFAULT NULL,
  `diskon` varchar(300) DEFAULT NULL,
  `stok` varchar(300) DEFAULT NULL
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
(25, 22, 'DM003', 'DEBIT MANAGER 2', '2023-10-06 09:16:24', '2023-10-06 09:16:24', NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit`
--

CREATE TABLE `credit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` varchar(300) DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text NOT NULL,
  `credit_date` datetime NOT NULL,
  `gambar` varchar(300) DEFAULT NULL,
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
(90, 24, 14, 'GN8II', 10123, 'asbdjbasdas1234560000000tesssssssss', '2023-10-06 12:00:00', NULL, '2023-10-06 10:34:40', '2023-10-06 10:50:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `debit`
--

CREATE TABLE `debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `id_transaksi` varchar(300) DEFAULT NULL,
  `nominal` bigint(20) NOT NULL,
  `description` text NOT NULL,
  `debit_date` datetime NOT NULL,
  `gambar` varchar(300) DEFAULT NULL,
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
(55, 49, 24, NULL, 100123, 'dasdasdsadasd00000000000', '2023-10-06 12:00:00', NULL, '2023-10-06 10:59:20', '2023-10-06 10:59:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
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
  `presensi_id` bigint(100) UNSIGNED NOT NULL,
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
  `total_hadir` varchar(300) DEFAULT NULL,
  `jumlah_hadir` varchar(300) DEFAULT NULL,
  `total_presensi` varchar(300) DEFAULT NULL,
  `tanggal` datetime DEFAULT NULL,
  `potongan` varchar(100) NOT NULL,
  `pph` varchar(300) DEFAULT NULL,
  `total` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `note` text DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `maintenance`
--

INSERT INTO `maintenance` (`id`, `user_id`, `title`, `note`, `start_date`, `end_date`, `gambar`, `status`, `created_at`, `updated_at`) VALUES
(5, NULL, 'nyoba', 'adfasdasdas', '2023-09-23 05:00:00', '2023-09-24 01:00:00', '1695610770.jpg', 'non-aktif', '2023-09-24 15:14:53', '2023-09-25 04:03:24'),
(6, NULL, 'tessss', 'adawdawdawd', '2023-09-23 05:00:00', '2023-09-24 05:00:00', 'images/1695614655.png', 'non-aktif', '2023-09-25 04:04:15', '2023-09-25 04:04:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
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
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
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
  `id` varchar(100) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `client_id` int(10) UNSIGNED NOT NULL,
  `scopes` text DEFAULT NULL,
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
  `name` varchar(255) NOT NULL,
  `secret` varchar(100) DEFAULT NULL,
  `redirect` text NOT NULL,
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
  `id` varchar(100) NOT NULL,
  `access_token_id` varchar(100) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `lokasi` varchar(300) DEFAULT NULL,
  `time_pulang` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `presensi`
--

INSERT INTO `presensi` (`id`, `user_id`, `status`, `status_pulang`, `note`, `gambar`, `lokasi`, `time_pulang`, `created_at`, `updated_at`) VALUES
(61, 49, 'dinas luar kota', NULL, '<p>sdfasdasdas</p>', '1694969803.jpg', 'Unknown', NULL, '2023-09-17 23:56:43', '2023-09-17 16:56:43'),
(62, 49, 'hadir', 'pulang', '<p>edfasdasda</p>', '1694997361.png', 'Unknown', '2023-09-18 07:44:47', '2023-09-18 07:36:01', '2023-09-18 00:44:47'),
(66, 44, 'terlambat', NULL, '<p>sfsdfsdfsd</p>', '1695012255.png', 'Unknown', NULL, '2023-09-18 11:44:15', '2023-09-18 04:44:15'),
(67, 24, 'izin', 'pulang', '<p>dawdawda</p>', '1695013916.png', 'Unknown', '2023-09-18 15:13:33', '2023-09-18 12:11:56', '2023-09-18 08:13:33'),
(68, 22, 'izin', 'pulang', '<p>adasdasdasd</p>', '1695263096.jpg', 'Unknown', '2023-09-21 09:24:56', '2023-09-21 09:23:15', '2023-09-21 02:24:56'),
(69, 22, 'terlambat', NULL, '<p>uhgiugh</p>', '1696419142.jpg', 'Unknown', NULL, '2023-10-04 18:32:22', '2023-10-04 11:32:22'),
(70, 22, 'terlambat', NULL, '<p>fhfh</p>', '1696492288.png', 'Unknown', NULL, '2023-10-05 14:51:28', '2023-10-05 07:51:28');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `level` varchar(25) NOT NULL DEFAULT 'user',
  `jenis` varchar(25) NOT NULL DEFAULT 'perorangan',
  `company` varchar(25) DEFAULT NULL,
  `alamat_company` text DEFAULT NULL,
  `telp_company` varchar(200) DEFAULT NULL,
  `email_company` varchar(200) DEFAULT NULL,
  `logo_company` varchar(200) DEFAULT NULL,
  `pj_company` varchar(300) DEFAULT NULL,
  `telp` varchar(100) DEFAULT NULL,
  `status` varchar(100) DEFAULT NULL,
  `notif` varchar(300) DEFAULT NULL,
  `tenggat` varchar(300) DEFAULT NULL,
  `title` varchar(300) DEFAULT NULL,
  `nik` varchar(100) DEFAULT NULL,
  `norek` varchar(100) DEFAULT NULL,
  `bank` varchar(100) DEFAULT NULL,
  `gambar` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `email_verified_at`, `avatar`, `remember_token`, `created_at`, `updated_at`, `level`, `jenis`, `company`, `alamat_company`, `telp_company`, `email_company`, `logo_company`, `pj_company`, `telp`, `status`, `notif`, `tenggat`, `title`, `nik`, `norek`, `bank`, `gambar`) VALUES
(1, 'Berto Juni', 'bertojuni@gmail.com', 'bertojuni', 'junijuni008', NULL, '898192462.png', NULL, NULL, NULL, 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'bertojunik', 'bertojunijuni@gmail.com', 'bertojunik', '$2y$10$wwknRoFVyEgG51kO0sxICepdHSjf5Dvd2QdxKkOexM.XyMEddihS.', '2023-07-15 22:14:15', NULL, NULL, '2023-07-15 01:15:22', '2023-07-15 22:14:15', 'admin', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'junijuni', 'juni@gmail.com', 'juni', '$2y$10$LCf6VN3S7vpf0aw87rdG7ursTKa7gide8ziPUl64P5stnbdd9CeyC', NULL, NULL, NULL, '2023-07-15 03:50:27', '2023-07-15 03:50:27', '', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'AJIROHMAT', 'aji@gmail.com', 'aji123', '$2y$10$bq1CweJhPPSviFZT7AmyFufN6J0rm2ojJJ1LTV62UCxG.QNka6l.6', NULL, NULL, NULL, '2023-07-15 04:30:17', '2023-07-15 08:16:08', 'bisnis', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'admin', 'admin@gmail.com', 'admin', '$2y$10$HfeNu60ry3y0iMqe48LITubOI0dIVYZAHshOmlY6D5EF8KLl2/4hG', '2023-07-15 22:28:38', NULL, NULL, '2023-07-15 22:28:38', '2023-07-15 22:28:38', 'admin', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'user', 'user@gmail.com', 'user', '$2y$10$1LbprO4sqJha0rQYVq.Zp.2lZDVRXkbNqhs2qVWfY/ldSIqbTxwYu', NULL, NULL, NULL, '2023-07-15 22:29:08', '2023-07-15 22:29:08', 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'bisnis', 'bisnis@gmail.com', 'bisnis', '$2y$10$5i1VcojhcIe.d17xyxMkYu/w5B9IbxWR4YbHCAfoGb6Dm./CxM262', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:31:55', '2023-07-15 22:31:55', 'bisnis', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'user2', 'user2@gmail.com', 'user2', '$2y$10$ij09uJWwUSED1xesf2nyceme9MlQyrL7L9ObwwIxEDlpHZGRL5fKG', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:44:41', '2023-07-15 22:44:41', 'user', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'admin2', 'admin2@gmail.com', 'admin2', '$2y$10$ZXWl8md4Tts7TtkQSBgLR.R7qmrhj6LmxgVJN9joaLHVmPQQsxNQu', '2023-07-16 07:48:39', NULL, NULL, '2023-07-16 07:48:39', '2023-07-16 07:48:39', 'admin', '', '', NULL, NULL, NULL, NULL, NULL, '', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 'manager', 'manager@gmail.com', 'manager', '$2y$10$fIOHMi7XbpMrX1774/gEJevQiasVLwo0KMZ307nplhMioeibRNXp.', '2023-07-31 06:33:39', NULL, NULL, '2023-07-25 03:07:04', '2023-10-03 17:20:32', 'manager', 'perorangan', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '506', '1695226055.JPG'),
(24, 'staff', 'staff@gmail.com', 'staff', '$2y$10$h.zowDsg0SrwjE3psUAkF..YsqQjbp.dkCYo.KdtQuTYjSOuWAuNW', '2023-08-11 21:17:32', NULL, NULL, '2023-07-25 03:44:11', '2023-10-03 17:20:32', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 'azlinda', 'azlinda@gmail.com', 'azlinda', '$2y$10$Yaw3qwIOAQsTMwdY0qJQn.klaPKl4geCNaSSF7jQac0yElipRNBQO', '2023-07-26 08:16:30', NULL, NULL, '2023-07-26 08:04:55', '2023-07-26 08:16:30', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', 'off', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'tessss', 'tess@gmail.com', 'tessss', '$2y$10$/UhG6LTus26zty/etTOLJuz6uQXscUje20//4G4nhkZ8Bj0jnUY5C', NULL, NULL, NULL, '2023-07-26 09:36:58', '2023-07-26 09:36:58', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'ebiiiiiii', 'ebi@gmail.com', 'ebiiiiiii', '$2y$10$5l3hOxKAKM4aVeluGt1Ibe557kCwiTT/AIJGOOv7ZyZSu2VSUVlSu', '2023-07-27 08:59:18', NULL, NULL, '2023-07-27 08:52:45', '2023-10-03 17:20:32', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'jonii', 'joni@gmail.com', 'jonii', '$2y$10$khOUus0zo4B/CqPvuNhEoOipDxBfwaDdHDezUGxTN1aUufl1VG77y', NULL, NULL, NULL, '2023-07-27 08:56:54', '2023-07-27 08:56:54', 'user', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'staff2', 'staff2@gmail.com', 'staff2', '$2y$10$OWQko./89s/ZWRBVlps0pu0ui1uvdHtH9aAurgY.IpeNbhbLn7V4.', '2023-07-28 21:47:25', NULL, NULL, '2023-07-28 21:45:31', '2023-10-03 17:20:32', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'yolanda', 'yola@gmail.com', 'yolanda', '$2y$10$eDJhpUHPafy6B.2t9Q9NEeG91IV3FF0LdNI5L6D0O.jSDIci.lDlO', '2023-08-22 08:20:24', NULL, NULL, '2023-07-29 02:03:59', '2023-10-03 17:20:32', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '441', NULL),
(45, 'hafid', 'hafid@gmail.com', 'hafid', '$2y$10$lEdZ9612KDsk1dlojVB/2uIy3Wy6eSLQEN.IyCftAdCL/DDGdrevW', NULL, NULL, NULL, '2023-07-29 02:45:46', '2023-07-29 02:45:46', 'users', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '0895421735441', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 'ipann', 'ipan@gmai.com', 'ipann', '$2y$10$42AuxNDJ6GMD8Q6SnkM/uenpiNOVhfzfx8iNFlrpYEWs.40SXZhPu', NULL, NULL, NULL, '2023-07-29 02:50:24', '2023-10-03 17:20:32', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', NULL, NULL, NULL, NULL, '123', '123', '1234', NULL),
(47, 'rental', 'rental@gmail.com', 'rental', '$2y$10$mccAiMPIyQ9J75.0u9Aee.JiJ.aNIObmC4FTOxmKFT9Cze4/Fhl7a', '2023-08-10 22:27:13', NULL, NULL, '2023-08-10 22:26:13', '2023-08-10 22:27:13', 'manager', 'penyewaan', 'rental', NULL, NULL, NULL, NULL, NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 'Berto Juni Krisnanto', 'bertojunikrisnanto@gmail.com', 'junii', '$2y$10$mj9wIGi6jEKMHx4reastsugzygw1tDDB5xhpx7GgN9pwu93.jbpP6', '2023-08-11 21:00:30', NULL, NULL, '2023-08-11 20:59:31', '2023-08-11 21:00:30', 'staff', 'penyewaan', 'rsccc', NULL, NULL, NULL, NULL, NULL, '0895421735441', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'karyawan', 'karyawan@gmail.com', 'karyawan', '$2y$10$y3.aLjrFU3rMMvqLOJwvuukvomUkRlrbzjqfpmbfx0LHTYAKoFXoS', '2023-10-05 07:44:23', NULL, NULL, '2023-08-12 03:36:07', '2023-10-05 07:44:23', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895421735441', 'on', NULL, NULL, NULL, '123456', '123456', '014', '1695309460.jpg'),
(50, 'baimm', 'baim@gmail.com', 'baimm', '$2y$10$7AZeYgj8Gvd8.w1C96Q.3uirSviRB0qyETZRYHEkQj.fIRpZ.sn6a', NULL, NULL, NULL, '2023-08-14 05:00:37', '2023-10-03 17:20:32', 'staff', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '123456789012', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 'ida ervi', 'erviida@gmail.com', 'ida ervi', '$2y$10$fT6sS53/0rkj7wZJI/gv7ea3OaA1tONWWq0xOGhZ0som8UMtG2Cc2', NULL, NULL, NULL, '2023-09-21 15:29:16', '2023-10-03 17:20:32', 'karyawan', 'bisnis', 'rumahscopus', 'Tegal Catak UH IV/No.638 Warungboto, Umbulharjo, Yogyakarta\r\nDaerah Istimewa Yogyakarta 55164', '43534534', 'bertojunikrisnanto@gmail.com', '1696353632.jpg', NULL, '0895415165136', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 'fertika', 'fertika@gmail.com', 'fertika', '$2y$10$HaneVoc.ox3l2SAjhfXOJOxzTgMSzGE29Y0pkNb9z9bV5ISGlA7T.', '2023-09-21 15:55:46', NULL, NULL, '2023-09-21 15:50:47', '2023-09-21 15:55:46', 'admin', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '08951616516', 'on', NULL, NULL, NULL, '54564', '54654', '022', NULL),
(54, 'dwilestari', 'dwilestari@gmail.com', 'dwilestari', '$2y$10$iPcdzrUAHs156Fbq6.G8m.kpYvHbDIWXFTSDTjA4EaNDYxG0eVSVy', '2023-09-21 16:01:49', NULL, NULL, '2023-09-21 15:59:32', '2023-09-21 16:01:49', 'users', 'perorangan', NULL, NULL, NULL, NULL, NULL, NULL, '45646546', 'on', NULL, NULL, NULL, '123456', '123456', '200', NULL);

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
-- AUTO_INCREMENT untuk tabel `categories_credit`
--
ALTER TABLE `categories_credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `company`
--
ALTER TABLE `company`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit`
--
ALTER TABLE `credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT untuk tabel `debit`
--
ALTER TABLE `debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `gaji`
--
ALTER TABLE `gaji`
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

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
  MODIFY `id` bigint(100) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT untuk tabel `tambah_barang`
--
ALTER TABLE `tambah_barang`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

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
  ADD CONSTRAINT `gaji_ibfk_2` FOREIGN KEY (`presensi_id`) REFERENCES `presensi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
