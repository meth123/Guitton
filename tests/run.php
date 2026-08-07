<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$passed = 0;
$failed = 0;
function check(bool $condition, string $label): void
{
    global $passed, $failed;
    if ($condition) { echo "[OK] {$label}\n"; $passed++; }
    else { echo "[FALHA] {$label}\n"; $failed++; }
}

check(slugify('Título da Notícia!') === 'titulo-da-noticia', 'slug sem acentos e caracteres perigosos');
check(password_is_valid('Abc1!x') && !password_is_valid('abcdef') && !password_is_valid('123456!'), 'política de senha');
$dirty = '<h2 onclick="x">Título</h2><script>alert(1)</script><p>Texto <strong>seguro</strong></p><a href="javascript:alert(1)">ruim</a>';
$clean = sanitize_post_html($dirty);
check(!str_contains($clean, '<script') && !str_contains($clean, 'onclick') && !str_contains($clean, 'javascript:'), 'sanitização remove scripts, eventos e protocolos perigosos');
check(str_contains($clean, '<h2>Título</h2>') && str_contains($clean, '<strong>seguro</strong>'), 'sanitização preserva formatação permitida');
check(reading_time(str_repeat('palavra ', 401)) === 3, 'tempo estimado de leitura');

$runtime = __DIR__ . '/runtime';
if (!is_dir($runtime)) mkdir($runtime, 0755, true);
$testFile = $runtime . '/storage.json';
check(write_json_file($testFile, ['ok' => true, 'texto' => 'ação']), 'gravação JSON atômica');
check(read_json_file($testFile) === ['ok' => true, 'texto' => 'ação'], 'leitura JSON UTF-8');

$fakePost = ['image' => 'assets/inexistente.jpg'];
check(post_image_url($fakePost) === DEFAULT_SOCIAL_IMAGE, 'fallback de imagem social');
check(absolute_url('/blog.php') === SITE_URL . '/blog.php', 'URL absoluta HTTPS configurada');

[$invalidSubscription] = subscribe_email('email-invalido', true);
check($invalidSubscription === false, 'newsletter rejeita endereço de e-mail inválido');
[$welcomeHtml, $welcomeText] = newsletter_welcome_message(['token' => str_repeat('a', 48)]);
check(str_contains($welcomeHtml, 'INSCRIÇÃO CONFIRMADA') && str_contains($welcomeText, 'Cancelar inscrição'), 'mensagem de confirmação da newsletter');

$blockIp = '192.0.2.55';
for ($attempt = 0; $attempt < 5; $attempt++) record_failed_login('blocked_test_user', $blockIp);
$blocked = login_status('blocked_test_user', $blockIp);
check($blocked['count'] >= 5 && $blocked['blocked_until'] > time(), 'bloqueio temporário e progressivo após tentativas de login');

$xml = shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__) . '/sitemap.php'));
check(is_string($xml) && str_contains($xml, '<urlset') && str_contains($xml, SITE_URL . '/blog.php'), 'sitemap dinâmico válido na estrutura');

echo "\n{$passed} teste(s) passaram; {$failed} falharam.\n";
exit($failed > 0 ? 1 : 0);
