<?php
declare(strict_types=1);
$adminTitle = $adminTitle ?? 'Painel';
send_security_headers(true);
?>
<!doctype html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow,noarchive"><title><?= e($adminTitle) ?> | Admin Guitton</title><link rel="icon" href="/assets/images/favicon.png"><link rel="stylesheet" href="/assets/css/admin.css"></head><body class="admin-body">
<header class="admin-topbar"><a class="admin-brand" href="/admin/dashboard.php"><img src="/assets/images/logo-guitton.png" alt="Guitton"></a><?php if (is_admin()): ?><button class="admin-menu-button" type="button" aria-expanded="false">Menu</button><nav class="admin-nav"><a href="/admin/dashboard.php">Publicações</a><a href="/admin/edit.php">Nova publicação</a><a href="/admin/docs.php">Ajuda</a><a href="/admin/password.php">Senha</a><form class="admin-logout-form" action="/admin/logout.php" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button type="submit">Sair</button></form></nav><?php endif; ?></header><main class="admin-main">
