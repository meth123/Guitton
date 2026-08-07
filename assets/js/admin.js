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
