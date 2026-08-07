document.querySelectorAll('.copy-link').forEach((button) => {
  button.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(button.dataset.url || window.location.href);
      button.textContent = 'Link copiado!';
    } catch (_) {
      window.prompt('Copie o link:', button.dataset.url || window.location.href);
    }
    window.setTimeout(() => { button.textContent = 'Copiar link'; }, 2200);
  });
});
