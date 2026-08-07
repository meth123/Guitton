<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/admin-posts.php';
require_admin();
$id = trim((string) ($_GET['id'] ?? $_POST['id'] ?? ''));
$post = null;
foreach (all_posts() as $item) if (($item['id'] ?? '') === $id) { $post = $item; break; }
if ($id !== '' && !$post) { http_response_code(404); exit('Publicação não encontrada.'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$ok, $saved, $errors] = save_post_from_request($post);
    if ($ok) { start_secure_session(); $_SESSION['admin_flash'] = ($_POST['action'] ?? '') === 'publish' ? 'Publicação salva e publicada.' : 'Rascunho salvo.'; redirect('/admin/dashboard.php'); }
    $post = array_merge($post ?? [], $_POST);
}
$post = array_merge(['id'=>'','title'=>'','summary'=>'','content'=>'<p></p>','image'=>'','image_alt'=>'','author_name'=>'Cristina Guitton','author_description'=>'Especialista em administração e consultoria imobiliária.','status'=>'draft'], $post ?? []);
$adminTitle = $id ? 'Editar publicação' : 'Nova publicação'; require dirname(__DIR__) . '/partials/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-eyebrow">BLOG</p><h1><?= $id ? 'Editar publicação' : 'Nova publicação' ?></h1><p>Salve como rascunho ou confira a prévia antes de publicar.</p></div></div>
<?php if ($errors): ?><div class="admin-alert error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e((string) $error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="post-editor-form" id="post-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e((string) $post['id']) ?>"><input type="hidden" name="existing_image" value="<?= e((string) $post['image']) ?>"><input type="hidden" name="content" id="content-input" value="<?= e((string) $post['content']) ?>">
  <div class="editor-grid"><section class="editor-main admin-panel"><label>Título<input name="title" required maxlength="150" value="<?= e((string) $post['title']) ?>" placeholder="Título claro e informativo"></label><label>Resumo<textarea name="summary" required maxlength="320" rows="4" placeholder="Uma apresentação breve da notícia"><?= e((string) $post['summary']) ?></textarea><small><span data-summary-count>0</span>/320 caracteres</small></label><fieldset class="rich-editor"><legend>Conteúdo</legend><div class="editor-toolbar" role="toolbar" aria-label="Formatação"><button type="button" data-command="formatBlock" data-value="p">Parágrafo</button><button type="button" data-command="formatBlock" data-value="h2">Título</button><button type="button" data-command="formatBlock" data-value="h3">Subtítulo</button><button type="button" data-command="bold"><strong>N</strong></button><button type="button" data-command="italic"><em>I</em></button><button type="button" data-command="insertUnorderedList">Lista</button><button type="button" data-command="insertOrderedList">Numerada</button><button type="button" data-command="formatBlock" data-value="blockquote">Citação</button><button type="button" data-link>Link</button></div><div id="content-editor" class="content-editor" contenteditable="true" role="textbox" aria-multiline="true"><?= $post['content'] ?></div></fieldset></section>
  <aside class="editor-side"><section class="admin-panel"><h2>Imagem destacada</h2><?php if ($post['image']): ?><img class="current-image" src="/<?= e((string) $post['image']) ?>" alt="Imagem atual"><?php endif; ?><label>Enviar imagem<input type="file" name="image" accept="image/jpeg,image/webp,image/png"></label><div class="image-guidance"><strong>Recomendação</strong><span>1200 × 675 pixels</span><span>WebP ou JPEG</span><span>Boa iluminação e nitidez</span><span>Preferencialmente abaixo de 500 KB</span></div><label>Texto alternativo (opcional)<input name="image_alt" maxlength="180" value="<?= e((string) $post['image_alt']) ?>"><small>Ajuda pessoas que usam leitores de tela e pode contribuir para o SEO.</small></label></section><section class="admin-panel"><h2>Autoria</h2><label>Nome<input name="author_name" maxlength="100" required value="<?= e((string) $post['author_name']) ?>"></label><label>Descrição<textarea name="author_description" maxlength="350" rows="4" required><?= e((string) $post['author_description']) ?></textarea></label></section></aside></div>
  <div class="editor-actions"><button class="admin-button secondary" type="submit" name="action" value="draft">Salvar rascunho</button><button class="admin-button secondary" type="submit" formaction="/admin/preview.php" formtarget="_blank" name="action" value="preview">Abrir prévia</button><button class="admin-button" type="submit" name="action" value="publish">Publicar</button></div>
</form>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
