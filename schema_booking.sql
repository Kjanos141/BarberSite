-- ============================================================
-- Bukta Zoltán EV — Időpontfoglalás kiegészítő séma
-- Futtatás: mysql -u root -p bukta_zoltan_ev < schema_booking.sql
-- ============================================================

USE bukta_zoltan_ev;

-- ---- SERVICES (Szolgáltatások) ----
CREATE TABLE IF NOT EXISTS services (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120)  NOT NULL,
  description   TEXT          NULL,
  duration      TINYINT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Perc: 30 vagy 60',
  price         DECIMAL(10,0) NULL COMMENT 'Forint, NULL = egyedi árajánlat',
  color         VARCHAR(7)    NOT NULL DEFAULT '#B87333' COMMENT 'Hex szín a naptárban',
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  sort_order    SMALLINT      NOT NULL DEFAULT 0,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- BLOCKED SLOTS (Letiltott időpontok) ----
CREATE TABLE IF NOT EXISTS blocked_slots (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  block_type    ENUM('day','slot','recurring_day','recurring_slot') NOT NULL,
  block_date    DATE          NULL COMMENT 'Egyszeri nap letiltáshoz',
  block_time    TIME          NULL COMMENT 'Adott időpont letiltáshoz',
  weekday       TINYINT       NULL COMMENT '0=Vasárnap, 1=Hétfő, ..., 6=Szombat',
  recurring_time TIME         NULL COMMENT 'Ismétlődő időpont letiltáshoz',
  reason        VARCHAR(255)  NULL,
  valid_from    DATE          NULL,
  valid_until   DATE          NULL,
  created_by    INT UNSIGNED  NOT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_date (block_date),
  INDEX idx_weekday (weekday)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- BOOKINGS (Foglalások) ----
CREATE TABLE IF NOT EXISTS bookings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED  NOT NULL,
  service_id    INT UNSIGNED  NOT NULL,
  booking_date  DATE          NOT NULL,
  booking_time  TIME          NOT NULL,
  status        ENUM('pending','confirmed','cancelled','rejected') NOT NULL DEFAULT 'pending',
  note          TEXT          NULL COMMENT 'Ügyfél megjegyzése',
  admin_note    TEXT          NULL COMMENT 'Admin belső megjegyzése',
  is_recurring  TINYINT(1)    NOT NULL DEFAULT 0,
  recurring_group_id INT UNSIGNED NULL COMMENT 'Ismétlődő foglalások csoportazonosítója',
  confirmed_by  INT UNSIGNED  NULL,
  confirmed_at  DATETIME      NULL,
  cancelled_at  DATETIME      NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
  FOREIGN KEY (confirmed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_date   (booking_date),
  INDEX idx_status (status),
  INDEX idx_user   (user_id),
  INDEX idx_recurring (recurring_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- ACTIVITY LOG ----
CREATE TABLE IF NOT EXISTS activity_log (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED  NULL,
  action        VARCHAR(80)   NOT NULL COMMENT 'pl: booking.create, user.login, invite.send',
  entity_type   VARCHAR(40)   NULL COMMENT 'pl: booking, user, service',
  entity_id     INT UNSIGNED  NULL,
  description   TEXT          NULL,
  ip_address    VARCHAR(45)   NULL,
  user_agent    VARCHAR(255)  NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_action    (action),
  INDEX idx_user      (user_id),
  INDEX idx_entity    (entity_type, entity_id),
  INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- ALAPÉRTELMEZETT SZOLGÁLTATÁSOK ----
INSERT INTO services (name, description, duration, price, color, sort_order) VALUES
  ('Hajvágás',          'Klasszikus hajvágás mosással',          60, 4500,  '#B87333', 1),
  ('Szakáll igazítás',  'Szakáll formázás és igazítás',          30, 2500,  '#7A4D22', 2),
  ('Hajvágás + Szakáll','Kombinált hajvágás és szakáll kezelés', 60, 6500,  '#D4935A', 3),
  ('Egyhosszra vágás',  'Egyenletes, egy hosszra vágás',         30, 3500,  '#8B6914', 4),
  ('Borotválás',        'Hagyományos borotválás törölközővel',   60, 3000,  '#5C4033', 5)
ON DUPLICATE KEY UPDATE id = id;
