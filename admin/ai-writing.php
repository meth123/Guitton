<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/gemini.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_admin();
verify_csrf();
start_secure_session();

$now = time();
$requests = array_values(array_filter(
    (array) ($_SESSION['ai_writing_requests'] ?? []),
    static fn($timestamp): bool => is_int($timestamp) && $timestamp > $now - 600
));
if (count($requests) >= 10) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'message' => 'Limite temporário atingido. Aguarde alguns minutos.'], JSON_UNESCAPED_UNICODE);
    exit;
}
$requests[] = $now;
$_SESSION['ai_writing_requests'] = $requests;

$action = trim((string) ($_POST['ai_action'] ?? ''));
$title = trim((string) ($_POST['title'] ?? ''));
$summary = trim((string) ($_POST['summary'] ?? ''));
$content = sanitize_post_html((string) ($_POST['content'] ?? ''));

if (!in_array($action, ['create', 'improve', 'continue'], true)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Escolha uma ação válida.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($title === '' || $summary === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Preencha o título e o resumo antes de usar a IA.'], JSON_UNESCAPED_UNICODE);
    exit;
}
if (($action === 'improve' || $action === 'continue') && plain_text_from_html($content) === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Escreva um pouco do texto antes de escolher esta ação.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $result = generate_post_with_gemini(
        $action,
        mb_substr($title, 0, 150, 'UTF-8'),
        mb_substr($summary, 0, 320, 'UTF-8'),
        mb_substr($content, 0, 30000, 'UTF-8')
    );
    echo json_encode(['ok' => true] + $result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    error_log('Gemini writing assistant: ' . $error->getMessage());
    http_response_code(502);
    echo json_encode(['ok' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
