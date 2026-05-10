-- АфишаКолыма — полная схема базы данных
-- Сгенерировано для деплоя на чистый сервер
-- Применяется до php artisan migrate (миграции уже учтены)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Справочники ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `roles` (`role_id`, `name`) VALUES
  (1, 'Администратор'),
  (2, 'Пользователь'),
  (3, 'Модератор');

CREATE TABLE IF NOT EXISTS `statuses` (
  `status_id` int NOT NULL AUTO_INCREMENT,
  `status_name` varchar(50) NOT NULL,
  PRIMARY KEY (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `statuses` (`status_id`, `status_name`) VALUES
  (1, 'Активно'),
  (2, 'Завершено'),
  (3, 'Отменено'),
  (4, 'Ожидает подтверждения');

CREATE TABLE IF NOT EXISTS `organization_types` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `organization_types` (`type_id`, `name`) VALUES
  (1, 'ООО'),
  (2, 'ИП'),
  (3, 'НКО'),
  (4, 'Другое');

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `categories` (`id`, `name`) VALUES
  (1, 'Концерты'),
  (2, 'Выставки'),
  (3, 'Театр'),
  (4, 'Кино'),
  (5, 'Спорт'),
  (6, 'Фестивали'),
  (7, 'Лекции и мастер-классы'),
  (8, 'Вечеринки');

-- ── Пользователи ─────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `patronymic` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `email` text,
  `phone` varchar(20) NOT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `role_id` int DEFAULT NULL,
  `status` enum('active','warned','restricted','blocked') NOT NULL DEFAULT 'active',
  `warning_count` tinyint unsigned NOT NULL DEFAULT '0',
  `blocked_until` datetime DEFAULT NULL,
  `restriction_until` datetime DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `pd_consent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Организации ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `organization` (
  `organization_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `address` text,
  `inn` text,
  `ogrn` varchar(15) DEFAULT NULL,
  `kpp` varchar(9) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(300) DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `rejection_reason` text,
  `password_hash` varchar(255) NOT NULL,
  `email` text,
  `image` varchar(500) DEFAULT NULL,
  `pd_consent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`organization_id`),
  KEY `type_id` (`type_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `organization_types` (`type_id`),
  CONSTRAINT `organization_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Площадки и события ───────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `venues` (
  `venue_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `address` text,
  `category_id` int DEFAULT NULL,
  `age` tinyint DEFAULT NULL,
  `description` text,
  `image` text,
  PRIMARY KEY (`venue_id`),
  KEY `category_id` (`category_id`),
  FULLTEXT KEY `ft_venues` (`name`,`description`,`address`),
  CONSTRAINT `venues_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `age_restriction` tinyint DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `image` text,
  `organization_id` int DEFAULT NULL,
  `venue_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `organization_id` (`organization_id`),
  KEY `venue_id` (`venue_id`),
  KEY `category_id` (`category_id`),
  KEY `status_id` (`status_id`),
  FULLTEXT KEY `ft_events` (`title`,`description`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`),
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`),
  CONSTRAINT `events_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `events_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Отзывы, избранное, билеты ─────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `text` text,
  `rating` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `venue_id` int DEFAULT NULL,
  PRIMARY KEY (`review_id`),
  KEY `event_id` (`event_id`),
  KEY `venue_id` (`venue_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`),
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `favorites` (
  `favorite_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `venue_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`favorite_id`),
  KEY `event_id` (`event_id`),
  KEY `venue_id` (`venue_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  CONSTRAINT `favorites_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tickets` (
  `ticket_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `quantity` int DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'cart',
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Sanctum токены ───────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Логи аудита ──────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` int unsigned DEFAULT NULL,
  `actor_role` tinyint unsigned DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` int unsigned DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_actor_id_index` (`actor_id`),
  KEY `audit_logs_target_type_target_id_index` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Таблица миграций Laravel (все миграции уже применены) ────────────────────

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_05_10_012815_create_personal_access_tokens_table', 1),
  ('2026_05_10_045135_add_fulltext_indexes',               2),
  ('2026_05_10_100000_add_user_moderation_fields',         3),
  ('2026_05_10_100001_create_audit_logs_table',            3);

SET FOREIGN_KEY_CHECKS = 1;
