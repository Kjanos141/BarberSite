-- ============================================================
-- Blackstone Barber — MySQL Database Schema
-- Futtatás: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS blackstone_barber
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE blackstone_barber;

-- ---- USERS ----
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)  NOT NULL,
  email         VARCHAR(255)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  status        ENUM('active', 'inactive')    NOT NULL DEFAULT 'active',
  last_login    DATETIME      NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email  (email),
  INDEX idx_status (status),
  INDEX idx_role   (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- INVITES ----
CREATE TABLE IF NOT EXISTS invites (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(255)  NOT NULL,
  name        VARCHAR(120)  NOT NULL DEFAULT '',
  role        ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  token       CHAR(64)      NOT NULL UNIQUE,
  invited_by  INT UNSIGNED  NULL,
  note        TEXT          NULL,
  status      ENUM('pending', 'used', 'revoked') NOT NULL DEFAULT 'pending',
  expires_at  DATETIME      NOT NULL,
  used_at     DATETIME      NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_token      (token),
  INDEX idx_email      (email),
  INDEX idx_status     (status),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEFAULT ADMIN ACCOUNT
-- Email: admin@blackstonebarber.hu
-- Jelszó: Admin1234! (változtasd meg bejelentkezés után!)
-- ============================================================
INSERT INTO users (name, email, password_hash, role, status) VALUES (
  'Adminisztrátor',
  'admin@blackstonebarber.hu',
  '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8n1oF9K5CePv7rYwXXe',
  'admin',
  'active'
) ON DUPLICATE KEY UPDATE id = id;

-- Megjegyzés: a fenti hash az 'Admin1234!' jelszóhoz tartozik
-- Generálj sajátot: php -r "echo password_hash('UjJelszó!', PASSWORD_BCRYPT, ['cost'=>12]);"
