<?php
declare(strict_types=1);

function read_json_file(string $file, array $fallback = []): array
{
    if (!is_file($file)) {
        return $fallback;
    }
    $contents = file_get_contents($file);
    if ($contents === false || trim($contents) === '') {
        return $fallback;
    }
    $decoded = json_decode($contents, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function write_json_file(string $file, array $data): bool
{
    $directory = dirname($file);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        return false;
    }
    $lockFile = $file . '.lock';
    $lock = fopen($lockFile, 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        if (is_resource($lock)) fclose($lock);
        return false;
    }
    $temporary = $file . '.' . bin2hex(random_bytes(6)) . '.tmp';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $written = $json !== false && file_put_contents($temporary, $json . PHP_EOL, LOCK_EX) !== false;
    if ($written) {
        @chmod($temporary, 0644);
        $written = DIRECTORY_SEPARATOR === '\\' && is_file($file)
            ? copy($temporary, $file)
            : rename($temporary, $file);
    }
    if (is_file($temporary)) unlink($temporary);
    flock($lock, LOCK_UN);
    fclose($lock);
    return $written;
}
