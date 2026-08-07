const adminMenuButton = document.querySelector('.admin-menu-button');
const adminNav = document.querySelector('.admin-nav');
if (adminMenuButton && adminNav) {
  adminMenuButton.addEventListener('click', () => {
    const open = adminNav.classList.toggle('open');
    adminMenuButton.setAttribute('aria-expanded', String(open));
  });
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (!window.confirm(form.dataset.confirm || 'Confirmar esta ação?')) event.preventDefault();
  });
});

const editor = document.querySelector('#content-editor');
const contentInput = document.querySelector('#content-input');
const postForm = document.querySelector('#post-form');
if (editor && contentInput && postForm) {
  const contentCount = document.querySelector('[data-content-count]');
  const sync = () => {
    contentInput.value = editor.innerHTML;
    if (contentCount) contentCount.textContent = String((editor.textContent || '').trim().length);
  };
  document.querySelectorAll('[data-command]').forEach((button) => {
    button.addEventListener('click', () => {
      document.execCommand(button.dataset.command, false, button.dataset.value || null);
      editor.focus();
      sync();
    });
  });
  document.querySelector('[data-link]')?.addEventListener('click', () => {
    const url = window.prompt('Cole o endereço completo do link (https://...):');
    if (url && /^(https?:\/\/|mailto:|tel:|\/)/i.test(url)) document.execCommand('createLink', false, url);
    editor.focus();
    sync();
  });
  editor.addEventListener('input', sync);
  postForm.addEventListener('submit', sync);
  sync();
}

const title = document.querySelector('input[name="title"]');
const titleCount = document.querySelector('[data-title-count]');
if (title && titleCount) {
  const countTitle = () => { titleCount.textContent = String(title.value.length); };
  title.addEventListener('input', countTitle);
  countTitle();
}

const summary = document.querySelector('textarea[name="summary"]');
const summaryCount = document.querySelector('[data-summary-count]');
if (summary && summaryCount) {
  const count = () => { summaryCount.textContent = String(summary.value.length); };
  summary.addEventListener('input', count);
  count();
}

const imageInput = document.querySelector('#post-image');
const imagePreview = document.querySelector('[data-image-preview]');
const uploadLabel = document.querySelector('[data-upload-label]');
if (imageInput && imagePreview) {
  imageInput.addEventListener('change', () => {
    const [file] = imageInput.files || [];
    if (!file) return;
    imagePreview.src = URL.createObjectURL(file);
    imagePreview.hidden = false;
    imagePreview.alt = `Prévia de ${file.name}`;
    if (uploadLabel) uploadLabel.textContent = file.name;
  });
}

const aiPanel = document.querySelector('[data-ai-writing]');
if (aiPanel && editor && contentInput && postForm && title && summary) {
  const actionSelect = aiPanel.querySelector('[data-ai-action]');
  const runButton = aiPanel.querySelector('[data-ai-run]');
  const buttonText = aiPanel.querySelector('[data-ai-button-text]');
  const feedback = aiPanel.querySelector('[data-ai-feedback]');

  const setFeedback = (message, type = '') => {
    feedback.textContent = message;
    feedback.className = `ai-feedback${type ? ` ${type}` : ''}`;
  };

  runButton.addEventListener('click', async () => {
    const action = actionSelect.value;
    const currentText = (editor.textContent || '').trim();

    if (!title.value.trim() || !summary.value.trim()) {
      setFeedback('Preencha o título e o resumo antes de usar a IA.', 'error');
      (!title.value.trim() ? title : summary).focus();
      return;
    }
    if ((action === 'improve' || action === 'continue') && !currentText) {
      setFeedback('Escreva um pouco do texto antes de escolher esta ação.', 'error');
      editor.focus();
      return;
    }
    if (currentText && action !== 'continue' && !window.confirm('A IA substituirá o texto atual no editor. Deseja continuar?')) return;

    contentInput.value = editor.innerHTML;
    const body = new FormData();
    body.append('csrf', postForm.querySelector('input[name="csrf"]').value);
    body.append('ai_action', action);
    body.append('title', title.value);
    body.append('summary', summary.value);
    body.append('content', contentInput.value);

    runButton.disabled = true;
    aiPanel.classList.add('is-loading');
    buttonText.textContent = 'Escrevendo…';
    setFeedback('A IA está preparando o texto. Isso pode levar alguns segundos.', 'working');

    try {
      const response = await fetch('/admin/ai-writing.php', { method: 'POST', body, headers: { Accept: 'application/json' } });
      const result = await response.json().catch(() => null);
      if (!response.ok || !result?.ok) throw new Error(result?.message || 'Não foi possível gerar o texto agora.');

      editor.innerHTML = result.content_html;
      contentInput.value = result.content_html;
      editor.dispatchEvent(new Event('input', { bubbles: true }));
      setFeedback('Texto inserido no editor. Revise antes de publicar.', 'success');
      editor.focus();
    } catch (error) {
      setFeedback(error.message || 'Não foi possível gerar o texto agora.', 'error');
    } finally {
      runButton.disabled = false;
      aiPanel.classList.remove('is-loading');
      buttonText.textContent = 'Executar com IA';
    }
  });
}
