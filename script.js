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

const cookieConsentKey = 'guitton_cookie_consent_v1';
const getCookieConsent = () => {
  try { return window.localStorage.getItem(cookieConsentKey); } catch (_) { return null; }
};
const storeCookieConsent = (value) => {
  try { window.localStorage.setItem(cookieConsentKey, value); } catch (_) { /* preferência válida somente nesta página */ }
};

const cookieBanner = document.createElement('section');
cookieBanner.className = 'cookie-banner';
cookieBanner.setAttribute('role', 'dialog');
cookieBanner.setAttribute('aria-label', 'Preferências de cookies');
cookieBanner.setAttribute('aria-live', 'polite');
cookieBanner.innerHTML = `
  <div class="cookie-banner-copy">
    <strong>Sua privacidade importa</strong>
    <p>Usamos cookies necessários para o site funcionar. Com sua permissão, também carregamos Google Maps e Calendly.</p>
    <a href="/privacidade.php">Ler Política de Privacidade</a>
  </div>
  <div class="cookie-banner-actions">
    <button class="cookie-reject" type="button" data-cookie-reject>Recusar opcionais</button>
    <button class="cookie-accept" type="button" data-cookie-accept>Aceitar opcionais</button>
  </div>`;
document.body.appendChild(cookieBanner);

const loadOptionalServices = () => {
  document.querySelectorAll('[data-cookie-src]').forEach((frame) => {
    if (!frame.getAttribute('src')) frame.setAttribute('src', frame.dataset.cookieSrc);
    frame.hidden = false;
    frame.dataset.cookieLoaded = 'true';
    const placeholder = frame.parentElement?.querySelector('.cookie-embed-placeholder');
    placeholder?.remove();
  });
};

const setCookieConsent = (value) => {
  storeCookieConsent(value);
  cookieBanner.classList.remove('open');
  if (value === 'accepted') loadOptionalServices();
};

const showCookieBanner = () => cookieBanner.classList.add('open');
cookieBanner.querySelector('[data-cookie-accept]').addEventListener('click', () => setCookieConsent('accepted'));
cookieBanner.querySelector('[data-cookie-reject]').addEventListener('click', () => setCookieConsent('rejected'));
document.querySelectorAll('[data-cookie-settings]').forEach((button) => button.addEventListener('click', showCookieBanner));

document.querySelectorAll('iframe[data-cookie-src]').forEach((frame) => {
  if (getCookieConsent() === 'accepted') return;
  frame.hidden = true;
  const placeholder = document.createElement('div');
  placeholder.className = 'cookie-embed-placeholder contact-map';
  placeholder.innerHTML = '<p>O mapa usa um serviço externo.</p><button type="button">Permitir e carregar mapa</button>';
  placeholder.querySelector('button').addEventListener('click', () => setCookieConsent('accepted'));
  frame.insertAdjacentElement('afterend', placeholder);
});

if (getCookieConsent() === 'accepted') loadOptionalServices();
else if (!getCookieConsent()) showCookieBanner();

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
    <iframe title="Agendar consulta com Cristina Guitton" loading="lazy" data-cookie-src="https://calendly.com/agendaguitton/meet?hide_gdpr_banner=1" hidden></iframe>
    <div class="schedule-cookie-gate"><strong>Permitir Calendly?</strong><p>O agendamento usa um serviço externo que pode definir cookies.</p><button type="button">Permitir e abrir agenda</button></div>
  </div>`;
document.body.appendChild(modal);
const scheduleFrame = modal.querySelector('iframe');
const scheduleGate = modal.querySelector('.schedule-cookie-gate');
const loadSchedule = () => {
  if (!scheduleFrame.getAttribute('src')) scheduleFrame.src = scheduleFrame.dataset.cookieSrc;
  scheduleFrame.hidden = false;
  scheduleGate.hidden = true;
};
scheduleGate.querySelector('button').addEventListener('click', () => {
  setCookieConsent('accepted');
  loadSchedule();
});

const closeSchedule = () => {
  modal.classList.remove('open');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('modal-open');
};

document.querySelectorAll('a').forEach((link) => {
  if (link.textContent.trim().toLowerCase().includes('agendar consulta')) {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      if (getCookieConsent() === 'accepted') loadSchedule();
      else { scheduleFrame.hidden = true; scheduleGate.hidden = false; }
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
