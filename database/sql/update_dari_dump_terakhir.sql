-- Update untuk database yang strukturnya sama dengan dump terakhir (sebelum kolom email,
-- jenis agenda, dan tabel OTP). Jalankan SEKALI di tab SQL phpMyAdmin pada db_rapi_sekolah.
-- Data yang sudah ada tidak dihapus.

-- 1. Email pengguna (wajib untuk OTP lupa password)
ALTER TABLE `users`
  ADD COLUMN `email` VARCHAR(255) NULL AFTER `username`,
  ADD UNIQUE KEY `users_email_unique` (`email`);

-- 2. Jenis agenda di kalender: Siswa / Guru
ALTER TABLE `events`
  ADD COLUMN `category` VARCHAR(20) NOT NULL DEFAULT 'Guru' AFTER `title`;
UPDATE `events` SET `category` = 'Siswa'
  WHERE `user_id` IN (SELECT `id` FROM `users` WHERE LOWER(`name`) LIKE '%osis%');

-- 3. Tabel kode OTP
CREATE TABLE `password_otps` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_otps_user_id_foreign` (`user_id`),
  CONSTRAINT `password_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Catat migrasi agar `php artisan migrate` tidak mengulanginya
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_11_05_000001_add_email_to_users_table', 4),
('2026_11_05_000002_create_password_otps_table', 4),
('2026_11_06_000001_add_category_to_events_table', 4);

-- 5. (Opsional) Isi email akun yang ada. Akun tanpa email akan diminta mengisinya saat login.
-- UPDATE `users` SET `email` = 'admin@emailanda.com' WHERE `id` = 1;
