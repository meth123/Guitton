<?php
declare(strict_types=1);

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) return;
    session_name('guitton_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

function send_security_headers(bool $admin = false): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; frame-src https://calendly.com https://maps.google.com; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
    if (is_https()) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    if ($admin) header('X-Robots-Tag: noindex, nofollow, noarchive');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    start_secure_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    start_secure_session();
    $token = (string) ($_POST['csrf'] ?? '');
    if ($token === '' || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $token)) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
}

function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function password_is_valid(string $password): bool
{
    return strlen($password) >= 6
        && preg_match('/[A-Za-zÀ-ÿ]/u', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[^A-Za-zÀ-ÿ\d]/u', $password);
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function honeypot_filled(): bool
{
    return trim((string) ($_POST['website'] ?? '')) !== '';
}
