<?php
declare(strict_types=1);

function subscribers(): array
{
    return read_json_file(DATA_PATH . '/subscribers.json');
}

function active_subscribers(): array
{
    return array_values(array_filter(subscribers(), fn(array $item) => ($item['active'] ?? false) === true));
}

function newsletter_rate_allowed(string $ip): bool
{
    $file = DATA_PATH . '/login-attempts.json';
    $entries = read_json_file($file);
    $key = 'newsletter_' . hash('sha256', $ip);
    $recent = array_values(array_filter((array) ($entries[$key]['times'] ?? []), fn($time) => (int) $time > time() - 3600));
    if (count($recent) >= 5) return false;
    $recent[] = time();
    $entries[$key] = ['times' => $recent];
    write_json_file($file, $entries);
    return true;
}

function subscribe_email(string $email, bool $consent): array
{
    $email = mb_strtolower(trim($email), 'UTF-8');
    if (!$consent) return [false, 'É necessário aceitar o consentimento para se inscrever.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) return [false, 'Informe um e-mail válido.'];
    if (!newsletter_rate_allowed(client_ip())) return [false, 'Muitas tentativas. Aguarde antes de tentar novamente.'];
    $items = subscribers();
    foreach ($items as &$item) {
        if (hash_equals((string) $item['email'], $email)) {
            if (($item['active'] ?? false) === true) return [true, 'Este e-mail já está inscrito.'];
            $item['active'] = true;
            $item['consented_at'] = date('c');
            $item['token'] = bin2hex(random_bytes(24));
            write_json_file(DATA_PATH . '/subscribers.json', $items);
            return [true, 'Inscrição reativada com sucesso.'];
        }
    }
    unset($item);
    $items[] = ['email' => $email, 'active' => true, 'token' => bin2hex(random_bytes(24)), 'consented_at' => date('c')];
    return write_json_file(DATA_PATH . '/subscribers.json', $items)
        ? [true, 'Inscrição realizada. Você receberá as próximas publicações.']
        : [false, 'Não foi possível concluir a inscrição agora.'];
}

function unsubscribe_token(string $token): bool
{
    if (!preg_match('/^[a-f0-9]{48}$/', $token)) return false;
    $items = subscribers();
    $found = false;
    foreach ($items as &$item) {
        if (isset($item['token']) && hash_equals((string) $item['token'], $token)) {
            $item['active'] = false;
            $item['unsubscribed_at'] = date('c');
            $found = true;
            break;
        }
    }
    unset($item);
    return $found && write_json_file(DATA_PATH . '/subscribers.json', $items);
}
