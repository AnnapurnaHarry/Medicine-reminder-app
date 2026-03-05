<?php
require_once __DIR__ . '/config/db.php';
require_auth();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$userId = current_user_id();

if ($id) {
}

$stmt = $pdo->prepare("SELECT m.*, (SELECT COUNT(*) FROM schedules s WHERE s.medicine_id=m.id) AS schedules_count
                       FROM medicines m WHERE m.user_id=? ORDER BY m.created_at DESC");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

// Collect today's schedules that are NOT taken and have a time set
$dueSchedules = [];
$scheduleTimeStmt = $pdo->prepare("SELECT s.id, s.medicine_id, s.time FROM schedules s WHERE s.user_id=?");
$scheduleTimeStmt->execute([$userId]);
$schedulesWithTime = $scheduleTimeStmt->fetchAll();

$today = date('Y-m-d');
$logStmt = $pdo->prepare("SELECT schedule_id FROM dose_logs WHERE user_id=? AND date=? AND taken=1");
$logStmt->execute([$userId, $today]);
$takenSchedules = array_column($logStmt->fetchAll(), 'schedule_id');

foreach ($schedulesWithTime as $s) {
    if (!in_array($s['id'], $takenSchedules)) {
        foreach ($rows as $m) {
            if ($m['id'] == $s['medicine_id']) {
                $dueSchedules[] = [
                    'name' => $m['name'],
                    'dose_mg' => $m['dose_mg'],
                    'color' => $m['color'],
                    'time' => $s['time'],
                ];
                break;
            }
        }
    }
}

include __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2 class="section-title">Your Medicines</h2>
  <?php if (!$rows): ?>
    <p class="helper">No medicines yet.</p>
  <?php else: ?>
    <table class="table">
      <thead><tr><th>Name</th><th>Dose</th><th>Color</th><th>Schedules</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= h($r['name']) ?></td>
            <td><?= h($r['dose_mg']) ?></td>
            <td><?= h($r['color']) ?></td>
            <td><?= (int)$r['schedules_count'] ?></td>
            <td>
              <a class="btn btn-primary" href="/medicine-reminder/edit_medicine.php?id=<?= (int)$r['id'] ?>">Edit</a>
              <a class="btn btn-ghost" href="/medicine-reminder/medicines.php?delete=<?= (int)$r['id'] ?>" onclick="return confirm('Delete medicine and its schedules?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php if (!empty($dueSchedules)): ?>
  <div class="card" style="background:#fffbe6;border-left:6px solid #ff9800;margin-bottom:1.5rem;">
    <h3 style="color:#ff9800;margin-top:0;">⏰ Medicine Reminder</h3>
    <ul style="margin:0;padding-left:1.2em;">
      <?php foreach ($dueSchedules as $med): ?>
        <li>
          <strong><?= h($med['name']) ?></strong>
          (<?= h($med['dose_mg']) ?> mg, <?= h($med['color']) ?>)
          - Scheduled at <strong><?= h($med['time']) ?></strong>
        </li>
      <?php endforeach; ?>
    </ul>
    <span style="font-size:0.95em;color:#555;">Don't forget to take your medicine!</span>
  </div>
<?php endif; ?>
<script>
const dueSchedules = <?= json_encode($dueSchedules) ?>;

function pad(n) { return n < 10 ? '0' + n : n; }
function getCurrentTime() {
  const d = new Date();
  return pad(d.getHours()) + ':' + pad(d.getMinutes());
}

// Helper: get time one minute from now
function getOneMinuteLater() {
  const d = new Date();
  d.setMinutes(d.getMinutes() + 1);
  return pad(d.getHours()) + ':' + pad(d.getMinutes());
}

setInterval(() => {
  const now = getCurrentTime();
  const oneMinuteLater = getOneMinuteLater();
  console.log("Now:", now, "One minute later:", oneMinuteLater, dueSchedules);
  dueSchedules.forEach(sch => {
    if (sch.time.slice(0,5) === oneMinuteLater) {
      showMedicineModal(`Reminder: In 1 minute, it's time to take <strong>${sch.name}</strong> (${sch.dose_mg} mg, ${sch.color}) at <strong>${sch.time}</strong>.`);
      if (window.Notification && Notification.permission !== "denied") {
        Notification.requestPermission().then(function(permission) {
          if (permission === "granted") {
            new Notification("Medicine Reminder", {
              body: `In 1 minute, take ${sch.name} (${sch.dose_mg} mg, ${sch.color}) at ${sch.time}`,
              icon: "/medicine-reminder/public/pill-icon.png"
            });
          }
        });
      }
    }
  });
}, 60000);

document.addEventListener('DOMContentLoaded', function() {
  const oneMinuteLater = getOneMinuteLater();
  dueSchedules.forEach(sch => {
    if (sch.time.slice(0,5) === oneMinuteLater) {
      showMedicineModal(`Reminder: In 1 minute, it's time to take <strong>${sch.name}</strong> (${sch.dose_mg} mg, ${sch.color}) at <strong>${sch.time}</strong>.`);
      if (window.Notification && Notification.permission !== "denied") {
        Notification.requestPermission().then(function(permission) {
          if (permission === "granted") {
            new Notification("Medicine Reminder", {
              body: `In 1 minute, take ${sch.name} (${sch.dose_mg} mg, ${sch.color}) at ${sch.time}`,
              icon: "/medicine-reminder/public/pill-icon.png"
            });
          }
        });
      }
    }
  });
});
</script>
<!-- Modal HTML (place before footer include) -->
<div id="medicineModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fffbe6;border-left:6px solid #ff9800;padding:2rem 2.5rem;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.15);max-width:350px;text-align:center;">
    <h2 style="color:#ff9800;margin-top:0;">⏰ Medicine Reminder</h2>
    <div id="medicineModalBody" style="font-size:1.1rem;margin-bottom:1.2rem;"></div>
    <button onclick="closeMedicineModal()" style="background:#ff9800;color:#fff;border:none;padding:0.7em 2em;border-radius:8px;font-size:1rem;cursor:pointer;">OK</button>
  </div>
</div>
<script>
function showMedicineModal(message) {
  document.getElementById('medicineModalBody').innerHTML = message;
  document.getElementById('medicineModal').style.display = 'flex';
}
function closeMedicineModal() {
  document.getElementById('medicineModal').style.display = 'none';
}
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>
