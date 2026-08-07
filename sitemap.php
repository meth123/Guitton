<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/xml; charset=UTF-8');
$pages = [
    ['/index.html', date('Y-m-d', filemtime(__DIR__ . '/index.html'))],
    ['/pos-locacao.html', date('Y-m-d', filemtime(__DIR__ . '/pos-locacao.html'))],
    ['/consultoria.html', date('Y-m-d', filemtime(__DIR__ . '/consultoria.html'))],
    ['/parceiros.html', date('Y-m-d', filemtime(__DIR__ . '/parceiros.html'))],
    ['/blog.php', date('Y-m-d', filemtime(DATA_PATH . '/posts.json'))],
];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as [$url, $modified]): ?>  <url><loc><?= e(absolute_url($url)) ?></loc><lastmod><?= e($modified) ?></lastmod></url>
<?php endforeach; ?><?php foreach (published_posts() as $post): ?>  <url><loc><?= e(absolute_url('/post.php?slug=' . rawurlencode((string) $post['slug']))) ?></loc><lastmod><?= e(substr((string) $post['updated_at'], 0, 10)) ?></lastmod></url>
<?php endforeach; ?></urlset>
