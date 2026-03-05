<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$DB_HOST = '127.0.0.1';
$DB_NAME = 'medicine_reminder';
$DB_USER = 'root';
$DB_PASS = ''; // WAMP default is empty

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

// small helpers
function is_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function current_user_id(): ?int { return $_SESSION['user_id'] ?? null; }
function require_auth(): void {
  if (!current_user_id()) {
    header('Location: /medicine-reminder/login.php');
    exit;
  }
}

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
