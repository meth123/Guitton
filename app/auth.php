<?php
declare(strict_types=1);

function admins(): array
{
    return read_json_file(DATA_PATH . '/admins.json');
}

function admin_exists(): bool
{
    return count(admins()) > 0;
}

function is_admin(): bool
{
    start_secure_session();
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_authenticated']);
}

function require_admin(): void
{
    if (!is_admin()) redirect('/admin/');
}

function login_key(string $username, string $ip): string
{
    return hash('sha256', mb_strtolower(trim($username), 'UTF-8') . '|' . $ip);
}

function login_status(string $username, string $ip): array
{
    $attempts = read_json_file(DATA_PATH . '/login-attempts.json');
    $entry = $attempts[login_key($username, $ip)] ?? ['count' => 0, 'blocked_until' => 0];
    return ['count' => (int) ($entry['count'] ?? 0), 'blocked_until' => (int) ($entry['blocked_until'] ?? 0)];
}

function record_failed_login(string $username, string $ip): int
{
    $file = DATA_PATH . '/login-attempts.json';
    $attempts = read_json_file($file);
    $key = login_key($username, $ip);
    $current = $attempts[$key] ?? ['count' => 0, 'last' => 0, 'blocked_until' => 0];
    if ((int) ($current['last'] ?? 0) < time() - 1800) $current['count'] = 0;
    $current['count'] = (int) $current['count'] + 1;
    $current['last'] = time();
    $delay = $current['count'] >= 5 ? min(1800, 30 * (2 ** min(6, $current['count'] - 5))) : min(8, $current['count']);
    $current['blocked_until'] = time() + $delay;
    $attempts[$key] = $current;
    write_json_file($file, $attempts);
    return $delay;
}

function clear_login_attempts(string $username, string $ip): void
{
    $file = DATA_PATH . '/login-attempts.json';
    $attempts = read_json_file($file);
    unset($attempts[login_key($username, $ip)]);
    write_json_file($file, $attempts);
}

function attempt_admin_login(string $username, string $password): array
{
    $ip = client_ip();
    $status = login_status($username, $ip);
    if ($status['blocked_until'] > time()) return [false, 'Aguarde ' . ($status['blocked_until'] - time()) . ' segundos antes de tentar novamente.'];
    $admin = admins()[0] ?? null;
    $valid = is_array($admin)
        && hash_equals(mb_strtolower((string) ($admin['username'] ?? ''), 'UTF-8'), mb_strtolower(trim($username), 'UTF-8'))
        && password_verify($password, (string) ($admin['password_hash'] ?? ''));
    if (!$valid) {
        $delay = record_failed_login($username, $ip);
        return [false, 'Usuário ou senha inválidos. Aguarde ' . $delay . ' segundo(s) para tentar novamente.'];
    }
    clear_login_attempts($username, $ip);
    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return [true, ''];
}
