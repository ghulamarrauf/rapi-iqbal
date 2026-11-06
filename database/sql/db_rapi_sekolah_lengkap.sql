-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Versi lengkap (impor ke database kosong): sudah termasuk kolom email, jenis agenda, dan tabel OTP
-- Generation Time: Nov 05, 2026 at 01:07 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_rapi_sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `reporter_id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `pic_id` bigint UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repair_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_repair` enum('Belum Diperbaiki','Menunggu Spare Part','Sudah Diperbaiki') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Diperbaiki',
  `repaired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `room_id`, `reporter_id`, `event_id`, `pic_id`, `description`, `photo_path`, `repair_photo_path`, `status_repair`, `repaired_at`, `created_at`, `updated_at`) VALUES
(1, 2, 2, NULL, 4, 'AC tidak dingin dan menetes air di dekat meja nomor 5.', NULL, 'repairs/9f5eb840-7d09-40e1-93fe-bf01e4d992d9.jpg', 'Sudah Diperbaiki', '2026-11-04 17:33:36', '2026-09-30 05:31:39', '2026-11-04 17:33:39'),
(2, 3, 2, NULL, 4, 'Switch rak jaringan mati sebagian, 6 port tidak menyala.', NULL, NULL, 'Belum Diperbaiki', NULL, '2026-09-25 05:31:39', '2026-11-04 17:34:27'),
(3, 5, 3, NULL, 5, 'Proyektor tidak menampilkan gambar.', NULL, NULL, 'Menunggu Spare Part', NULL, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(4, 4, 2, NULL, 5, 'hvyhujbhihb', 'complaints/d32510e8-9bcc-4394-92fd-b9280f2e955e.jpg', NULL, 'Belum Diperbaiki', NULL, '2026-11-03 18:24:07', '2026-11-03 18:24:07'),
(5, 1, 2, NULL, 4, 'jbjbjjjbwjbsnjsnj', 'complaints/e9900321-14b2-4216-9847-8340c3522998.jpg', NULL, 'Sudah Diperbaiki', NULL, '2026-11-03 18:38:02', '2026-11-03 19:12:47'),
(6, 4, 2, NULL, 5, 'ccfcgyychcfvyy', 'complaints/0bad8c53-c05b-472f-ae97-5ede9acd5317.jpg', NULL, 'Belum Diperbaiki', NULL, '2026-11-03 23:03:42', '2026-11-03 23:03:42'),
(7, 6, 2, NULL, NULL, 'proyektor tidak bisa nyala', 'complaints/778126e8-20f9-46d4-82ff-bdf20f7ed775.jpg', NULL, 'Belum Diperbaiki', NULL, '2026-11-04 03:13:50', '2026-11-04 03:13:50'),
(8, 1, 2, 1, 4, 'kursi patah', 'complaints/a70bf50b-ae70-41eb-8999-00d66b31137b.jpg', 'repairs/eaced093-a171-4a69-bb12-02f214dc991f.jpg', 'Sudah Diperbaiki', '2026-11-04 04:49:34', '2026-11-04 03:37:54', '2026-11-04 04:49:34');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Guru',
  `description` text COLLATE utf8mb4_unicode_ci,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Menunggu',
  `reject_reason` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `move_reason` text COLLATE utf8mb4_unicode_ci,
  `moved_from_room_id` bigint UNSIGNED DEFAULT NULL,
  `moved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `room_id`, `title`, `description`, `event_date`, `start_time`, `end_time`, `status`, `reject_reason`, `reviewed_by`, `reviewed_at`, `move_reason`, `moved_from_room_id`, `moved_at`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Praktikum Basis Data XI RPL', 'Praktik query SQL dan relasi tabel.', '2026-10-01', '07:45:00', '10:00:00', 'Disetujui', NULL, NULL, NULL, NULL, NULL, NULL, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(2, 3, 1, 'Rapat Persiapan Class Meeting', NULL, '2026-10-01', '13:00:00', '15:15:00', 'Disetujui', NULL, NULL, NULL, 'ppppp', 4, '2026-11-04 17:50:22', '2026-09-30 05:31:39', '2026-11-04 17:50:22'),
(3, 1, 4, 'Sosialisasi Tata Tertib', NULL, '2026-10-03', '07:00:00', '09:15:00', 'Disetujui', NULL, NULL, NULL, 'mbuuh', 3, '2026-11-01 18:34:49', '2026-09-30 05:31:39', '2026-11-01 18:34:49');

--
-- Jenis agenda: agenda milik akun OSIS menjadi event siswa
--
UPDATE `events` SET `category` = 'Siswa' WHERE `id` = 2;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_09_04_031406_create_roles_table', 1),
(2, '2026_09_04_031425_create_users_table', 1),
(3, '2026_09_04_031453_create_rooms_table', 1),
(4, '2026_09_04_031532_create_complaints_table', 1),
(5, '2026_09_04_031602_create_events_table', 1),
(6, '2026_09_04_031700_create_room_notifications_table', 1),
(7, '2026_11_04_000001_add_review_columns_to_events_table', 2),
(8, '2026_11_04_000002_add_event_and_pic_to_complaints_table', 2),
(9, '2026_11_04_000003_add_repair_photo_to_complaints_table', 3),
(10, '2026_11_05_000001_add_email_to_users_table', 4),
(11, '2026_11_05_000002_create_password_otps_table', 4),
(12, '2026_11_06_000001_add_category_to_events_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_otps_user_id_foreign` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Waka / Admin', '2026-09-30 05:31:36', '2026-09-30 05:31:36'),
(2, 'Guru / Organisasi', '2026-09-30 05:31:36', '2026-09-30 05:31:36'),
(3, 'PIC Ruang', '2026-09-30 05:31:36', '2026-09-30 05:31:36');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `location`, `pic_id`, `created_at`, `updated_at`) VALUES
(1, 'Lab Komputer 1', 'Gedung A lantai 2', 4, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(2, 'Lab Komputer 2', 'Gedung A lantai 2', 4, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(3, 'Bengkel Jaringan', 'Gedung B', 4, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(4, 'Aula', 'Gedung Utama', 5, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(5, 'Perpustakaan', 'Gedung Utama lantai 2', 5, '2026-09-30 05:31:39', '2026-09-30 05:31:39'),
(6, 'Ruang Rapat Guru', 'Gedung Utama', NULL, '2026-09-30 05:31:39', '2026-09-30 05:31:39');

-- --------------------------------------------------------

--
-- Table structure for table `room_notifications`
--

CREATE TABLE `room_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `event_id` bigint UNSIGNED DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_notifications`
--

INSERT INTO `room_notifications` (`id`, `user_id`, `event_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(4, 3, 2, 'Agenda \"Rapat Persiapan Class Meeting\" pada 1 Oktober 2026 pada Jam ke-8 s/d ke-10 dipindahkan dari Aula ke Lab Komputer 1. Alasan: ppppp', 0, '2026-11-04 17:50:22', '2026-11-04 17:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `phone`, `role_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Waka Sarana', 'admin', '$2y$12$NUJKsKIQvfyqsi6U2z.RAO8M5A3f5RTDaU8S5WrlE3aRlBdcytupu', '082314490681', 1, NULL, '2026-09-30 05:31:37', '2026-09-30 05:31:37'),
(2, 'Rina Kusumawati', 'guru', '$2y$12$PHRJxUGsLJgYhydChSNCoeqsUj9gqkIS7LG6oHbP27ogyVbiohAcO', '082314490681', 2, 'xqv5NY850F6wWjGY2dz1VDVBe083BYCEqOnm4UwQQofe72wYTghwQuvMtjj0', '2026-09-30 05:31:37', '2026-11-04 03:12:39'),
(3, 'OSIS SMK', 'osis', '$2y$12$YFed.dIRw/WuY.czZDYWcek3aK8SUFOqyRPQJL3544SwFCuc/a/by', NULL, 2, NULL, '2026-09-30 05:31:38', '2026-09-30 05:31:38'),
(4, 'Pak Dimas', 'pic', '$2y$12$GfmFkr4vewpp8JzrAqkj3u9lRKhrnPQa2FL97u2jP5/wtuibfXdA6', '082314490681', 3, NULL, '2026-09-30 05:31:38', '2026-09-30 05:31:38'),
(5, 'Bu Sari', 'pic2', '$2y$12$/eO3BwTAjPU2IT7sQlfJ5eKJ2eWBmYgryQls3EWsj4d.MnAlNnKhe', '082314490681', 3, NULL, '2026-09-30 05:31:39', '2026-09-30 05:31:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaints_room_id_foreign` (`room_id`),
  ADD KEY `complaints_reporter_id_foreign` (`reporter_id`),
  ADD KEY `complaints_event_id_foreign` (`event_id`),
  ADD KEY `complaints_pic_id_foreign` (`pic_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `events_user_id_foreign` (`user_id`),
  ADD KEY `events_moved_from_room_id_foreign` (`moved_from_room_id`),
  ADD KEY `events_room_id_event_date_index` (`room_id`,`event_date`),
  ADD KEY `events_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `events_event_date_index` (`event_date`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_name_unique` (`name`),
  ADD KEY `rooms_pic_id_foreign` (`pic_id`);

--
-- Indexes for table `room_notifications`
--
ALTER TABLE `room_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_notifications_user_id_foreign` (`user_id`),
  ADD KEY `room_notifications_event_id_foreign` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `room_notifications`
--
ALTER TABLE `room_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_pic_id_foreign` FOREIGN KEY (`pic_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_moved_from_room_id_foreign` FOREIGN KEY (`moved_from_room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD CONSTRAINT `password_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_pic_id_foreign` FOREIGN KEY (`pic_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `room_notifications`
--
ALTER TABLE `room_notifications`
  ADD CONSTRAINT `room_notifications_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
