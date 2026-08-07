<?php
declare(strict_types=1);

function load_local_env(string $file): void
{
    if (!is_file($file) || !is_readable($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = array_map('trim', explode('=', $line, 2));
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name) || getenv($name) !== false) {
            continue;
        }
        $value = trim($value, "\"'");
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

load_local_env(dirname(__DIR__) . '/.env');

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false || $value === '' ? $default : (string) $value;
}

define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('SITE_URL', rtrim(env_value('SITE_URL', 'https://guitton.com.br'), '/'));
define('SITE_NAME', 'Guitton');
define('DEFAULT_SOCIAL_IMAGE', SITE_URL . '/assets/guitton-social.jpg');
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);

foreach ([DATA_PATH, UPLOAD_PATH] as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}
