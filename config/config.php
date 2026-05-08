<?php
/**
 * JBeauty · config/config.php
 * ─────────────────────────────────────────────────────────────
 * Database connection via PDO.
 *
 * On Railway  → reads credentials from environment variables
 *               automatically injected by the MySQL service.
 * On XAMPP    → falls back to localhost/root defaults.
 * ─────────────────────────────────────────────────────────────
 */

declare(strict_types=1);

// ── Read from environment (Railway injects these automatically)
// Fallback values are your local XAMPP defaults.
define('DB_HOST',    $_ENV['MYSQLHOST']     ?? getenv('MYSQLHOST')     ?: 'localhost');
define('DB_PORT',    $_ENV['MYSQLPORT']     ?? getenv('MYSQLPORT')     ?: '3306');
define('DB_NAME',    $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?: 'jbeauty');
define('DB_USER',    $_ENV['MYSQLUSER']     ?? getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS',    $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?: '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO instance.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[JBeauty DB] Connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection error. Please try again later.');
        }
    }

    return $pdo;
}