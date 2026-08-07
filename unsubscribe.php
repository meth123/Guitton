<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/public-head.php';
$ok = unsubscribe_token((string) ($_GET['token'] ?? ''));
render_public_head(['title' => 'Cancelar inscrição | Guitton', 'description' => 'Cancelamento da newsletter Guitton.', 'canonical' => '/unsubscribe.php', 'robots' => 'noindex, nofollow']);
$activePage = 'blog';
?>
<body><?php require __DIR__ . '/partials/public-header.php'; ?><main><section class="status-page"><p class="eyebrow">NEWSLETTER</p><h1><?= $ok ? 'Inscrição cancelada' : 'Link inválido ou expirado' ?></h1><p><?= $ok ? 'Você não receberá novos avisos. Se mudar de ideia, poderá se inscrever novamente.' : 'Não foi possível localizar uma inscrição ativa para este link.' ?></p><a class="outline-button" href="/blog.php">Voltar ao blog</a></section></main><?php require __DIR__ . '/partials/public-footer.php'; ?>
