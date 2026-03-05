<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/db.php';
$logged_in = is_logged_in();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Medicine Reminder</title>
  <link rel="stylesheet" href="/medicine-reminder/public/styles.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <div class="brand">Medicine Reminder</div>
    <nav class="nav">
      <?php if ($logged_in): ?>
        <a href="/medicine-reminder/index.php">Dashboard</a>
        <a href="/medicine-reminder/medicines.php">Medicines</a>
        <a class="btn btn-primary" href="/medicine-reminder/add_medicine.php">Add Medicine</a>
        <a class="btn btn-ghost" href="/medicine-reminder/logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-primary" href="/medicine-reminder/login.php">Login</a>
        <a class="btn btn-ghost" href="/medicine-reminder/register.php">Register</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container main-content">

</main>
</body>
</html>
