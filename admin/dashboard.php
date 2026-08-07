<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
$posts = all_posts();
$adminTitle = 'Publicações'; require dirname(__DIR__) . '/partials/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-eyebrow">CONTEÚDO</p><h1>Publicações</h1><p>Crie, revise e publique notícias do blog.</p></div><a class="admin-button" href="/admin/edit.php">Nova publicação</a></div>
<?php start_secure_session(); if (!empty($_SESSION['admin_flash'])): ?><div class="admin-alert success" role="status"><?= e((string) $_SESSION['admin_flash']) ?></div><?php unset($_SESSION['admin_flash']); endif; ?>
<?php if (!$posts): ?><div class="admin-empty"><h2>Nenhuma publicação ainda</h2><p>Comece criando um rascunho. Você poderá conferir a prévia antes de publicar.</p><a class="admin-button" href="/admin/edit.php">Criar primeira publicação</a></div><?php else: ?><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Título</th><th>Status</th><th>Atualização</th><th>Ações</th></tr></thead><tbody><?php foreach ($posts as $post): ?><tr><td><strong><?= e((string) $post['title']) ?></strong><small>/<?= e((string) $post['slug']) ?></small></td><td><span class="status-badge <?= e((string) $post['status']) ?>"><?= ($post['status'] ?? '') === 'published' ? 'Publicado' : 'Rascunho' ?></span></td><td><?= e(format_date_br($post['updated_at'] ?? null)) ?></td><td class="table-actions"><a href="/admin/edit.php?id=<?= e((string) $post['id']) ?>">Editar</a><?php if (($post['status'] ?? '') === 'published'): ?><a href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>" target="_blank">Ver</a><?php endif; ?><form action="/admin/delete.php" method="post" data-confirm="Excluir esta publicação? Esta ação não pode ser desfeita."><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string) $post['id']) ?>"><button type="submit">Excluir</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
