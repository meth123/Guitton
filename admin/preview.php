<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/admin-posts.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/admin/dashboard.php');
verify_csrf();
$title = trim((string) ($_POST['title'] ?? 'Prévia da publicação'));
$summary = trim((string) ($_POST['summary'] ?? ''));
$content = sanitize_post_html((string) ($_POST['content'] ?? ''));
$image = trim((string) ($_POST['existing_image'] ?? ''));
$imageData = '';
if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    [$valid, , $error] = validate_uploaded_image($_FILES['image']);
    if ($valid) { $imageInfo = getimagesize((string) $_FILES['image']['tmp_name']); $imageData = 'data:' . ($imageInfo['mime'] ?? 'image/jpeg') . ';base64,' . base64_encode((string) file_get_contents((string) $_FILES['image']['tmp_name'])); }
}
send_security_headers(true);
?><!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Prévia | Guitton</title><link rel="stylesheet" href="/styles.css"><link rel="stylesheet" href="/assets/css/blog.css"></head><body><div class="preview-banner">PRÉVIA — esta página não está publicada</div><main class="article-page preview-page"><article><header class="article-header blog-shell"><p class="eyebrow">BLOG GUITTON</p><h1><?= e($title) ?></h1><p class="article-summary"><?= e($summary) ?></p><div class="post-meta article-meta"><span><?= reading_time($content) ?> min de leitura</span></div></header><?php if ($imageData || $image): ?><figure class="article-image blog-shell"><img src="<?= e($imageData ?: '/' . ltrim($image, '/')) ?>" alt="<?= e((string) ($_POST['image_alt'] ?? $title)) ?>"></figure><?php endif; ?><div class="article-layout blog-shell"><div class="article-content"><?= $content ?></div></div><section class="author-box blog-shell"><p class="eyebrow">SOBRE A AUTORA</p><h2><?= e((string) ($_POST['author_name'] ?? 'Cristina Guitton')) ?></h2><p><?= e((string) ($_POST['author_description'] ?? '')) ?></p></section></article></main></body></html>
