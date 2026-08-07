<?php
declare(strict_types=1);

function slugify(string $text): string
{
    $text = trim(mb_strtolower($text, 'UTF-8'));
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: $text;
    } else {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = $ascii !== false ? $ascii : $text;
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'publicacao';
}

function sanitize_post_html(string $html): string
{
    $allowed = ['p', 'h2', 'h3', 'strong', 'em', 'ul', 'ol', 'li', 'a', 'blockquote', 'br'];
    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('root');
    if (!$root) return '';
    $walker = function (DOMNode $node) use (&$walker, $allowed): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (!in_array($tag, $allowed, true)) {
                    while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attribute) {
                    if ($tag !== 'a' || $attribute->name !== 'href') $child->removeAttribute($attribute->name);
                }
                if ($tag === 'a') {
                    $href = trim($child->getAttribute('href'));
                    if (!preg_match('#^(https?://|mailto:|tel:|/)#i', $href)) $child->removeAttribute('href');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
                $walker($child);
            }
        }
    };
    $walker($root);
    $output = '';
    foreach ($root->childNodes as $child) $output .= $doc->saveHTML($child);
    return trim($output);
}

function plain_text_from_html(string $html): string
{
    return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
}

function reading_time(string $html): int
{
    $words = preg_split('/\s+/u', plain_text_from_html($html), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return max(1, (int) ceil(count($words) / 200));
}

function all_posts(): array
{
    $posts = read_json_file(DATA_PATH . '/posts.json');
    usort($posts, fn(array $a, array $b) => strcmp((string) ($b['published_at'] ?? $b['updated_at'] ?? ''), (string) ($a['published_at'] ?? $a['updated_at'] ?? '')));
    return $posts;
}

function published_posts(): array
{
    $now = date('c');
    return array_values(array_filter(all_posts(), fn(array $post) => ($post['status'] ?? '') === 'published' && ($post['published_at'] ?? $now) <= $now));
}

function find_post(string $slug, bool $includeDrafts = false): ?array
{
    $posts = $includeDrafts ? all_posts() : published_posts();
    foreach ($posts as $post) if (($post['slug'] ?? '') === $slug) return $post;
    return null;
}

function unique_slug(string $title, ?string $id = null): string
{
    $base = slugify($title);
    $candidate = $base;
    $number = 2;
    $posts = all_posts();
    while (true) {
        $conflict = false;
        foreach ($posts as $post) {
            if (($post['id'] ?? '') !== $id && ($post['slug'] ?? '') === $candidate) {
                $conflict = true;
                break;
            }
        }
        if (!$conflict) return $candidate;
        $candidate = $base . '-' . $number++;
    }
}

function absolute_url(string $path = ''): string
{
    if (preg_match('#^https://#i', $path)) return $path;
    return SITE_URL . '/' . ltrim($path, '/');
}

function post_image_url(array $post): string
{
    $image = (string) ($post['image'] ?? '');
    return $image !== '' && is_file(ROOT_PATH . '/' . ltrim($image, '/')) ? absolute_url($image) : DEFAULT_SOCIAL_IMAGE;
}

function format_date_br(?string $date): string
{
    if (!$date) return '';
    try {
        $formatter = new IntlDateFormatter('pt_BR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'America/Sao_Paulo');
        return $formatter->format(new DateTimeImmutable($date)) ?: '';
    } catch (Throwable) {
        return date('d/m/Y', strtotime($date));
    }
}
