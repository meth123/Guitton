<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require dirname(__DIR__) . '/app/admin-posts.php';
require_admin();

$id = trim((string) ($_GET['id'] ?? $_POST['id'] ?? ''));
$post = null;
foreach (all_posts() as $item) {
    if (($item['id'] ?? '') === $id) { $post = $item; break; }
}
if ($id !== '' && !$post) { http_response_code(404); exit('Publicação não encontrada.'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$ok, $saved, $errors] = save_post_from_request($post);
    if ($ok) {
        start_secure_session();
        $_SESSION['admin_flash'] = ($_POST['action'] ?? '') === 'publish' ? 'Publicação salva e publicada.' : 'Rascunho salvo.';
        redirect('/admin/dashboard.php');
    }
    $post = array_merge($post ?? [], $_POST);
}

$post = array_merge([
    'id' => '', 'title' => '', 'summary' => '', 'content' => '<p></p>', 'image' => '', 'image_alt' => '',
    'author_name' => 'Cristina Guitton',
    'author_description' => 'Especialista em administração e consultoria imobiliária.',
    'status' => 'draft',
], $post ?? []);
$isPublished = ($post['status'] ?? '') === 'published';
$adminTitle = $id ? 'Editar publicação' : 'Nova publicação';
require dirname(__DIR__) . '/partials/admin-header.php';
?>
<a class="admin-back-link" href="/admin/dashboard.php"><span aria-hidden="true">←</span> Voltar para publicações</a>
<div class="admin-page-head editor-heading">
  <div>
    <p class="admin-eyebrow"><?= $id ? 'EDIÇÃO DE CONTEÚDO' : 'NOVO CONTEÚDO' ?></p>
    <h1><?= $id ? 'Editar publicação' : 'Nova publicação' ?></h1>
    <p>Escreva com calma, organize a leitura e confira tudo na prévia.</p>
  </div>
</div>

<?php if ($errors): ?>
  <div class="admin-alert error" role="alert"><strong>Revise os pontos abaixo:</strong><ul><?php foreach ($errors as $error): ?><li><?= e((string) $error) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="post-editor-form" id="post-form">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <input type="hidden" name="id" value="<?= e((string) $post['id']) ?>">
  <input type="hidden" name="existing_image" value="<?= e((string) $post['image']) ?>">
  <input type="hidden" name="content" id="content-input" value="<?= e((string) $post['content']) ?>">

  <div class="editor-grid refined-editor-grid">
    <section class="editor-main admin-panel writing-panel">
      <div class="field-group">
        <label for="post-title">Título</label>
        <input id="post-title" name="title" required maxlength="150" value="<?= e((string) $post['title']) ?>" placeholder="Um título claro, útil e interessante">
        <small class="field-hint"><span>É a primeira frase que o leitor verá.</span><b><span data-title-count>0</span>/150</b></small>
      </div>

      <div class="field-group">
        <label for="post-summary">Resumo</label>
        <textarea id="post-summary" name="summary" required maxlength="320" rows="4" placeholder="Apresente em poucas linhas o que a pessoa encontrará na publicação"><?= e((string) $post['summary']) ?></textarea>
        <small class="field-hint"><span>Este texto aparece nos cards e nas redes sociais.</span><b><span data-summary-count>0</span>/320</b></small>
      </div>

      <fieldset class="rich-editor">
        <legend>Texto da publicação</legend>
        <div class="editor-label-row"><div><strong>Texto da publicação</strong><span>Selecione um trecho e use os botões para formatá-lo.</span></div><small><span data-content-count>0</span> caracteres</small></div>
        <div class="editor-toolbar" role="toolbar" aria-label="Formatação do texto">
          <button type="button" data-command="formatBlock" data-value="p" title="Parágrafo">P</button>
          <button type="button" data-command="formatBlock" data-value="h2" title="Título de seção">H2</button>
          <button type="button" data-command="formatBlock" data-value="h3" title="Subtítulo">H3</button>
          <span class="toolbar-divider"></span>
          <button type="button" data-command="bold" title="Negrito"><strong>N</strong></button>
          <button type="button" data-command="italic" title="Itálico"><em>I</em></button>
          <button type="button" data-command="formatBlock" data-value="blockquote" title="Citação">“ ”</button>
          <span class="toolbar-divider"></span>
          <button type="button" data-command="insertUnorderedList" title="Lista com marcadores">• Lista</button>
          <button type="button" data-command="insertOrderedList" title="Lista numerada">1. Lista</button>
          <button type="button" data-link title="Inserir link">Link ↗</button>
        </div>
        <div id="content-editor" class="content-editor" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Comece a escrever aqui. Use parágrafos curtos para deixar a leitura mais agradável."><?= $post['content'] ?></div>
      </fieldset>
    </section>

    <aside class="editor-side">
      <section class="admin-panel ai-writing-panel" data-ai-writing>
        <div class="ai-panel-heading">
          <span class="ai-spark" aria-hidden="true">✦</span>
          <h2>Assistente de escrita</h2>
        </div>
        <p>Escolha uma ação. A IA usa título e resumo; nas duas últimas opções, também lê o texto atual.</p>
        <label for="ai-writing-action">O que deseja fazer?</label>
        <select id="ai-writing-action" data-ai-action>
          <option value="create">Criar texto do zero</option>
          <option value="improve">Melhorar o texto atual</option>
          <option value="continue">Continuar escrevendo</option>
        </select>
        <button class="admin-button full-button ai-writing-button" type="button" data-ai-run>
          <span data-ai-button-text>Executar com IA</span>
          <span class="ai-loading-mark" aria-hidden="true"></span>
        </button>
        <p class="ai-feedback" data-ai-feedback aria-live="polite">Uso econômico ativado. Revise antes de publicar.</p>
      </section>

      <section class="admin-panel publication-panel">
        <div class="panel-title-row"><h2>Publicação</h2><span class="status-dot <?= $isPublished ? 'live' : '' ?>"></span></div>
        <div class="current-status"><span>Status atual</span><strong><?= $isPublished ? 'Publicado' : 'Rascunho' ?></strong></div>
        <button class="admin-button secondary full-button" type="submit" formaction="/admin/preview.php" formtarget="_blank" name="action" value="preview">Pré-visualizar <span aria-hidden="true">↗</span></button>
        <button class="admin-button subtle full-button" type="submit" name="action" value="draft">Salvar como rascunho</button>
        <button class="admin-button full-button" type="submit" name="action" value="publish"><?= $isPublished ? 'Atualizar publicação' : 'Publicar agora' ?></button>
        <p class="publication-note">A prévia não publica nem altera o conteúdo salvo.</p>
      </section>

      <section class="admin-panel image-panel">
        <div class="panel-title-row"><h2>Imagem de destaque</h2><span class="panel-step">01</span></div>
        <div class="image-guidance"><strong>Formato recomendado</strong><span>1200 × 675 px (16:9), WebP ou JPEG.</span><span>Boa iluminação, nitidez e até 500 KB.</span></div>
        <label class="upload-dropzone" for="post-image">
          <img data-image-preview src="<?= $post['image'] ? '/' . e(ltrim((string) $post['image'], '/')) : '' ?>" alt="<?= $post['image'] ? 'Prévia da imagem atual' : '' ?>"<?= $post['image'] ? '' : ' hidden' ?>>
          <span class="upload-icon" aria-hidden="true">＋</span>
          <strong data-upload-label><?= $post['image'] ? 'Trocar imagem' : 'Escolher imagem' ?></strong>
          <small>JPEG, WebP ou PNG — máximo de 5 MB</small>
        </label>
        <input class="visually-hidden-file" id="post-image" type="file" name="image" accept="image/jpeg,image/webp,image/png">
        <label for="image-alt">Descrição da imagem <span>(opcional)</span></label>
        <input id="image-alt" name="image_alt" maxlength="180" value="<?= e((string) $post['image_alt']) ?>" placeholder="Ex.: mãos segurando as chaves de um imóvel">
        <small>Ajuda na acessibilidade e no SEO.</small>
      </section>

      <section class="admin-panel author-panel">
        <div class="panel-title-row"><h2>Autoria</h2><span class="panel-step">02</span></div>
        <label for="author-name">Nome</label>
        <input id="author-name" name="author_name" maxlength="100" required value="<?= e((string) $post['author_name']) ?>">
        <label for="author-description">Apresentação</label>
        <textarea id="author-description" name="author_description" maxlength="350" rows="4" required><?= e((string) $post['author_description']) ?></textarea>
      </section>
    </aside>
  </div>
</form>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
