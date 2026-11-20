-- Import via cPanel phpMyAdmin after creating a database and DB user.
-- The application does not use the WordPress database or WordPress tables.
CREATE TABLE IF NOT EXISTS nitiksham_records (
  id VARCHAR(40) NOT NULL PRIMARY KEY,
  kind ENUM('design','newsletter','software') NOT NULL,
  title VARCHAR(191) NOT NULL,
  description TEXT NOT NULL,
  mime VARCHAR(80) NOT NULL DEFAULT '',
  published TINYINT(1) NOT NULL DEFAULT 0,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  live_url TEXT NULL,
  demo_url TEXT NULL,
  file_name VARCHAR(90) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_published_kind (published, archived, kind),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO nitiksham_records (id, kind, title, description, published, created_at)
VALUES ('stockdesk', 'software', 'Stockdesk', 'Plumbing inventory and stock management workspace.', 1, '2026-11-18 00:00:00')
ON DUPLICATE KEY UPDATE id = id;
