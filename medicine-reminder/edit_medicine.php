<?php
require_once __DIR__ . '/config/db.php';
require_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    echo "Invalid medicine ID.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM medicines WHERE id=? AND user_id=?");
$stmt->execute([$id, current_user_id()]);
$medicine = $stmt->fetch();

if (!$medicine) {
    echo "Medicine not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $dose_mg = trim($_POST['dose_mg'] ?? '');
    $color = trim($_POST['color'] ?? '');

    $pdo->prepare("UPDATE medicines SET name=?, dose_mg=?, color=? WHERE id=? AND user_id=?")
        ->execute([$name, $dose_mg, $color, $id, current_user_id()]);

    header('Location: /medicine-reminder/medicines.php');
    exit;
}

include __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Edit Medicine</h2>
  <form method="post">
    <div>
      <label>Name</label>
      <input class="input" name="name" required value="<?= h($medicine['name']) ?>">
    </div>
    <div>
      <label>Dose (mg)</label>
      <input class="input" name="dose_mg" type="number" value="<?= h($medicine['dose_mg']) ?>">
    </div>
    <div>
      <label>Color</label>
      <input class="input" name="color" value="<?= h($medicine['color']) ?>">
    </div>
    <button class="btn btn-primary" type="submit">Save Changes</button>
    <a class="btn btn-ghost" href="/medicine-reminder/medicines.php">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>