<?php
/** @var array|null $project */
/** @var PDO $pdo */
$plans = $pdo->query('SELECT id, code, name FROM plans ORDER BY code')->fetchAll();
$strategicIssues = $pdo->query('SELECT id, name FROM strategic_issues ORDER BY id')->fetchAll();
$mophList = $pdo->query('SELECT id, code, name FROM moph_excellence ORDER BY code')->fetchAll();
$networks = $pdo->query('SELECT id, name FROM health_networks ORDER BY id')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$facilities = $pdo->query('SELECT id, name FROM health_facilities ORDER BY name')->fetchAll();
$fiscalYears = $pdo->query('SELECT id, label, is_active FROM fiscal_years ORDER BY year_be DESC')->fetchAll();
$priorityLevels = $pdo->query('SELECT id, name FROM priority_levels WHERE is_active=1 ORDER BY sort_order')->fetchAll();
$mainProjects = [];
if ($project && $project['plan_id']) {
    $st = $pdo->prepare('SELECT id, code, name FROM main_projects WHERE plan_id = ? ORDER BY code');
    $st->execute([$project['plan_id']]);
    $mainProjects = $st->fetchAll();
}
$selectedPriority = [];
if ($project) {
    $st = $pdo->prepare('SELECT priority_level_id FROM project_priority_levels WHERE project_id = ?');
    $st->execute([$project['id']]);
    $selectedPriority = array_column($st->fetchAll(), 'priority_level_id');
}
$v = fn($key, $default = '') => h((string)($project[$key] ?? $default));
$ownerMode = $project && $project['owner_facility_id'] ? 'facility' : 'office';
?>
<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label">ชื่อโครงการย่อย <span class="text-danger">*</span></label>
    <input type="text" name="sub_project_name" class="form-control" required value="<?= $v('sub_project_name') ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">ประเภทโครงการ <span class="text-danger">*</span></label>
    <select name="project_type" class="form-select" required>
      <option value="general" <?= ($project['project_type'] ?? '') === 'general' ? 'selected' : '' ?>>ทั่วไป</option>
      <option value="material_procurement" <?= ($project['project_type'] ?? '') === 'material_procurement' ? 'selected' : '' ?>>จัดซื้อวัสดุ</option>
    </select>
  </div>

  <div class="col-md-4">
    <label class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
    <select name="fiscal_year_id" class="form-select" required>
      <?php foreach ($fiscalYears as $fy): ?>
        <option value="<?= $fy['id'] ?>" <?= (($project['fiscal_year_id'] ?? '') == $fy['id']) || (!$project && $fy['is_active']) ? 'selected' : '' ?>><?= h($fy['label']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">เครือข่ายบริการสุขภาพ</label>
    <select name="network_id" class="form-select">
      <?php foreach ($networks as $n): ?>
        <option value="<?= $n['id'] ?>" <?= ($project['network_id'] ?? '') == $n['id'] ? 'selected' : '' ?>><?= h($n['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <label class="form-label">ประเด็น Excellence</label>
    <select name="moph_excellence_id" class="form-select">
      <option value="">-- ไม่ระบุ --</option>
      <?php foreach ($mophList as $m): ?>
        <option value="<?= $m['id'] ?>" <?= ($project['moph_excellence_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= h($m['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">แผนงาน <i class="bi bi-info-circle" title="เลือกแผนงานก่อน ระบบจะกรองโครงการหลักที่เกี่ยวข้องให้อัตโนมัติ"></i></label>
    <select name="plan_id" id="plan_id" class="form-select">
      <option value="">-- ไม่ระบุ --</option>
      <?php foreach ($plans as $p): ?>
        <option value="<?= $p['id'] ?>" <?= ($project['plan_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= h($p['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">โครงการหลัก</label>
    <select name="main_project_id" id="main_project_id" class="form-select">
      <option value="">-- เลือกแผนงานก่อน --</option>
      <?php foreach ($mainProjects as $mp): ?>
        <option value="<?= $mp['id'] ?>" <?= ($project['main_project_id'] ?? '') == $mp['id'] ? 'selected' : '' ?>><?= h($mp['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-md-12">
    <label class="form-label">ประเด็นยุทธศาสตร์ที่เกี่ยวข้อง</label>
    <select name="strategic_issue_id" class="form-select">
      <option value="">-- ไม่ระบุ --</option>
      <?php foreach ($strategicIssues as $s): ?>
        <option value="<?= $s['id'] ?>" <?= ($project['strategic_issue_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12"><hr><h6>หน่วยงานเจ้าของโครงการ</h6></div>
  <div class="col-md-4">
    <div class="form-check">
      <input class="form-check-input" type="radio" name="owner_mode" id="owner_office" value="office" <?= $ownerMode === 'office' ? 'checked' : '' ?>>
      <label class="form-check-label" for="owner_office">กลุ่มงานภายใน สสอ.</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="owner_mode" id="owner_facility" value="facility" <?= $ownerMode === 'facility' ? 'checked' : '' ?>>
      <label class="form-check-label" for="owner_facility">หน่วยบริการ</label>
    </div>
  </div>
  <div class="col-md-4">
    <select name="owner_office_id" class="form-select">
      <option value="">-- เลือกกลุ่มงาน --</option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($project['owner_office_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= h($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-4">
    <select name="owner_facility_id" class="form-select">
      <option value="">-- เลือกหน่วยบริการ --</option>
      <?php foreach ($facilities as $f): ?>
        <option value="<?= $f['id'] ?>" <?= ($project['owner_facility_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= h($f['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="col-12"><hr><h6>เหตุผล/ที่มาของโครงการ (Situation Analysis) <i class="bi bi-info-circle" title="ไม่บังคับตอนบันทึกร่าง แต่บังคับก่อนกดเสนอโครงการ"></i></h6></div>
  <div class="col-md-8">
    <label class="form-label">สถานการณ์ปัญหา</label>
    <textarea name="situation_analysis" class="form-control" rows="3"><?= $v('situation_analysis') ?></textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">แหล่งข้อมูลอ้างอิง</label>
    <input type="text" name="situation_data_source" class="form-control" value="<?= $v('situation_data_source') ?>">
  </div>

  <div class="col-md-6">
    <label class="form-label">วันที่เริ่มดำเนินการ (แผน)</label>
    <input type="date" name="planned_start_date" class="form-control" value="<?= $v('planned_start_date') ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">วันที่สิ้นสุดดำเนินการ (แผน)</label>
    <input type="date" name="planned_end_date" class="form-control" value="<?= $v('planned_end_date') ?>">
  </div>

  <div class="col-12">
    <label class="form-label">ระดับความสำคัญ/ที่มาของโครงการ</label>
    <div>
      <?php foreach ($priorityLevels as $pl): ?>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="priority_levels[]" value="<?= $pl['id'] ?>" id="pl<?= $pl['id'] ?>"
            <?= in_array($pl['id'], $selectedPriority) ? 'checked' : '' ?>>
          <label class="form-check-label" for="pl<?= $pl['id'] ?>"><?= h($pl['name']) ?></label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
document.getElementById('plan_id').addEventListener('change', function () {
  const planId = this.value;
  const sel = document.getElementById('main_project_id');
  sel.innerHTML = '<option value="">กำลังโหลด...</option>';
  if (!planId) { sel.innerHTML = '<option value="">-- เลือกแผนงานก่อน --</option>'; return; }
  fetch('/api/main_projects.php?plan_id=' + planId)
    .then((r) => r.json())
    .then((rows) => {
      sel.innerHTML = '<option value="">-- ไม่ระบุ --</option>' +
        rows.map((r) => `<option value="${r.id}">${r.name}</option>`).join('');
    });
});
</script>
