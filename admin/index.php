<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
start_secure_session();
if (is_admin()) redirect('/admin/dashboard.php');
if (!admin_exists()) redirect('/admin/setup.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (honeypot_filled()) redirect('/admin/');
    [$ok, $error] = attempt_admin_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
    if ($ok) redirect('/admin/dashboard.php');
}
$adminTitle = 'Entrar'; require dirname(__DIR__) . '/partials/admin-header.php';
?>
<section class="auth-card"><p class="admin-eyebrow">ÁREA RESTRITA</p><h1>Entrar no painel</h1><p>Use as credenciais do administrador principal.</p><?php if ($error): ?><div class="admin-alert error" role="alert"><?= e($error) ?></div><?php endif; ?><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="hp-field"><label>Website<input name="website" autocomplete="off" tabindex="-1"></label></div><label>Usuário<input name="username" required autocomplete="username" maxlength="80"></label><label>Senha<input type="password" name="password" required autocomplete="current-password"></label><button class="admin-button" type="submit">Entrar</button></form></section>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
