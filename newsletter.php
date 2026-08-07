<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
start_secure_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/blog.php');
verify_csrf();
if (honeypot_filled()) redirect('/blog.php?newsletter=ok#newsletter-title');
[$ok, $message] = subscribe_email((string) ($_POST['email'] ?? ''), isset($_POST['consent']));
$_SESSION['flash'] = $message;
redirect('/blog.php?newsletter=' . ($ok ? 'ok' : 'erro') . '#newsletter-title');
