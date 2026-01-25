-- Skema Database untuk Platform Streaming Anime (Scraper Otakudesu)
-- Dibuat oleh Kilo Code

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for animes
-- ----------------------------
CREATE TABLE IF NOT EXISTS `animes` (
  `slug` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `title_jp` VARCHAR(255),
  `score` FLOAT DEFAULT NULL,
  `producer` VARCHAR(255),
  `type` VARCHAR(100),
  `status` VARCHAR(100),
  `total_episode` INT DEFAULT 0,
  `duration` VARCHAR(100),
  `release_date` VARCHAR(100),
  `studio` VARCHAR(255),
  `synopsis` TEXT,
  `poster_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`slug`),
  INDEX `idx_anime_title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for episodes
-- ----------------------------
CREATE TABLE IF NOT EXISTS `episodes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `anime_slug` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `episode_number` VARCHAR(50),
  `episode_slug` VARCHAR(255) NOT NULL,
  `uploaded_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_episode_slug` (`episode_slug`),
  CONSTRAINT `fk_episodes_anime_slug` FOREIGN KEY (`anime_slug`) REFERENCES `animes` (`slug`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_episodes_anime_slug` (`anime_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for download_links
-- ----------------------------
CREATE TABLE IF NOT EXISTS `download_links` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `episode_id` INT NOT NULL,
  `resolution` VARCHAR(50) NOT NULL,
  `provider` VARCHAR(100) NOT NULL,
  `url` VARCHAR(1000) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_download_links_episode_id` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_download_links_episode_id` (`episode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for stream_links
-- ----------------------------
CREATE TABLE IF NOT EXISTS `stream_links` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `episode_id` INT NOT NULL,
  `provider` VARCHAR(100) NOT NULL,
  `url` VARCHAR(1000) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_stream_links_episode_id` FOREIGN KEY (`episode_id`) REFERENCES `episodes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_stream_links_episode_id` (`episode_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;