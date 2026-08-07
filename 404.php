<?php
declare(strict_types=1);
if (!defined('ROOT_PATH')) require __DIR__ . '/app/bootstrap.php';
if (!function_exists('render_public_head')) require __DIR__ . '/partials/public-head.php';
http_response_code(404);
render_public_head(['title' => 'Página não encontrada | Guitton', 'description' => 'A página solicitada não foi encontrada.', 'canonical' => '/404.php', 'robots' => 'noindex, nofollow']);
$activePage = '';
?>
<body><?php require __DIR__ . '/partials/public-header.php'; ?><main><section class="status-page"><p class="eyebrow">ERRO 404</p><h1>Página não encontrada</h1><p>O endereço pode ter mudado ou não existe mais.</p><a class="outline-button" href="/index.html">Voltar ao início</a></section></main><?php require __DIR__ . '/partials/public-footer.php'; ?>
