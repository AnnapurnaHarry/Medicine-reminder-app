<?php
require_once __DIR__ . '/config/db.php';
require_auth();

$userId = current_user_id();
$user_name = $_SESSION['name'] ?? 'User'; // <-- Add this line
$today = new DateTime('now');
$dayShort = $today->format('D'); // Mon, Tue, ...
$todayDate = $today->format('Y-m-d');

// fetch today's due schedules
$sql = "SELECT s.id AS schedule_id, s.time, s.amount, s.frequency, s.days_set,
               m.name, m.dose_mg, m.color
        FROM schedules s
        JOIN medicines m ON m.id = s.medicine_id
        WHERE s.user_id = :uid
          AND (
             s.frequency = 'everyday'
             OR (s.frequency='specific_days' AND FIND_IN_SET(:day, REPLACE(s.days_set,' ','')) > 0)
          )
        ORDER BY s.time ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['uid'=>$userId, 'day'=>$dayShort]);
$rows = $stmt->fetchAll();

// taken logs today
$logStmt = $pdo->prepare("SELECT schedule_id FROM dose_logs WHERE user_id=? AND date=? AND taken=1");
$logStmt->execute([$userId, $todayDate]);
$takenMap = [];
foreach ($logStmt->fetchAll() as $r) $takenMap[(int)$r['schedule_id']] = true;

$total = count($rows);
$taken = count($takenMap);
$progressPct = $total ? round(($taken / $total) * 100) : 0;

include __DIR__ . '/partials/header.php';
?>
<style>
.dashboard-bg {
  background: linear-gradient(135deg, #e0f7fa 0%, #fce4ec 100%);
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  border-radius: 18px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.08);
  margin: 2rem auto;
  padding: 2.5rem;
  max-width: 700px;
}
.medicine-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  color: #1976d2;
}
</style>
<div class="dashboard-bg">
  <div class="medicine-icon">💊</div>
  <h1 style="margin-bottom:0.5rem;">Hello, <?= h($user_name) ?>!</h1>
  <p style="font-size:1.2rem;color:#444;margin-bottom:2rem;">
    Welcome to your Medicine Reminder dashboard.
  </p>
  <a href="/medicine-reminder/medicines.php" class="btn btn-primary" style="font-size:1.1rem;padding:0.75rem 2rem;">
    View Your Medicines
  </a>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
