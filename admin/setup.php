<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
start_secure_session();
if (admin_exists()) { http_response_code(404); require dirname(__DIR__) . '/404.php'; exit; }
$error = '';
$setupConfigured = env_value('ADMIN_SETUP_TOKEN') !== '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (honeypot_filled()) redirect('/admin/setup.php');
    $providedToken = (string) ($_POST['setup_token'] ?? '');
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    if (!$setupConfigured || !hash_equals(env_value('ADMIN_SETUP_TOKEN'), $providedToken)) $error = 'Token de configuração inválido.';
    elseif (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $username)) $error = 'O usuário deve ter de 3 a 80 caracteres e usar apenas letras, números, ponto, hífen ou sublinhado.';
    elseif (!password_is_valid($password)) $error = 'A senha precisa ter pelo menos 6 caracteres, uma letra, um número e um símbolo.';
    elseif (!hash_equals($password, $confirmation)) $error = 'As senhas não coincidem.';
    else {
        $admin = [['id' => bin2hex(random_bytes(16)), 'username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'created_at' => date('c')]];
        if (write_json_file(DATA_PATH . '/admins.json', $admin)) redirect('/admin/');
        $error = 'Não foi possível criar o administrador. Verifique as permissões da pasta data.';
    }
}
$adminTitle = 'Configuração inicial'; require dirname(__DIR__) . '/partials/admin-header.php';
?>
<section class="auth-card wide"><p class="admin-eyebrow">PRIMEIRO ACESSO</p><h1>Criar administrador principal</h1><?php if (!$setupConfigured): ?><div class="admin-alert error">Defina <code>ADMIN_SETUP_TOKEN</code> no arquivo local <code>.env</code> antes de continuar.</div><?php endif; ?><?php if ($error): ?><div class="admin-alert error" role="alert"><?= e($error) ?></div><?php endif; ?><p>Esta tela será bloqueada automaticamente depois da criação. Não use credenciais que estejam no GitHub.</p><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="hp-field"><label>Website<input name="website" tabindex="-1"></label></div><label>Token de configuração<input type="password" name="setup_token" required autocomplete="off"></label><label>Usuário principal<input name="username" required autocomplete="username"></label><label>Senha<input type="password" name="password" required autocomplete="new-password"><small>Mínimo de 6 caracteres, com letra, número e símbolo.</small></label><label>Confirmar senha<input type="password" name="password_confirmation" required autocomplete="new-password"></label><button class="admin-button" type="submit"<?= !$setupConfigured ? ' disabled' : '' ?>>Criar administrador</button></form></section>
<?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
