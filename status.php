<?php
/**
 * R-RIC · status.php: deployment health check.
 *
 * Shows whether the database connection works and which settings the server
 * actually received, without revealing secrets (the password is never shown,
 * only its length and first/last characters; the user name is shortened).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

function mask(string $value): string
{
    $length = strlen($value);
    if ($length <= 4) {
        return str_repeat('*', $length);
    }
    return $value[0] . str_repeat('*', $length - 2) . $value[$length - 1];
}

function short_user(string $user): string
{
    return strlen($user) > 10 ? substr($user, 0, 4) . '…' . substr($user, -5) : $user;
}

$error = '';
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        array_filter([
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
            PDO::MYSQL_ATTR_SSL_CA => DB_SSL === '' ? null : (DB_SSL === 'true' ? '/etc/ssl/certs/ca-certificates.crt' : DB_SSL),
        ], fn ($v) => $v !== null)
    );
    $database = 'CONNECTED (' . $pdo->query('SELECT VERSION()')->fetchColumn() . ')';
} catch (PDOException $ex) {
    $database = 'FAILED';
    $error = $ex->getMessage();
}

echo "R-RIC status\n============\n";
echo "Database : $database\n";
if ($error !== '') {
    echo "Error    : $error\n";
}
echo "\nSettings received from the environment:\n";
printf("  DB_HOST  %s\n", DB_HOST);
printf("  DB_PORT  %s\n", DB_PORT);
printf("  DB_NAME  %s\n", DB_NAME);
printf("  DB_USER  %s (%d chars)\n", short_user(DB_USER), strlen(DB_USER));
printf("  DB_PASS  %s (%d chars)\n", mask(DB_PASS), strlen(DB_PASS));
printf("  DB_SSL   %s\n", DB_SSL === '' ? '(off)' : DB_SSL);
echo "\nVariables set: " . implode(', ', array_filter(
    ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_SSL'],
    fn ($k) => getenv($k) !== false
)) . "\n";
