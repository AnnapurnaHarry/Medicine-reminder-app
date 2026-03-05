<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../config/db.php';

function auth_user_id(): ?int {
  return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function auth_logged_in(): bool {
  return auth_user_id() !== null;
}

function auth_require_login(): void {
  if (!auth_logged_in()) {
    header('Location: /auth/login.php');
    exit;
  }
}

function auth_login(int $userId): void {
  $_SESSION['user_id'] = $userId;
}

function auth_logout_and_redirect(): void {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
  header('Location: /auth/login.php');
  exit;
}

function auth_current_user(PDO $pdo): ?array {
  $id = auth_user_id();
  if (!$id) return null;
  $stmt = $pdo->prepare('SELECT id, full_name, email, created_at FROM users WHERE id = ?');
  $stmt->execute([$id]);
  $user = $stmt->fetch();
  return $user ?: null;
}
