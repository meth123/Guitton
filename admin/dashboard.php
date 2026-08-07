<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$posts = all_posts();
$publishedCount = count(array_filter($posts, fn(array $post): bool => ($post['status'] ?? '') === 'published'));
$draftCount = count($posts) - $publishedCount;
$adminTitle = 'Publicações';
require dirname(__DIR__) . '/partials/admin-header.php';
?>
<div class="admin-page-head dashboard-heading">
  <div>
    <p class="admin-eyebrow">CONTEÚDO</p>
    <h1>Publicações</h1>
    <p>Crie histórias, revise conteúdos e escolha o melhor momento para publicar.</p>
  </div>
  <a class="admin-button admin-button-icon" href="/admin/edit.php">Nova publicação <span aria-hidden="true">＋</span></a>
</div>

<?php start_secure_session(); if (!empty($_SESSION['admin_flash'])): ?>
  <div class="admin-alert success" role="status"><?= e((string) $_SESSION['admin_flash']) ?></div>
<?php unset($_SESSION['admin_flash']); endif; ?>

<section class="dashboard-stats" aria-label="Resumo das publicações">
  <article><span>Total</span><strong><?= count($posts) ?></strong><small>conteúdos criados</small></article>
  <article><span>Publicados</span><strong><?= $publishedCount ?></strong><small>visíveis no blog</small></article>
  <article><span>Rascunhos</span><strong><?= $draftCount ?></strong><small>aguardando revisão</small></article>
</section>

<a class="admin-tip" href="/admin/docs.php">
  <span class="admin-tip-icon" aria-hidden="true">✦</span>
  <span><strong>Primeira vez usando o blog?</strong><small>Veja o tutorial simples, as recomendações de imagem e o checklist antes de publicar.</small></span>
  <b>Ver tutorial <span aria-hidden="true">→</span></b>
</a>

<?php if (!$posts): ?>
  <div class="admin-empty polished-empty">
    <span class="empty-symbol" aria-hidden="true">✦</span>
    <h2>Seu espaço editorial está pronto</h2>
    <p>Comece com um rascunho e veja a prévia com calma antes de apresentar a primeira publicação.</p>
    <a class="admin-button" href="/admin/edit.php">Criar primeira publicação</a>
  </div>
<?php else: ?>
  <section class="publication-list" aria-label="Lista de publicações">
    <?php foreach ($posts as $post):
      $isPublished = ($post['status'] ?? '') === 'published';
      $image = (string) ($post['image'] ?? '');
      $imagePath = $image !== '' && is_file(ROOT_PATH . '/' . ltrim($image, '/')) ? '/' . ltrim($image, '/') : '/assets/guitton-social.jpg';
    ?>
      <article class="publication-row">
        <a class="publication-thumb" href="/admin/edit.php?id=<?= e((string) $post['id']) ?>" aria-label="Editar <?= e((string) $post['title']) ?>">
          <img src="<?= e($imagePath) ?>" alt="" loading="lazy">
        </a>
        <div class="publication-copy">
          <div class="publication-meta">
            <span class="status-badge <?= e((string) $post['status']) ?>"><?= $isPublished ? 'Publicado' : 'Rascunho' ?></span>
            <time datetime="<?= e((string) ($post['updated_at'] ?? '')) ?>"><?= e(format_date_br($post['updated_at'] ?? null)) ?></time>
          </div>
          <h2><a href="/admin/edit.php?id=<?= e((string) $post['id']) ?>"><?= e((string) $post['title']) ?></a></h2>
          <p><?= e((string) ($post['summary'] ?? '')) ?></p>
        </div>
        <div class="publication-actions">
          <a href="/admin/edit.php?id=<?= e((string) $post['id']) ?>">Editar</a>
          <?php if ($isPublished): ?><a href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>" target="_blank" rel="noopener">Visualizar <span aria-hidden="true">↗</span></a><?php endif; ?>
          <form action="/admin/delete.php" method="post" data-confirm="Excluir esta publicação? Esta ação não pode ser desfeita.">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= e((string) $post['id']) ?>">
            <button type="submit">Excluir</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
