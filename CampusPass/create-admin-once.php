<?php
// Run from CLI only: php create-admin-once.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/config/database.php';
$name = trim($argv[1] ?? 'CampusPass Administrator');
$email = strtolower(trim($argv[2] ?? ''));
$password = getenv('CAMPUSPASS_ADMIN_PASSWORD') ?: '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php create-admin-once.php admin@example.edu
");
    exit(1);
}
if (strlen($password) < 14) {
    fwrite(STDERR, "Set CAMPUSPASS_ADMIN_PASSWORD to a unique password of at least 14 characters.
");
    exit(1);
}
$check = $pdo->prepare('SELECT id FROM admin_users WHERE role = ? LIMIT 1');
$check->execute(['super_admin']);
if ($check->fetch()) { fwrite(STDERR, "A Super Admin already exists. Refusing to create another through this setup script.
"); exit(1); }
try {
    $stmt = $pdo->prepare("INSERT INTO admin_users(name,email,password_hash,role) VALUES(?,?,?,'super_admin')");
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Super Admin created for {$email}. Clear the environment variable and remove this script after setup.
");
} catch (PDOException $e) {
    fwrite(STDERR, "Could not create admin. The email may already be in use.
");
    exit(1);
}
