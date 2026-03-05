<?php
require __DIR__ . '/config/db.php'; require_auth();
$errors = []; $success = false;

if (is_post()) {
  $name = trim($_POST['name'] ?? '');
  $amount = (int)($_POST['amount'] ?? 1);
  $dose_mg = $_POST['dose_mg'] !== '' ? (int)$_POST['dose_mg'] : null;
  $color = $_POST['color'] ?? 'blue';
  $frequency = $_POST['frequency'] ?? 'everyday';
  $days = $_POST['days'] ?? []; // array of Mon Tue ...
  $times = $_POST['times'] ?? [];

  if ($name === '' || !$times) $errors[] = 'Medicine name and at least one time are required.';
  if (!$errors) {
    $pdo->beginTransaction();
    try {
      $pdo->prepare("INSERT INTO medicines (user_id,name,dose_mg,color,notes) VALUES (?,?,?,?,NULL)")
          ->execute([current_user_id(), $name, $dose_mg, $color]);
      $medId = (int)$pdo->lastInsertId();

      $daysSet = $frequency === 'specific_days' ? implode(',', array_map('trim', $days)) : null;
      $stmt = $pdo->prepare("INSERT INTO schedules (user_id,medicine_id,amount,time,frequency,days_set) VALUES (?,?,?,?,?,?)");
      foreach ($times as $t) {
        if (!$t) continue;
        $stmt->execute([current_user_id(), $medId, $amount, $t, $frequency, $daysSet]);
      }
      $pdo->commit();
      $success = true;
    } catch (Throwable $e) {
      $pdo->rollBack();
      $errors[] = 'Failed to save: ' . $e->getMessage();
    }
  }
}
include __DIR__ . '/partials/header.php';
?>
<div class="card" style="max-width:720px;margin:0 auto;">
  <h2 class="section-title">Schedule the Dose</h2>
  <?php if ($success): ?>
    <div class="badge" style="color:var(--accent);border-color:var(--accent)">Saved! You can add another or go back to Dashboard.</div>
  <?php endif; ?>
  <?php if ($errors): ?><div class="badge" style="border-color:#ef4444;color:#ef4444"><?php echo h(implode(' ', $errors)); ?></div><?php endif; ?>

  <form method="post" class="grid">
    <div class="form-row">
      <label>Pill’s Name</label>
      <input class="input" name="name" placeholder="e.g. Probiotic" required />
    </div>

    <div class="grid cols-2">
      <div class="form-row">
        <label>Amount</label>
        <select class="input" name="amount">
          <?php for($i=1;$i<=4;$i++): ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?> pill<?php echo $i>1?'s':''; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-row">
        <label>Dose (mg)</label>
        <input class="input" type="number" name="dose_mg" placeholder="e.g. 250" />
      </div>
    </div>

    <div class="form-row">
      <label>Frequency</label>
      <div class="inline">
        <label class="badge"><input type="radio" name="frequency" value="everyday" checked /> Everyday</label>
        <label class="badge"><input type="radio" name="frequency" value="specific_days" /> Specific days</label>
      </div>
    </div>

    <div class="form-row">
      <label>Days of Week (if specific)</label>
      <div class="inline">
        <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
          <label class="badge"><input type="checkbox" name="days[]" value="<?php echo $d; ?>" /> <?php echo $d; ?></label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-row">
      <label>Reminder Time(s)</label>
      <div id="times-wrapper" class="grid">
        <div class="inline">
          <input type="time" name="times[]" required class="input" style="max-width:200px" />
          <button class="btn btn-ghost" data-add-time>+ Add time</button>
        </div>
      </div>
    </div>

    <div class="form-row">
      <label>Appearance</label>
      <div class="inline">
        <?php foreach (['blue','green','red','yellow'] as $c): ?>
          <label class="badge"><input type="radio" name="color" value="<?php echo $c; ?>" <?php echo $c==='blue'?'checked':''; ?> /> <?php echo ucfirst($c); ?></label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="inline">
      <button class="btn btn-primary" type="submit">Done</button>
      <a class="btn btn-ghost" href="/medicine-reminder/">Back</a>
    </div>
  </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
