<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
start_secure_session();
$wantsJson = str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($wantsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    redirect('/blog.php');
}
verify_csrf();
if (honeypot_filled()) {
    if ($wantsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['ok' => true, 'message' => 'Inscrição realizada.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    redirect('/blog.php?newsletter=ok#newsletter-title');
}
[$ok, $message, $subscriber] = subscribe_email((string) ($_POST['email'] ?? ''), isset($_POST['consent']));
$emailSent = false;
if ($ok && is_array($subscriber)) {
    $emailSent = send_newsletter_welcome($subscriber);
    if (!$emailSent) error_log('Newsletter: inscrição salva, mas o e-mail de confirmação não foi enviado. Verifique a configuração SMTP.');
    $message .= $emailSent
        ? ' Enviamos uma confirmação para sua caixa de entrada.'
        : ' Seu cadastro foi salvo, mas o e-mail de confirmação não pôde ser enviado agora.';
}

if ($wantsJson) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code($ok ? 200 : 422);
    echo json_encode([
        'ok' => $ok,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$_SESSION['flash'] = $message;
redirect('/blog.php?newsletter=' . ($ok ? 'ok' : 'erro') . '#newsletter-title');
