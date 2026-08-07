<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/public-head.php';
start_secure_session();

$query = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;
$posts = published_posts();
if ($query !== '') {
    $needle = mb_strtolower($query, 'UTF-8');
    $posts = array_values(array_filter($posts, function (array $post) use ($needle): bool {
        $haystack = mb_strtolower(($post['title'] ?? '') . ' ' . ($post['summary'] ?? '') . ' ' . plain_text_from_html($post['content'] ?? ''), 'UTF-8');
        return str_contains($haystack, $needle);
    }));
}
$totalPages = max(1, (int) ceil(count($posts) / $perPage));
if ($page > $totalPages) $page = $totalPages;
$visible = array_slice($posts, ($page - 1) * $perPage, $perPage);
$featured = $query === '' && $page === 1 ? array_shift($visible) : null;
$canonicalPath = '/blog.php' . ($page > 1 ? '?page=' . $page : '');
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'Blog Guitton',
    'description' => 'Conteúdos sobre imóveis, contratos e planejamento patrimonial.',
    'url' => absolute_url($canonicalPath),
    'publisher' => ['@type' => 'Organization', 'name' => 'Guitton', 'logo' => ['@type' => 'ImageObject', 'url' => absolute_url('/assets/images/logo-guitton.png')]],
];
render_public_head([
    'title' => 'Blog Guitton | Imóveis, contratos e patrimônio',
    'description' => 'Informação clara sobre administração de imóveis, contratos, locação e planejamento patrimonial.',
    'canonical' => $canonicalPath,
    'robots' => $query !== '' ? 'noindex, follow' : 'index, follow, max-image-preview:large',
    'schema' => $schema,
]);
$activePage = 'blog';
?>
<body>
<?php require __DIR__ . '/partials/public-header.php'; ?>
<main>
  <?php start_secure_session(); if (!empty($_SESSION['flash'])): $flash = (string) $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <p class="flash-message<?= ($_GET['newsletter'] ?? '') === 'erro' ? ' error' : '' ?>" role="status"><?= e($flash) ?></p>
  <?php endif; ?>
  <section class="blog-hero">
    <div class="blog-shell">
      <p class="eyebrow">CONHECIMENTO QUE PROTEGE</p>
      <h1>Blog Guitton</h1>
      <p>Orientações práticas para decisões imobiliárias mais claras, seguras e conscientes.</p>
      <form class="blog-search" action="/blog.php" method="get" role="search">
        <label class="sr-only" for="blog-query">Pesquisar publicações</label>
        <input id="blog-query" name="q" type="search" value="<?= e($query) ?>" placeholder="Pesquise por título, resumo ou conteúdo" maxlength="100">
        <button type="submit">Pesquisar</button>
      </form>
    </div>
  </section>

  <section class="blog-shell blog-listing" aria-label="Publicações do blog">
    <?php if ($query !== ''): ?>
      <div class="search-summary"><p><?= count($posts) ?> resultado(s) para “<?= e($query) ?>”.</p><a href="/blog.php">Limpar busca</a></div>
    <?php endif; ?>

    <?php if ($featured): ?>
      <article class="featured-post">
        <a class="featured-image" href="/post.php?slug=<?= rawurlencode((string) $featured['slug']) ?>">
          <img src="/<?= e(ltrim((string) ($featured['image'] ?? 'assets/guitton-social.jpg'), '/')) ?>" alt="<?= e((string) ($featured['image_alt'] ?? $featured['title'])) ?>">
        </a>
        <div class="featured-copy">
          <p class="eyebrow">EM DESTAQUE</p>
          <h2><a href="/post.php?slug=<?= rawurlencode((string) $featured['slug']) ?>"><?= e((string) $featured['title']) ?></a></h2>
          <p><?= e((string) $featured['summary']) ?></p>
          <div class="post-meta"><time datetime="<?= e((string) $featured['published_at']) ?>"><?= e(format_date_br($featured['published_at'])) ?></time><span><?= reading_time((string) $featured['content']) ?> min de leitura</span></div>
          <a class="outline-button" href="/post.php?slug=<?= rawurlencode((string) $featured['slug']) ?>">Ler publicação</a>
        </div>
      </article>
    <?php endif; ?>

    <?php if ($visible || $query !== '' || !$featured): ?>
      <div class="section-heading blog-heading"><p class="eyebrow">ATUALIZAÇÕES</p><h2 id="latest-title"><?= $query !== '' ? 'RESULTADOS' : 'PUBLICAÇÕES RECENTES' ?></h2></div>
    <?php endif; ?>
    <?php if (!$visible && ($query !== '' || !$featured)): ?>
      <div class="blog-empty">
        <span aria-hidden="true">✦</span>
        <h2><?= $query !== '' ? 'Nenhuma publicação encontrada' : 'Novas publicações em breve' ?></h2>
        <p><?= $query !== '' ? 'Tente pesquisar usando outras palavras.' : 'Estamos preparando conteúdos para ajudar você a cuidar melhor do seu patrimônio.' ?></p>
      </div>
    <?php elseif ($visible): ?>
      <div class="post-grid">
        <?php foreach ($visible as $post): ?>
          <article class="post-card">
            <a class="post-card-image" href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>"><img src="/<?= e(ltrim((string) ($post['image'] ?? 'assets/guitton-social.jpg'), '/')) ?>" alt="<?= e((string) ($post['image_alt'] ?? $post['title'])) ?>" loading="lazy"></a>
            <div class="post-card-body">
              <div class="post-meta"><time datetime="<?= e((string) $post['published_at']) ?>"><?= e(format_date_br($post['published_at'])) ?></time><span><?= reading_time((string) $post['content']) ?> min</span></div>
              <h3><a href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>"><?= e((string) $post['title']) ?></a></h3>
              <p><?= e((string) $post['summary']) ?></p>
              <a class="post-link" href="/post.php?slug=<?= rawurlencode((string) $post['slug']) ?>">Abrir notícia <span aria-hidden="true">→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
      <nav class="pagination" aria-label="Paginação do blog">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?><a<?= $i === $page ? ' class="current" aria-current="page"' : '' ?> href="?<?= http_build_query(array_filter(['q' => $query, 'page' => $i], fn($v) => $v !== '')) ?>"><?= $i ?></a><?php endfor; ?>
      </nav>
    <?php endif; ?>
  </section>

  <section class="newsletter-section" aria-labelledby="newsletter-title">
    <div class="newsletter-inner">
      <div><p class="eyebrow">FIQUE POR DENTRO</p><h2 id="newsletter-title">Receba novas publicações</h2><p>Conteúdo útil, enviado apenas quando houver uma nova notícia.</p></div>
      <form action="/newsletter.php" method="post" class="newsletter-form">
        <div class="hp-field" aria-hidden="true"><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label for="newsletter-email">Seu melhor e-mail</label>
        <div class="newsletter-row"><input id="newsletter-email" name="email" type="email" required maxlength="254" placeholder="voce@exemplo.com"><button type="submit">Quero receber</button></div>
        <label class="consent"><input type="checkbox" name="consent" value="1" required> Concordo em receber avisos de novas publicações e sei que posso cancelar quando quiser.</label>
      </form>
    </div>
  </section>
</main>
<?php require __DIR__ . '/partials/public-footer.php'; ?>
