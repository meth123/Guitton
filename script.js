const menuToggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.main-nav');
const dropdown = document.querySelector('.nav-dropdown');
const dropdownToggle = document.querySelector('.dropdown-toggle');

let favicon = document.querySelector('link[rel~="icon"]');
if (!favicon) {
  favicon = document.createElement('link');
  favicon.rel = 'icon';
  document.head.appendChild(favicon);
}
favicon.type = 'image/png';
favicon.href = 'assets/images/favicon.png?v=2';
document.title = document.title.replace('Cristina Guitton', 'Guitton');

if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(open));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.site-header')) {
      nav.classList.remove('open');
      menuToggle.setAttribute('aria-expanded', 'false');
    }
  });
}

if (dropdown && dropdownToggle) {
  dropdownToggle.setAttribute('aria-expanded', 'false');
  dropdownToggle.addEventListener('click', (event) => {
    event.stopPropagation();
    const open = dropdown.classList.toggle('dropdown-open');
    dropdownToggle.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', () => {
    dropdown.classList.remove('dropdown-open');
    dropdownToggle.setAttribute('aria-expanded', 'false');
  });
}

const modal = document.createElement('div');
modal.className = 'schedule-modal';
modal.setAttribute('aria-hidden', 'true');
modal.innerHTML = `
  <div class="schedule-dialog" role="dialog" aria-modal="true" aria-label="Agendar consulta">
    <button class="schedule-close" type="button" aria-label="Fechar agendamento">×</button>
    <iframe title="Agendar consulta com Cristina Guitton" loading="lazy" src="https://calendly.com/agendaguitton/meet?hide_gdpr_banner=1"></iframe>
  </div>`;
document.body.appendChild(modal);

const closeSchedule = () => {
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('modal-open');
};

document.querySelectorAll('a').forEach((link) => {
  if (link.textContent.trim().toLowerCase().includes('agendar consulta')) {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      modal.classList.add('open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      modal.querySelector('.schedule-close').focus();
    });
  }
});

modal.querySelector('.schedule-close').addEventListener('click', closeSchedule);
modal.addEventListener('click', (event) => { if (event.target === modal) closeSchedule(); });
document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeSchedule(); });
