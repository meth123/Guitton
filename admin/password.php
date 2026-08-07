<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $items = admins(); $admin = $items[0] ?? [];
    $current = (string) ($_POST['current_password'] ?? ''); $new = (string) ($_POST['new_password'] ?? ''); $confirmation = (string) ($_POST['confirmation'] ?? '');
    if (!password_verify($current, (string) ($admin['password_hash'] ?? ''))) $error = 'A senha atual está incorreta.';
    elseif (!password_is_valid($new)) $error = 'A nova senha precisa ter 6 caracteres ou mais, uma letra, um número e um símbolo.';
    elseif (!hash_equals($new, $confirmation)) $error = 'A confirmação não corresponde à nova senha.';
    else { $items[0]['password_hash'] = password_hash($new, PASSWORD_DEFAULT); $items[0]['password_changed_at'] = date('c'); if (write_json_file(DATA_PATH . '/admins.json', $items)) { session_regenerate_id(true); $success = 'Senha alterada com sucesso.'; } else $error = 'Não foi possível salvar a nova senha.'; }
}
$adminTitle = 'Alterar senha'; require dirname(__DIR__) . '/partials/admin-header.php';
?><section class="auth-card"><p class="admin-eyebrow">SEGURANÇA</p><h1>Alterar senha</h1><?php if ($error): ?><div class="admin-alert error"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="admin-alert success"><?= e($success) ?></div><?php endif; ?><form method="post" class="admin-form"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Senha atual<input type="password" name="current_password" required autocomplete="current-password"></label><label>Nova senha<input type="password" name="new_password" required autocomplete="new-password"><small>Mínimo de 6 caracteres, com letra, número e símbolo.</small></label><label>Confirmar nova senha<input type="password" name="confirmation" required autocomplete="new-password"></label><button class="admin-button" type="submit">Alterar senha</button></form></section><?php require dirname(__DIR__) . '/partials/admin-footer.php'; ?>
