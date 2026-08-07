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

document.querySelectorAll('.newsletter-form').forEach((form) => {
  const button = form.querySelector('button[type="submit"]');
  const feedback = form.querySelector('[data-newsletter-feedback]');
  const email = form.querySelector('input[name="email"]');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    button.disabled = true;
    button.textContent = 'Enviando…';
    feedback.className = 'newsletter-feedback working';
    feedback.textContent = 'Concluindo seu cadastro…';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      });
      const result = await response.json().catch(() => null);
      if (!response.ok || !result?.ok) throw new Error(result?.message || 'Não foi possível concluir o cadastro.');

      feedback.className = 'newsletter-feedback success';
      feedback.textContent = result.message;
      form.reset();
    } catch (error) {
      feedback.className = 'newsletter-feedback error';
      feedback.textContent = error.message || 'Não foi possível concluir o cadastro. Tente novamente.';
      email?.focus();
    } finally {
      button.disabled = false;
      button.textContent = 'Quero receber';
    }
  });
});
