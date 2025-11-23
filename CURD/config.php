<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'company';

const AUTH_COOKIE = 'company_portal_auth';
const COOKIE_LIFETIME = 60 * 60 * 24 * 30; // 30 days

/**
 * Returns an active mysqli connection or terminates with a readable error.
 */
function get_db_connection(): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error . '<br>Please check your database credentials in config.php');
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}

/**
 * Simple helper to ensure strings are safe to echo in HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Store authenticated user data in the session and optionally issue a remember cookie.
 */
function login_user(mysqli $conn, array $user, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
    ];

    if ($remember) {
        remember_user($conn, (int) $user['id']);
    } else {
        forget_user_cookie($conn, (int) $user['id']);
    }
}

/**
 * Remove everything that keeps the user authenticated.
 */
function logout_user(): void
{
    $userId = $_SESSION['user']['id'] ?? null;
    if ($userId) {
        $conn = get_db_connection();
        forget_user_cookie($conn, (int) $userId);
    } else {
        forget_user_cookie();
    }

    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }

    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
    ]);
}

/**
 * Issue a random remember-me cookie tied to the user record.
 */
function remember_user(mysqli $conn, int $userId): void
{
    $token = bin2hex(random_bytes(32));
    $expires = time() + COOKIE_LIFETIME;
    $hash = password_hash($token, PASSWORD_BCRYPT);

    $stmt = $conn->prepare('UPDATE users SET remember_token_hash = ?, remember_token_expires = ? WHERE id = ?');
    $stmt->bind_param('sii', $hash, $expires, $userId);
    $stmt->execute();
    $stmt->close();

    setcookie(
        AUTH_COOKIE,
        $userId . ':' . $token,
        [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Remove the remember-me cookie (optionally clearing the DB record too).
 */
function forget_user_cookie(?mysqli $conn = null, ?int $userId = null): void
{
    if ($conn && $userId) {
        $stmt = $conn->prepare('UPDATE users SET remember_token_hash = NULL, remember_token_expires = NULL WHERE id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }

    setcookie(
        AUTH_COOKIE,
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Attempt to restore the logged-in user from the cookie.
 */
function ensure_user_from_cookie(): void
{
    if (isset($_SESSION['user']) || empty($_COOKIE[AUTH_COOKIE])) {
        return;
    }

    [$userId, $token] = array_pad(explode(':', $_COOKIE[AUTH_COOKIE], 2), 2, '');
    $userId = (int) $userId;
    if (!$userId || !$token) {
        return;
    }

    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT id, name, email, remember_token_hash, remember_token_expires FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (
        !$user ||
        empty($user['remember_token_hash']) ||
        ($user['remember_token_expires'] ?? 0) < time() ||
        !password_verify($token, $user['remember_token_hash'])
    ) {
        forget_user_cookie($conn, $userId);
        return;
    }

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
    ];
}

/**
 * Quickly fetch the currently authenticated user.
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Guard helper for pages that require authentication.
 */
function require_login(): void
{
    if (!current_user()) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'] ?? null;
        header('Location: login.php');
        exit;
    }
}

// Run on every request so sessions are automatically restored when possible.
ensure_user_from_cookie();

