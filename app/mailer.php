<?php
declare(strict_types=1);

function smtp_read($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') break;
    }
    return $response;
}

function smtp_command($socket, string $command, array $expected): bool
{
    fwrite($socket, $command . "\r\n");
    $response = smtp_read($socket);
    return in_array((int) substr($response, 0, 3), $expected, true);
}

function send_smtp_mail(string $to, string $subject, string $html, string $text): bool
{
    $host = env_value('SMTP_HOST');
    $port = (int) env_value('SMTP_PORT', '587');
    $username = env_value('SMTP_USERNAME');
    $password = env_value('SMTP_PASSWORD');
    $encryption = strtolower(env_value('SMTP_ENCRYPTION', 'tls'));
    $fromEmail = env_value('SMTP_FROM_EMAIL');
    $fromName = env_value('SMTP_FROM_NAME', 'Guitton');
    if ($host === '' || $fromEmail === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $target = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
    $socket = @stream_socket_client($target, $errorNumber, $errorMessage, 12, STREAM_CLIENT_CONNECT);
    if (!$socket) return false;
    stream_set_timeout($socket, 12);
    if ((int) substr(smtp_read($socket), 0, 3) !== 220) { fclose($socket); return false; }
    $hostname = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['SERVER_NAME'] ?? 'guitton.com.br') ?: 'guitton.com.br';
    if (!smtp_command($socket, 'EHLO ' . $hostname, [250])) { fclose($socket); return false; }
    if ($encryption === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', [220]) || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($socket); return false; }
        if (!smtp_command($socket, 'EHLO ' . $hostname, [250])) { fclose($socket); return false; }
    }
    if ($username !== '') {
        if (!smtp_command($socket, 'AUTH LOGIN', [334])
            || !smtp_command($socket, base64_encode($username), [334])
            || !smtp_command($socket, base64_encode($password), [235])) { fclose($socket); return false; }
    }
    if (!smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250])
        || !smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251])
        || !smtp_command($socket, 'DATA', [354])) { fclose($socket); return false; }
    $boundary = 'guitton_' . bin2hex(random_bytes(12));
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFrom = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $headers = [
        'From: ' . $encodedFrom . ' <' . $fromEmail . '>',
        'To: <' . $to . '>',
        'Subject: ' . $encodedSubject,
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . $hostname . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    $body = implode("\r\n", $headers) . "\r\n\r\n"
        . '--' . $boundary . "\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text)) . "\r\n"
        . '--' . $boundary . "\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html)) . "\r\n--" . $boundary . "--\r\n";
    $body = preg_replace('/(?m)^\./', '..', $body) ?? $body;
    fwrite($socket, $body . ".\r\n");
    $ok = (int) substr(smtp_read($socket), 0, 3) === 250;
    smtp_command($socket, 'QUIT', [221]);
    fclose($socket);
    return $ok;
}

function newsletter_message(array $post, array $subscriber): array
{
    $postUrl = absolute_url('/post.php?slug=' . rawurlencode((string) $post['slug']));
    $unsubscribeUrl = absolute_url('/unsubscribe.php?token=' . rawurlencode((string) $subscriber['token']));
    $title = (string) $post['title'];
    $summary = (string) $post['summary'];
    $logo = absolute_url('/assets/images/logo-guitton.png');
    $html = '<!doctype html><html lang="pt-BR"><head><meta name="viewport" content="width=device-width"><meta charset="UTF-8"></head><body style="margin:0;background:#f2f5f3;font-family:Arial,sans-serif;color:#161918"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:28px 12px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;margin:auto;background:#fff;border-radius:12px;overflow:hidden"><tr><td style="padding:28px;text-align:center;border-top:5px solid #078b37"><img src="' . e($logo) . '" width="190" alt="Guitton" style="max-width:55%;height:auto"></td></tr><tr><td style="padding:8px 36px 36px"><p style="color:#078b37;font-size:12px;letter-spacing:3px">NOVA PUBLICAÇÃO</p><h1 style="font-size:28px;line-height:1.25">' . e($title) . '</h1><p style="font-size:16px;line-height:1.65;color:#474d4a">' . e($summary) . '</p><p style="padding:18px 0"><a href="' . e($postUrl) . '" style="display:inline-block;padding:13px 24px;border-radius:999px;background:#078b37;color:#fff;text-decoration:none">Ler publicação</a></p></td></tr><tr><td style="padding:22px 36px;background:#eef5f0;color:#59615d;font-size:12px;line-height:1.6">Guitton — Administração de imóveis e consultoria imobiliária em Santos, SP.<br><a href="' . e($unsubscribeUrl) . '" style="color:#078b37">Cancelar inscrição</a></td></tr></table></td></tr></table></body></html>';
    $text = "Guitton — Nova publicação\n\n{$title}\n\n{$summary}\n\nLer publicação: {$postUrl}\n\nCancelar inscrição: {$unsubscribeUrl}";
    return [$html, $text];
}

function notify_subscribers(array $post): array
{
    $sent = 0;
    $failed = 0;
    foreach (active_subscribers() as $subscriber) {
        [$html, $text] = newsletter_message($post, $subscriber);
        if (send_smtp_mail((string) $subscriber['email'], 'Nova publicação: ' . $post['title'], $html, $text)) $sent++; else $failed++;
    }
    return ['sent' => $sent, 'failed' => $failed];
}
