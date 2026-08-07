<?php $activePage = $activePage ?? ''; ?>
<header class="site-header">
  <a class="brand" href="/index.html" aria-label="Guitton — início"><span></span></a>
  <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false"><span></span><span></span><span></span></button>
  <nav class="main-nav" aria-label="Navegação principal">
    <a<?= $activePage === 'home' ? ' class="active"' : '' ?> href="/index.html">Início</a>
    <div class="nav-dropdown">
      <button class="dropdown-toggle" type="button">Serviços <span>⌄</span></button>
      <div class="dropdown-menu">
        <a href="/pos-locacao.html">Pós-locação</a>
        <a href="/consultoria.html">Consultoria</a>
        <a href="/parceiros.html">Parceiros</a>
      </div>
    </div>
    <a href="/index.html#contato">Contato</a>
    <a<?= $activePage === 'blog' ? ' class="active"' : '' ?> href="/blog.php">Blog</a>
  </nav>
  <a class="header-whatsapp" href="https://wa.me/551340404663" target="_blank" rel="noopener noreferrer" aria-label="Fale conosco pelo WhatsApp"></a>
</header>
