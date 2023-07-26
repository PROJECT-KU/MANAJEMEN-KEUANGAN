-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Jul 2023 pada 06.16
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
(3, 3, 'minum', '2023-07-15 01:18:47', '2023-07-15 01:18:47'),
(4, 22, 'Evoluigi', '2023-07-25 08:46:35', '2023-07-25 08:46:35'),
(5, 22, 'manangercredit', '2023-07-25 19:02:27', '2023-07-25 20:09:12'),
(11, 22, 'credit staff', '2023-07-25 20:11:16', '2023-07-25 20:11:32'),
(12, 22, 'buat staff', '2023-07-25 20:11:51', '2023-07-25 20:11:51');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories_debit`
--

CREATE TABLE `categories_debit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories_debit`
--

INSERT INTO `categories_debit` (`id`, `user_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'makan', '2023-07-12 23:14:29', '2023-07-12 23:14:29'),
(2, 1, 'minum', '2023-07-13 07:50:41', '2023-07-13 07:50:41'),
(3, 3, 'makan', '2023-07-15 01:18:39', '2023-07-15 01:18:39'),
(5, 24, 'berto', '2023-07-25 04:17:20', '2023-07-25 04:17:20'),
(6, 24, 'staff', '2023-07-25 04:24:03', '2023-07-25 04:24:03'),
(7, 22, 'nyoba', '2023-07-25 16:18:14', '2023-07-25 16:18:14'),
(8, 22, 'manager', '2023-07-25 18:59:26', '2023-07-25 18:59:26'),
(10, 22, 'staff new', '2023-07-25 19:57:58', '2023-07-25 19:57:58'),
(12, 22, 'buat staf', '2023-07-25 20:07:05', '2023-07-25 20:07:05');

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
(5, 3, 3, 10000, 'asdasdasdas', '2023-07-15 03:00:00', '2023-07-15 01:19:22', '2023-07-15 01:19:22'),
(6, 22, 5, 50000, 'wdadawdawdawd', '2023-07-26 12:00:00', '2023-07-25 19:19:27', '2023-07-25 19:19:27'),
(7, 22, 4, 10000, 'fsefsefsefs', '2023-07-26 12:00:00', '2023-07-25 19:24:32', '2023-07-25 19:24:32'),
(8, 22, 11, 50000, 'asdasdasda', '2023-07-26 12:00:00', '2023-07-25 20:11:43', '2023-07-25 20:11:43'),
(9, 24, 12, 2000, 'asdasdasdasd', '2023-07-26 12:00:00', '2023-07-25 20:12:10', '2023-07-25 20:12:10');

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
(13, 3, 3, 50000, 'fsdfasfasdfsad', '2023-07-15 02:00:00', '2023-07-15 01:19:05', '2023-07-15 02:43:19'),
(15, 24, 5, 10000, 'awdawdaw', '2023-07-25 12:00:00', '2023-07-25 04:17:31', '2023-07-25 04:17:31'),
(16, 24, 6, 5000, 'hjfhjashdfjashjkdhkasjhdas', '2023-07-25 12:00:00', '2023-07-25 04:24:20', '2023-07-25 04:24:20'),
(17, 22, 6, 5000, 'eawerfawff', '2023-07-26 12:00:00', '2023-07-25 18:08:30', '2023-07-25 18:10:29'),
(18, 22, 8, 10000, 'dasdadasda', '2023-07-26 12:00:00', '2023-07-25 19:22:22', '2023-07-25 19:22:22'),
(20, 22, 10, 10000, 'dadasdas', '2023-07-26 12:00:00', '2023-07-25 20:06:05', '2023-07-25 20:06:16'),
(21, 24, 12, 100000, 'adfsdasdasdas1233585', '2023-07-26 12:00:00', '2023-07-25 20:07:23', '2023-07-25 20:07:34'),
(22, 3, 3, 10000, 'adsdasdas121', '2023-07-26 12:00:00', '2023-07-25 20:56:55', '2023-07-25 20:57:03');

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
  `company` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password`, `email_verified_at`, `avatar`, `remember_token`, `created_at`, `updated_at`, `level`, `jenis`, `company`) VALUES
(1, 'Berto Juni', 'bertojuni@gmail.com', 'bertojuni', 'junijuni008', NULL, '898192462.png', NULL, NULL, NULL, 'user', '', ''),
(3, 'bertojunik', 'bertojunijuni@gmail.com', 'bertojunik', '$2y$10$wwknRoFVyEgG51kO0sxICepdHSjf5Dvd2QdxKkOexM.XyMEddihS.', '2023-07-15 22:14:15', NULL, NULL, '2023-07-15 01:15:22', '2023-07-15 22:14:15', 'admin', '', ''),
(12, 'junijuni', 'juni@gmail.com', 'juni', '$2y$10$LCf6VN3S7vpf0aw87rdG7ursTKa7gide8ziPUl64P5stnbdd9CeyC', NULL, NULL, NULL, '2023-07-15 03:50:27', '2023-07-15 03:50:27', '', '', ''),
(13, 'AJIROHMAT', 'aji@gmail.com', 'aji123', '$2y$10$bq1CweJhPPSviFZT7AmyFufN6J0rm2ojJJ1LTV62UCxG.QNka6l.6', NULL, NULL, NULL, '2023-07-15 04:30:17', '2023-07-15 08:16:08', 'bisnis', '', ''),
(14, 'azlinda', 'azlinda@gmail.com', 'febi', '$2y$10$t6oXH1bZpr/TFhnIKzJ.T.xNZym00SB/lUBprTIyFrigYX1mxzP7q', '2023-07-15 21:51:11', NULL, NULL, '2023-07-15 09:22:09', '2023-07-15 21:51:11', 'admin', '', ''),
(15, 'admin', 'admin@gmail.com', 'admin', '$2y$10$HfeNu60ry3y0iMqe48LITubOI0dIVYZAHshOmlY6D5EF8KLl2/4hG', '2023-07-15 22:28:38', NULL, NULL, '2023-07-15 22:28:38', '2023-07-15 22:28:38', 'admin', '', ''),
(16, 'user', 'user@gmail.com', 'user', '$2y$10$1LbprO4sqJha0rQYVq.Zp.2lZDVRXkbNqhs2qVWfY/ldSIqbTxwYu', NULL, NULL, NULL, '2023-07-15 22:29:08', '2023-07-15 22:29:08', 'user', '', ''),
(17, 'bisnis', 'bisnis@gmail.com', 'bisnis', '$2y$10$5i1VcojhcIe.d17xyxMkYu/w5B9IbxWR4YbHCAfoGb6Dm./CxM262', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:31:55', '2023-07-15 22:31:55', 'bisnis', '', ''),
(18, 'user2', 'user2@gmail.com', 'user2', '$2y$10$ij09uJWwUSED1xesf2nyceme9MlQyrL7L9ObwwIxEDlpHZGRL5fKG', NULL, '5d66284f74f17963dd1b68e2b3fd49b5.jpg', NULL, '2023-07-15 22:44:41', '2023-07-15 22:44:41', 'user', '', ''),
(19, 'admin2', 'admin2@gmail.com', 'admin2', '$2y$10$ZXWl8md4Tts7TtkQSBgLR.R7qmrhj6LmxgVJN9joaLHVmPQQsxNQu', '2023-07-16 07:48:39', NULL, NULL, '2023-07-16 07:48:39', '2023-07-16 07:48:39', 'admin', '', ''),
(22, 'manager', 'manager@gmail.com', 'manager', '$2y$10$QxDDAFSQWF1.F1zM7uUrFuqK7w/wWsUrdcdis1K8RcESBjNNSfnKq', '2023-07-25 04:15:14', NULL, NULL, '2023-07-25 03:07:04', '2023-07-25 04:15:14', 'manager', 'perorangan', 'rumah scopus'),
(24, 'staff', 'staff@gmail.com', 'staff', '$2y$10$h.zowDsg0SrwjE3psUAkF..YsqQjbp.dkCYo.KdtQuTYjSOuWAuNW', '2023-07-25 04:14:49', NULL, NULL, '2023-07-25 03:44:11', '2023-07-25 04:14:49', 'staff', 'perorangan', 'rumah scopus');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `categories_debit`
--
ALTER TABLE `categories_debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `credit`
--
ALTER TABLE `credit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `debit`
--
ALTER TABLE `debit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
