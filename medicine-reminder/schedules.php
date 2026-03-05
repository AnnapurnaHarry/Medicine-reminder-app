<?php
require __DIR__ . '/config/db.php'; require_auth();
$uid = current_user_id();

$daysLabels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $medicine_id = (int)($_POST['medicine_id'] ?? 0);
  $time = $_POST['time'] ?? '';
  $amount = max(1, (int)($_POST['amount'] ?? 1));
  $frequency = $_POST['frequency'] === 'specific_days' ? 'specific_days' : 'everyday';
  $days = $_POST['days'] ?? $daysLabels;
  $daysSet = $frequency === 'specific_days' ? implode(',', array_values(array_intersect($daysLabels, $days))) : null;

  if ($medicine_id > 0 && $time) {
    $stmt = $pdo->prepare("INSERT INTO schedules (user_id,medicine_id,amount,time,frequency,days_set) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$uid,$medicine_id,$amount,$time,$frequency,$daysSet]);
  }
  header('Location: /medicine-reminder/schedules.php'); exit;
}

if (isset($_GET['delete'])) {
  $sid = (int)$_GET['delete'];
  $pdo->prepare("DELETE FROM schedules WHERE id=? AND user_id=?")->execute([$sid,$uid]);
  header('Location: /medicine-reminder/schedules.php'); exit;
}

$page_title = 'Schedules';
include __DIR__ . '/partials/header.php';
?>
<section class="grid">
  <div class="card">
    <h2 style="margin-top:0" class="section-title">Your schedules</h2>
    <div class="list">
      <?php
      $rows = $pdo->prepare("SELECT s.*, m.name AS med_name, m.color FROM schedules s JOIN medicines m ON m.id=s.medicine_id WHERE s.user_id=? ORDER BY s.time");
      $rows->execute([$uid]);
      foreach ($rows as $r):
        $d = $r['days_set'] ?: 'Everyday';
        $time12 = date('g:i A', strtotime($r['time']));
      ?>
        <div class="item">
          <div class="item-left">
            <span class="pill <?= h($r['color']) ?>"></span>
            <div>
              <div style="font-weight:600"><?= h($r['med_name']) ?> • <?= h($time12) ?></div>
              <div class="helper">Qty <?= (int)$r['amount'] ?> • <?= h($d) ?></div>
            </div>
          </div>
          <a class="btn btn-ghost" href="?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Delete this schedule?')">Delete</a>
        </div>
      <?php endforeach; if ($rows->rowCount()===0): ?>
        <div class="item">No schedules yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <h3 style="margin-top:0" class="section-title">Add schedule</h3>
    <form method="post" class="grid" style="gap:10px">
      <input type="hidden" name="action" value="create">
      <div>
        <label class="label">Medicine</label>
        <select class="input" name="medicine_id" required>
          <?php
          $meds = $pdo->prepare("SELECT id,name FROM medicines WHERE user_id=? ORDER BY name");
          $meds->execute([$uid]);
          foreach ($meds as $m) {
            echo '<option value="'.(int)$m['id'].'">'.h($m['name']).'</option>';
          }
          ?>
        </select>
      </div>
      <div class="grid cols-2">
        <div class="form-row">
          <label class="label">Time</label>
          <input class="input" type="time" name="time" required>
        </div>
        <div class="form-row">
          <label class="label">Quantity</label>
          <input class="input" type="number" min="1" step="1" name="amount" value="1" required>
        </div>
      </div>
      <div class="form-row">
        <label class="label">Frequency</label>
        <select class="input" name="frequency" id="freq2">
          <option value="everyday">Everyday</option>
          <option value="specific_days">Specific days</option>
        </select>
      </div>
      <div class="form-row" id="days2" style="display:none">
        <label class="label">Days</label>
        <div class="inline">
          <?php foreach ($daysLabels as $v): ?>
            <label class="small" style="display:flex;gap:6px;align-items:center">
              <input type="checkbox" name="days[]" value="<?= $v ?>" checked> <?= $v ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Create schedule</button>
    </form>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const s = document.getElementById('freq2'); const d = document.getElementById('days2');
  const t = () => d.style.display = s.value === 'specific_days' ? '' : 'none';
  s.addEventListener('change', t); t();
});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
