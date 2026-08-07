<?php
declare(strict_types=1);

function render_public_head(array $meta): void
{
    $title = (string) ($meta['title'] ?? 'Guitton');
    $description = (string) ($meta['description'] ?? 'Administração de imóveis e consultoria imobiliária.');
    $canonical = absolute_url((string) ($meta['canonical'] ?? '/'));
    $robots = (string) ($meta['robots'] ?? 'index, follow, max-image-preview:large');
    $type = (string) ($meta['type'] ?? 'website');
    $image = (string) ($meta['image'] ?? DEFAULT_SOCIAL_IMAGE);
    $imageAlt = (string) ($meta['image_alt'] ?? 'Guitton — administração e consultoria imobiliária');
    $imagePath = (string) parse_url($image, PHP_URL_PATH);
    $imageType = match (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION))) { 'webp' => 'image/webp', 'png' => 'image/png', default => 'image/jpeg' };
    $imageWidth = 1200;
    $imageHeight = 630;
    $localImage = ROOT_PATH . '/' . ltrim($imagePath, '/');
    if (is_file($localImage) && ($dimensions = @getimagesize($localImage))) {
        $imageWidth = (int) $dimensions[0];
        $imageHeight = (int) $dimensions[1];
    }
    $schema = $meta['schema'] ?? [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $title,
        'description' => $description,
        'url' => $canonical,
        'isPartOf' => ['@type' => 'WebSite', 'name' => SITE_NAME, 'url' => SITE_URL],
    ];
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <meta name="robots" content="<?= e($robots) ?>">
  <link rel="canonical" href="<?= e($canonical) ?>">
  <link rel="icon" href="/assets/images/favicon.png" type="image/png">
  <meta property="og:locale" content="pt_BR">
  <meta property="og:type" content="<?= e($type) ?>">
  <meta property="og:site_name" content="Guitton">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= e($image) ?>">
  <meta property="og:image:secure_url" content="<?= e($image) ?>">
  <meta property="og:image:type" content="<?= e($imageType) ?>">
  <meta property="og:image:width" content="<?= $imageWidth ?>">
  <meta property="og:image:height" content="<?= $imageHeight ?>">
  <meta property="og:image:alt" content="<?= e($imageAlt) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($title) ?>">
  <meta name="twitter:description" content="<?= e($description) ?>">
  <meta name="twitter:image" content="<?= e($image) ?>">
  <meta name="twitter:image:alt" content="<?= e($imageAlt) ?>">
  <link rel="stylesheet" href="/styles.css">
  <link rel="stylesheet" href="/assets/css/blog.css">
  <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</head>
<?php
}
