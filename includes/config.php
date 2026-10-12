<?php
/**
 * R-RIC · database & storage configuration
 *
 * Values are read from environment variables first (Render, Docker, VPS),
 * then fall back to the defaults below (XAMPP / WAMP / InfinityFree).
 *
 * Render + TiDB Cloud example (set in Render → Environment):
 *   DB_HOST = gateway01.eu-central-1.prod.aws.tidbcloud.com
 *   DB_PORT = 4000
 *   DB_NAME = test
 *   DB_USER = 2abcDEF.root
 *   DB_PASS = (from TiDB "Connect" dialog)
 *   DB_SSL  = true
 *
 * InfinityFree example (from Control Panel → MySQL Databases):
 *   DB_HOST = 'sql123.infinityfree.com'
 *   DB_NAME = 'if0_12345678_rric'
 *   DB_USER = 'if0_12345678'
 *   DB_PASS = 'your-vpanel-password'
 *
 * If the database is unreachable, submissions are written to JSON files in
 * the /data folder instead, so the forms keep working.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'rric_club');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

// Encrypted connection, required by cloud MySQL hosts such as TiDB Cloud or Aiven.
//   ''      → no TLS (XAMPP, InfinityFree)
//   'true'  → TLS using the server's built-in CA bundle (TiDB Cloud)
//   a path  → TLS using that CA certificate file (e.g. Aiven's ca.pem)
define('DB_SSL', getenv('DB_SSL') ?: '');

// Local time for created_at in the JSON fallback and on confirmation pages.
date_default_timezone_set('Africa/Harare');

// Folder for the JSON fallback (protected by data/.htaccess).
define('DATA_DIR', dirname(__DIR__) . '/data');

// Validation patterns: keep these in sync with js/main.js.
define('STUDENT_ID_PATTERN', '~^[A-Z]{2,4}/(\d{4})/\d{3,5}$~');
define('UNI_EMAIL_PATTERN', '~^[A-Za-z0-9._%+-]+@(?:[A-Za-z0-9-]+\.)+(?:ac\.[A-Za-z]{2}|edu(?:\.[A-Za-z]{2})?)$~');
define('NAME_PATTERN', "~^[A-Za-z][A-Za-z .'-]{1,58}[A-Za-z.]$~");
define('PHONE_PATTERN', '~^\+?\d[\d\s-]{7,15}\d$~');
