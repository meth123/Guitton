<?php
declare(strict_types=1);
$adminTitle = $adminTitle ?? 'Painel';
send_security_headers(true);
$currentAdmin = is_admin() ? (admins()[0] ?? []) : [];
?>
<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><title><?= e($adminTitle) ?> | Admin Guitton</title><link rel="icon" href="/assets/images/favicon.png"><link rel="stylesheet" href="/assets/css/admin.css"></head><body class="admin-body">
<header class="admin-topbar">
  <a class="admin-brand" href="/admin/dashboard.php"><img src="/assets/images/logo-guitton.png" alt="Guitton"><span>Painel editorial</span></a>
  <?php if (is_admin()): ?>
    <button class="admin-menu-button" type="button" aria-label="Abrir menu" aria-expanded="false"><i></i><i></i><i></i></button>
    <nav class="admin-nav" aria-label="Navegação administrativa">
      <a href="/admin/docs.php">Como usar</a>
      <a href="/admin/password.php">Minha senha</a>
      <a href="/blog.php" target="_blank" rel="noopener">Ver blog <span aria-hidden="true">↗</span></a>
      <span class="admin-user"><?= e((string) ($currentAdmin['username'] ?? 'Administrador')) ?></span>
      <form class="admin-logout-form" action="/admin/logout.php" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button type="submit">Sair</button></form>
    </nav>
  <?php endif; ?>
</header>
<main class="admin-main">
