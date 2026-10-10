<?php
require_once __DIR__ . '/includes/auth.php';
$exists = (int)$pdo->query("SELECT COUNT(*) FROM admin_users WHERE role='super_admin'")->fetchColumn() > 0;
$error = '';
if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($m = v_name($name)) $error = $m;
    elseif ($m = v_email($email)) $error = $m;
    elseif ($m = v_password($password, 12)) $error = $m;
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        try {
            $pdo->beginTransaction();
            $lock = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE role='super_admin' FOR UPDATE")->fetchColumn();
            if ((int)$lock > 0) throw new RuntimeException('A Super Admin has already been created.');
            $q = $pdo->prepare("INSERT INTO admin_users(name,email,password_hash,role,is_active) VALUES(?,?,?,'super_admin',1)");
            $q->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $pdo->commit();
            header('Location: admin/login.php?setup=success'); exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e instanceof PDOException ? 'Could not create account. This email may already be in use.' : $e->getMessage();
        }
    }
}
$pageTitle = 'Create first administrator';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Initial admin setup · CampusPass</title><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"><link href="assets/css/style.css" rel="stylesheet"></head><body><main class="auth-wrap"><section class="auth-card"><span class="eyebrow">CampusPass setup</span><h1 class="h2 mt-3">Create your Super Admin</h1><?php if($exists): ?><div class="alert alert-success">A Super Admin already exists. For security, this setup page is now locked.</div><a class="btn btn-primary w-100" href="admin/login.php">Go to admin login</a><?php else: ?><p class="muted">Create the first administrator account. This page locks automatically after setup.</p><?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?><form method="post" autocomplete="off" data-validate novalidate><?=csrf_field()?><div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required minlength="2" maxlength="60" data-rule="name" value="<?=e($_POST['name']??'')?>"></div><div class="mb-3"><label class="form-label" for="email">Admin email / user ID</label><input class="form-control" id="email" type="email" name="email" required maxlength="120" value="<?=e($_POST['email']??'')?>"></div><div class="mb-3"><label class="form-label" for="password">Password (12+ characters)</label><input class="form-control" id="password" type="password" name="password" required minlength="12" maxlength="72" data-rule="strongpass" data-strength autocomplete="new-password"></div><div class="mb-4"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" id="confirm_password" type="password" name="confirm_password" required maxlength="72" data-match="#password" autocomplete="new-password"></div><button class="btn btn-primary w-100">Create Super Admin</button></form><?php endif; ?><a class="d-block text-center mt-3" href="index.php">Return to CampusPass</a></section></main><script src="assets/js/validation.js"></script></body></html>
