<?php
require_once __DIR__ . '/config/db.php'; if (current_user_id()) { header('Location: /medicine-reminder/'); exit; }
$errors = [];
if (is_post()) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT id, password_hash, name FROM users WHERE email = ?'); $stmt->execute([$email]);
  $u = $stmt->fetch();
  if (!$u || !password_verify($password, $u['password_hash'])) $errors[] = 'Invalid credentials.';
  else { $_SESSION['user_id'] = (int)$u['id']; $_SESSION['name'] = $u['name']; header('Location: /medicine-reminder/'); exit; }
}
include __DIR__ . '/partials/header.php'; ?>
<div class="card" style="max-width:480px;margin:0 auto;">
  <h2 class="section-title">Welcome back</h2>
  <?php if (isset($_GET['registered'])): ?><div class="badge" style="color:var(--accent);border-color:var(--accent)">Account created, please log in.</div><?php endif; ?>
  <?php if ($errors): ?><div class="badge" style="border-color:#ef4444;color:#ef4444"><?php echo h(implode(' ', $errors)); ?></div><?php endif; ?>
  <form method="post" class="grid">
    <div class="form-row"><label>Email</label><input class="input" name="email" type="email" required /></div>
    <div class="form-row"><label>Password</label><input class="input" name="password" type="password" required /></div>
    <div class="inline"><button class="btn btn-primary" type="submit">Login</button><a class="btn btn-ghost" href="/medicine-reminder/register.php">Register</a></div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
