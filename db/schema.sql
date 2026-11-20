-- Nitiksham site database schema.
-- Portable SQL: runs on MySQL (production) and SQLite (tests).
-- For MySQL, create the database with utf8mb4 first, for example:
--   CREATE DATABASE nitiksham CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- The installer (api/install.php) runs this file. It is safe to run twice:
-- tables use IF NOT EXISTS and the seed insert skips when present.

CREATE TABLE IF NOT EXISTS records (
  id VARCHAR(40) NOT NULL,
  kind VARCHAR(24) NOT NULL,
  title VARCHAR(191) NOT NULL,
  description TEXT NOT NULL,
  mime VARCHAR(80) NOT NULL DEFAULT '',
  published TINYINT(1) NOT NULL DEFAULT 0,
  archived TINYINT(1) NOT NULL DEFAULT 0,
  live_url TEXT,
  demo_url TEXT,
  owner_id VARCHAR(40) NOT NULL DEFAULT '',
  file_name VARCHAR(255) NOT NULL DEFAULT '',
  file_size INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
);
CREATE INDEX idx_records_published_kind ON records (published, kind);

CREATE TABLE IF NOT EXISTS admins (
  id VARCHAR(40) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS enquiries (
  id VARCHAR(40) NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(255) NOT NULL,
  company VARCHAR(120) NOT NULL DEFAULT '',
  phone VARCHAR(60) NOT NULL DEFAULT '',
  service VARCHAR(120) NOT NULL DEFAULT '',
  message TEXT NOT NULL,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
);
CREATE INDEX idx_enquiries_created_at ON enquiries (created_at);

CREATE TABLE IF NOT EXISTS login_attempts (
  ip VARCHAR(45) NOT NULL,
  email VARCHAR(255) NOT NULL,
  attempted_at DATETIME NOT NULL
);
CREATE INDEX idx_login_attempts_ip_email ON login_attempts (ip, email, attempted_at);

-- Seed record, carried over from the WordPress plugin.
INSERT INTO records (id, kind, title, description, mime, published, archived, owner_id, file_name, file_size, created_at)
SELECT 'stockdesk', 'software', 'Stockdesk', 'Plumbing inventory and stock management workspace.', '', 1, 0, '', '', 0, '2026-11-18 00:00:00'
WHERE NOT EXISTS (SELECT 1 FROM records WHERE id = 'stockdesk');
