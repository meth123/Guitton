<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/partials/public-head.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$post = find_post($slug);
if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$canonical = '/post.php?slug=' . rawurlencode((string) $post['slug']);
$image = post_image_url($post);
$schema = [
    '@context' => 'https://schema.org', '@type' => 'BlogPosting',
    'headline' => $post['title'], 'description' => $post['summary'], 'image' => [$image],
    'datePublished' => $post['published_at'], 'dateModified' => $post['updated_at'],
    'author' => ['@type' => 'Person', 'name' => $post['author_name']],
    'publisher' => ['@type' => 'Organization', 'name' => 'Guitton', 'logo' => ['@type' => 'ImageObject', 'url' => absolute_url('/assets/images/logo-guitton.png')]],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => absolute_url($canonical)],
    'url' => absolute_url($canonical),
];
render_public_head([
    'title' => $post['title'] . ' | Guitton', 'description' => $post['summary'], 'canonical' => $canonical,
    'type' => 'article', 'image' => $image, 'image_alt' => $post['image_alt'] ?: $post['title'], 'schema' => $schema,
]);
$activePage = 'blog';
$shareUrl = absolute_url($canonical);
?>
<body>
<?php require __DIR__ . '/partials/public-header.php'; ?>
<main class="article-page">
  <article>
    <header class="article-header blog-shell">
      <a class="back-link" href="/blog.php">← Voltar ao blog</a>
      <p class="eyebrow">BLOG GUITTON</p>
      <h1><?= e((string) $post['title']) ?></h1>
      <p class="article-summary"><?= e((string) $post['summary']) ?></p>
      <div class="post-meta article-meta"><time datetime="<?= e((string) $post['published_at']) ?>"><?= e(format_date_br($post['published_at'])) ?></time><span><?= reading_time((string) $post['content']) ?> min de leitura</span></div>
    </header>
    <figure class="article-image blog-shell"><img src="/<?= e(ltrim((string) ($post['image'] ?? 'assets/guitton-social.jpg'), '/')) ?>" alt="<?= e((string) ($post['image_alt'] ?: $post['title'])) ?>"></figure>
    <div class="article-layout blog-shell">
      <div class="article-content"><?= $post['content'] ?></div>
      <aside class="share-box" aria-label="Compartilhar publicação">
        <p>Compartilhe</p>
        <a href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' ' . $shareUrl) ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
        <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode((string) $post['title']) ?>" target="_blank" rel="noopener noreferrer">X / Twitter</a>
        <button type="button" class="copy-link" data-url="<?= e($shareUrl) ?>">Copiar link</button>
      </aside>
    </div>
    <section class="author-box blog-shell" aria-labelledby="author-title"><p class="eyebrow">SOBRE A AUTORA</p><h2 id="author-title"><?= e((string) $post['author_name']) ?></h2><p><?= e((string) $post['author_description']) ?></p></section>
  </article>
  <section class="article-cta"><div><h2>Receba as próximas publicações</h2><p>Cadastre-se gratuitamente para receber um aviso quando houver conteúdo novo.</p><a class="outline-button" href="/blog.php#newsletter-title">Quero receber</a></div></section>
</main>
<?php require __DIR__ . '/partials/public-footer.php'; ?>
