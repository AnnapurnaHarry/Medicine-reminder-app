<?php
require __DIR__ . '/config/db.php'; require_auth();
header('Content-Type: application/json');

$userId = current_user_id();
$scheduleId = isset($_POST['schedule_id']) ? (int)$_POST['schedule_id'] : 0;
$date = $_POST['date'] ?? date('Y-m-d');

if (!$scheduleId) { echo json_encode(['success'=>false,'message'=>'Missing schedule']); exit; }

// check if exists
$stmt = $pdo->prepare("SELECT id FROM dose_logs WHERE user_id=? AND schedule_id=? AND date=?");
$stmt->execute([$userId, $scheduleId, $date]);
$exist = $stmt->fetch();

if ($exist) {
  // toggle off (delete)
  $pdo->prepare("DELETE FROM dose_logs WHERE id=?")->execute([$exist['id']]);
  $taken = 0;
} else {
  // insert as taken
  $pdo->prepare("INSERT INTO dose_logs (user_id,schedule_id,date,taken) VALUES (?,?,?,1)")
      ->execute([$userId, $scheduleId, $date]);
  $taken = 1;
}

// recompute progress for today
$dayShort = date('D', strtotime($date));
$total = (int)$pdo->prepare("SELECT COUNT(*) FROM schedules s WHERE s.user_id=? AND (s.frequency='everyday' OR (s.frequency='specific_days' AND FIND_IN_SET(?, REPLACE(s.days_set,' ','')) > 0))")
                  ->execute([$userId, $dayShort]) ?: 0;
$stmtT = $pdo->prepare("SELECT COUNT(*) AS c FROM dose_logs WHERE user_id=? AND date=? AND taken=1");
$stmtT->execute([$userId, $date]);
$takenCount = (int)$stmtT->fetch()['c'];
$progressPct = $total ? round(($takenCount / $total) * 100) : 0;

echo json_encode(['success'=>true, 'taken'=>$taken, 'progressPct'=>$progressPct]);
