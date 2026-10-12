-- =====================================================================
-- R-RIC · Ronny Research & Innovation Club
-- db.sql: database schema
--
-- Import once with phpMyAdmin (Import tab) or the MySQL CLI:
--     mysql -u root -p < db.sql
--
-- process-join.php also runs these CREATE TABLE IF NOT EXISTS statements
-- automatically, so this file is optional if your user can create tables.
-- On shared hosting (e.g. InfinityFree) the database is created in the
-- control panel: skip the CREATE DATABASE / USE lines and import the rest.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS rric_club
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rric_club;

-- Membership registrations (Join form)
CREATE TABLE IF NOT EXISTS members (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    full_name      VARCHAR(80)   NOT NULL,
    student_id     VARCHAR(20)   NOT NULL,
    email          VARCHAR(120)  NOT NULL,
    phone          VARCHAR(20)   NULL,
    course         VARCHAR(80)   NOT NULL,
    year_of_study  TINYINT UNSIGNED NOT NULL,
    tier           ENUM('explorer', 'builder', 'innovator') NOT NULL DEFAULT 'explorer',
    interests      VARCHAR(255)  NOT NULL,
    motivation     TEXT          NULL,
    event_interest VARCHAR(120)  NULL,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_members_student_id (student_id),
    UNIQUE KEY uq_members_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- General messages (Contact form)
CREATE TABLE IF NOT EXISTS messages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name   VARCHAR(80)  NOT NULL,
    email       VARCHAR(120) NOT NULL,
    subject     VARCHAR(60)  NOT NULL,
    message     TEXT         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Donation pledges (Support Us form). No payment data is ever stored.
CREATE TABLE IF NOT EXISTS donations (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    full_name       VARCHAR(80)   NOT NULL,
    email           VARCHAR(120)  NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    fund            ENUM('general', 'lab', 'prizes', 'travel') NOT NULL DEFAULT 'general',
    payment_method  ENUM('ecocash', 'bank', 'cash') NOT NULL,
    message         VARCHAR(300)  NULL,
    is_anonymous    TINYINT(1)    NOT NULL DEFAULT 0,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
