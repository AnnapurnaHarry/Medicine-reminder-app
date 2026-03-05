<?php
require_once __DIR__ . '/config/db.php';

// CSRF helpers
function csrf_field(): string {
    $token = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('Invalid CSRF token');
        }
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}

if (is_logged_in()) redirect('/medicine-reminder/index.php');

$error = $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$pass) $error = 'Please fill all required fields.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email address.';
    elseif ($pass !== $confirm) $error = 'Passwords do not match.';
    else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $error = 'Email already registered.';
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')->execute([$name, $email, $hash]);
            // Log the user in automatically
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            // Redirect to add medicine page
            redirect('/medicine-reminder/add_medicine.php');
        }
    }
}

$page_title = 'Create account';

// Check for header file existence before including
$header_path = __DIR__ . '/partials/header.php';
if (!file_exists($header_path)) {
    // fallback: create a minimal header if missing
    echo "<!DOCTYPE html><html><head><title>" . h($page_title) . "</title></head><body>";
} else {
    include $header_path;
}
?>
<section class="auth card">
  <h2 style="margin-top:0">Create your account</h2>
  <?php if ($error): ?><div class="alert"><?= h($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="success"><?= h($success) ?></div><?php endif; ?>
  <form method="post" class="grid" style="gap:12px">
    <?= csrf_field() ?>
    <div>
      <label class="label">Full name</label>
      <input class="input" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
    </div>
    <div>
      <label class="label">Email</label>
      <input class="input" type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-row">
      <div style="flex:1">
        <label class="label">Password</label>
        <input class="input" type="password" name="password" required>
      </div>
      <div style="flex:1">
        <label class="label">Confirm</label>
        <input class="input" type="password" name="confirm" required>
      </div>
    </div>
    <button class="btn" type="submit">Create account</button>
  </form>
  <p class="small">Already have an account? <a href="/medicine-reminder/auth/login.php">Login</a></p>
</section>
<?php
// Check for footer file existence before including
$footer_path = __DIR__ . '/partials/footer.php';
if (file_exists($footer_path)) {
    include $footer_path;
} else {
    echo "</body></html>";
}
?>
