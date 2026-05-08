<?php
declare(strict_types=1);

// ── Start session so we can destroy it ──────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. Clear all session variables ──────────────────────────────
$_SESSION = [];

// ── 2. Destroy the session cookie on the client ─────────────────
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

// ── 3. Destroy the session on the server ────────────────────────
session_destroy();

header('Location: login.php');
exit;