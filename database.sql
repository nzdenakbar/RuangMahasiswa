
-- =========================================
-- DATABASE SISTEM INFORMASI UPY
-- VERSI TERBARU
-- =========================================

CREATE DATABASE IF NOT EXISTS sistem_informasi_upy
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sistem_informasi_upy;

-- Hapus tabel lama agar struktur dibuat ulang
DROP TABLE IF EXISTS news;
DROP TABLE IF EXISTS admins;

-- =========================================
-- 1. TABEL ADMINS
-- =========================================

CREATE TABLE admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_username (username),
    INDEX idx_admins_role (role)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 2. TABEL NEWS / INFORMASI KAMPUS
-- =========================================

CREATE TABLE news (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Informasi',
    content MEDIUMTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    author_id INT UNSIGNED NOT NULL,
    status ENUM('published', 'draft') NOT NULL DEFAULT 'draft',
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),

    CONSTRAINT fk_news_admin
        FOREIGN KEY (author_id)
        REFERENCES admins(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    INDEX idx_news_status_date (status, published_at),
    INDEX idx_news_author (author_id),
    INDEX idx_news_category (category)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 3. CEK HASIL PEMBUATAN DATABASE
-- =========================================

SHOW TABLES;

DESCRIBE admins;

DESCRIBE news;
