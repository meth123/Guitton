<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/admin/dashboard.php');
verify_csrf();
$id = (string) ($_POST['id'] ?? '');
$posts = all_posts();
$remaining = array_values(array_filter($posts, fn(array $post) => ($post['id'] ?? '') !== $id));
if (count($remaining) !== count($posts) && write_json_file(DATA_PATH . '/posts.json', $remaining)) { start_secure_session(); $_SESSION['admin_flash'] = 'Publicação excluída.'; }
redirect('/admin/dashboard.php');
