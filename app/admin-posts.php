<?php
declare(strict_types=1);

function validate_uploaded_image(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [true, null, null];
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return [false, null, 'Falha no envio da imagem.'];
    if ((int) ($file['size'] ?? 0) < 1 || (int) $file['size'] > MAX_UPLOAD_BYTES) return [false, null, 'A imagem deve ter no máximo 5 MB.'];
    $temporary = (string) ($file['tmp_name'] ?? '');
    $info = @getimagesize($temporary);
    $mime = class_exists('finfo') ? (new finfo(FILEINFO_MIME_TYPE))->file($temporary) : ($info['mime'] ?? '');
    $types = ['image/jpeg' => 'jpg', 'image/webp' => 'webp', 'image/png' => 'png'];
    if (!$info || !isset($types[$mime]) || !in_array((int) $info[2], [IMAGETYPE_JPEG, IMAGETYPE_WEBP, IMAGETYPE_PNG], true)) return [false, null, 'Use somente imagens JPEG, WebP ou PNG válidas.'];
    if (($info[0] ?? 0) < 300 || ($info[1] ?? 0) < 169) return [false, null, 'A imagem é pequena demais. Use pelo menos 300 × 169 pixels.'];
    return [true, $types[$mime], null];
}

function store_uploaded_image(array $file): array
{
    [$valid, $extension, $error] = validate_uploaded_image($file);
    if (!$valid || $extension === null) return [$valid, null, $error];
    $name = bin2hex(random_bytes(20)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], UPLOAD_PATH . '/' . $name)) return [false, null, 'Não foi possível armazenar a imagem.'];
    @chmod(UPLOAD_PATH . '/' . $name, 0644);
    return [true, 'assets/uploads/' . $name, null];
}

function save_post_from_request(?array $existing = null): array
{
    $title = trim((string) ($_POST['title'] ?? ''));
    $summary = trim((string) ($_POST['summary'] ?? ''));
    $content = sanitize_post_html((string) ($_POST['content'] ?? ''));
    $authorName = trim((string) ($_POST['author_name'] ?? 'Cristina Guitton'));
    $authorDescription = trim((string) ($_POST['author_description'] ?? 'Especialista em administração e consultoria imobiliária.'));
    $imageAlt = trim((string) ($_POST['image_alt'] ?? ''));
    $status = ($_POST['action'] ?? '') === 'publish' ? 'published' : 'draft';
    $errors = [];
    if (mb_strlen($title) < 5 || mb_strlen($title) > 150) $errors[] = 'O título deve ter entre 5 e 150 caracteres.';
    if (mb_strlen($summary) < 20 || mb_strlen($summary) > 320) $errors[] = 'O resumo deve ter entre 20 e 320 caracteres.';
    if (mb_strlen(plain_text_from_html($content)) < 30) $errors[] = 'O conteúdo precisa ter pelo menos 30 caracteres.';
    if ($authorName === '' || mb_strlen($authorName) > 100) $errors[] = 'Informe um nome de autor válido.';
    if ($authorDescription === '' || mb_strlen($authorDescription) > 350) $errors[] = 'Informe uma descrição breve do autor.';
    if (mb_strlen($imageAlt) > 180) $errors[] = 'O texto alternativo deve ter no máximo 180 caracteres.';
    $image = (string) ($existing['image'] ?? '');
    if (!empty($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        [$valid, $stored, $uploadError] = store_uploaded_image($_FILES['image']);
        if (!$valid) $errors[] = (string) $uploadError; else $image = (string) $stored;
    }
    if ($status === 'published' && $image === '') $errors[] = 'Envie uma imagem antes de publicar.';
    if ($errors) return [false, null, $errors];
    $now = date('c');
    $wasPublished = ($existing['status'] ?? '') === 'published';
    $post = [
        'id' => $existing['id'] ?? bin2hex(random_bytes(12)),
        'slug' => unique_slug($title, $existing['id'] ?? null),
        'title' => $title, 'summary' => $summary, 'content' => $content,
        'image' => $image, 'image_alt' => $imageAlt,
        'author_name' => $authorName, 'author_description' => $authorDescription,
        'status' => $status,
        'created_at' => $existing['created_at'] ?? $now,
        'updated_at' => $now,
        'published_at' => $status === 'published' ? ($existing['published_at'] ?? $now) : ($existing['published_at'] ?? null),
        'newsletter_notified_at' => $existing['newsletter_notified_at'] ?? null,
    ];
    $posts = all_posts();
    $replaced = false;
    foreach ($posts as $index => $item) {
        if (($item['id'] ?? '') === $post['id']) { $posts[$index] = $post; $replaced = true; break; }
    }
    if (!$replaced) $posts[] = $post;
    if (!write_json_file(DATA_PATH . '/posts.json', array_values($posts))) return [false, null, ['Não foi possível salvar a publicação. Verifique as permissões da pasta data.']];
    if ($status === 'published' && !$wasPublished && empty($post['newsletter_notified_at'])) {
        $result = notify_subscribers($post);
        $post['newsletter_notified_at'] = $now;
        $post['newsletter_result'] = $result;
        foreach ($posts as $index => $item) if (($item['id'] ?? '') === $post['id']) $posts[$index] = $post;
        write_json_file(DATA_PATH . '/posts.json', array_values($posts));
    }
    return [true, $post, []];
}
