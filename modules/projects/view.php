<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('
  SELECT p.*, s.name AS status_name, s.code AS status_code, s.next_status_id, s.return_status_id,
         d.name AS office_name, f.name AS facility_name, fy.label AS fiscal_year_label,
         mp.name AS main_project_name, pl.name AS plan_name, si.name AS strategic_issue_name
  FROM projects p
  JOIN project_status s ON p.status_id = s.id
  LEFT JOIN departments d ON p.owner_office_id = d.id
  LEFT JOIN health_facilities f ON p.owner_facility_id = f.id
  LEFT JOIN fiscal_years fy ON p.fiscal_year_id = fy.id
  LEFT JOIN main_projects mp ON p.main_project_id = mp.id
  LEFT JOIN plans pl ON p.plan_id = pl.id
  LEFT JOIN strategic_issues si ON p.strategic_issue_id = si.id
  WHERE p.id = ?
');
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

$canEdit = canEditProjectContent($project, $user);
$canChangeStatus = canChangeStatus($project, $user);
$canFreeSelect = canFreeSelectStatus($user);

// กิจกรรม
$stmt = $pdo->prepare("
  SELECT a.*, ast.name AS status_name, ast.code AS status_code, bs.name AS budget_source_name
  FROM activities a
  JOIN activity_status ast ON a.status_id = ast.id
  LEFT JOIN budget_sources bs ON a.budget_source_id = bs.id
  WHERE a.project_id = ? ORDER BY a.id
");
$stmt->execute([$id]);
$activities = $stmt->fetchAll();
$activityStatuses = $pdo->query('SELECT id, code, name FROM activity_status ORDER BY sort_order')->fetchAll();
$budgetSources = $pdo->query('SELECT id, name FROM budget_sources ORDER BY name')->fetchAll();

// งบประมาณ: เบิกจ่ายจริง
$stmt = $pdo->prepare("
  SELECT bd.*, a.activity_name FROM budget_disbursements bd
  JOIN activities a ON bd.activity_id = a.id
  WHERE bd.project_id = ? ORDER BY bd.disbursement_date DESC
");
$stmt->execute([$id]);
$disbursements = $stmt->fetchAll();
$totalDisbursed = array_sum(array_column($disbursements, 'amount'));

// รายการวัสดุ
$materials = [];
if ($project['project_type'] === 'material_procurement') {
    $stmt = $pdo->prepare('SELECT * FROM project_materials WHERE project_id = ? ORDER BY id');
    $stmt->execute([$id]);
    $materials = $stmt->fetchAll();
}

// ประวัติสถานะ
$stmt = $pdo->prepare("
  SELECT ptl.*, s.name AS status_name FROM project_tracking_log ptl
  JOIN project_status s ON ptl.status_id = s.id
  WHERE ptl.project_id = ? ORDER BY ptl.tracking_date DESC, ptl.id DESC
");
$stmt->execute([$id]);
$trackingLog = $stmt->fetchAll();
$allStatuses = $pdo->query('SELECT id, name FROM project_status ORDER BY sort_order')->fetchAll();

$nextStatus = null; $returnStatus = null;
if ($project['next_status_id']) {
    $st = $pdo->prepare('SELECT id, name FROM project_status WHERE id=?');
    $st->execute([$project['next_status_id']]);
    $nextStatus = $st->fetch();
}
if ($project['return_status_id']) {
    $st = $pdo->prepare('SELECT id, name FROM project_status WHERE id=?');
    $st->execute([$project['return_status_id']]);
    $returnStatus = $st->fetch();
}

// KPI
$stmt = $pdo->prepare("
  SELECT pk.*, k.name AS kpi_name, k.code AS kpi_code FROM project_kpi pk
  JOIN kpi_master k ON pk.kpi_id = k.id
  WHERE pk.project_id = ? ORDER BY pk.id
");
$stmt->execute([$id]);
$projectKpis = $stmt->fetchAll();
$allKpis = $pdo->query('SELECT id, code, name FROM kpi_master ORDER BY code')->fetchAll();

$activityDone = count(array_filter($activities, fn($a) => $a['status_code'] === 'act3'));

$pageTitle = $project['sub_project_name'];
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="mb-1"><?= h($project['sub_project_name']) ?></h4>
    <code><?= h($project['project_code']) ?></code>
    <span class="badge bg-secondary ms-2"><?= h($project['status_name']) ?></span>
    <span class="badge bg-info text-dark ms-1"><?= $project['project_type'] === 'material_procurement' ? 'จัดซื้อวัสดุ' : 'ทั่วไป' ?></span>
  </div>
  <div>
    <?php if ($canEdit): ?>
      <a href="/modules/projects/edit.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> แก้ไข</a>
    <?php endif; ?>
    <a href="/modules/projects/index.php" class="btn btn-outline-secondary btn-sm">กลับรายการ</a>
  </div>
</div>

<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-general">ข้อมูลทั่วไป</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activities">กิจกรรม (<?= $activityDone ?>/<?= count($activities) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-budget">งบประมาณ</button></li>
  <?php if ($project['project_type'] === 'material_procurement'): ?>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-materials">รายการวัสดุ</button></li>
  <?php endif; ?>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-status">ประวัติสถานะ</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kpi">KPI/OKR</button></li>
</ul>

<div class="tab-content">

  <!-- ข้อมูลทั่วไป -->
  <div class="tab-pane fade show active" id="tab-general">
    <div class="card"><div class="card-body">
      <div class="row g-3">
        <div class="col-md-6"><strong>ปีงบประมาณ:</strong> <?= h($project['fiscal_year_label']) ?></div>
        <div class="col-md-6"><strong>หน่วยงาน:</strong> <?= h($project['office_name'] ?? $project['facility_name'] ?? '-') ?></div>
        <div class="col-md-6"><strong>แผนงาน:</strong> <?= h($project['plan_name'] ?? '-') ?></div>
        <div class="col-md-6"><strong>โครงการหลัก:</strong> <?= h($project['main_project_name'] ?? '-') ?></div>
        <div class="col-md-12"><strong>ประเด็นยุทธศาสตร์:</strong> <?= h($project['strategic_issue_name'] ?? '-') ?></div>
        <div class="col-md-6"><strong>วันที่เริ่ม (แผน):</strong> <?= thai_date($project['planned_start_date']) ?></div>
        <div class="col-md-6"><strong>วันที่สิ้นสุด (แผน):</strong> <?= thai_date($project['planned_end_date']) ?></div>
        <div class="col-12"><hr>
          <strong>สถานการณ์ปัญหา:</strong>
          <p><?= nl2br(h($project['situation_analysis'] ?: '(ยังไม่ได้กรอก)')) ?></p>
          <strong>แหล่งข้อมูลอ้างอิง:</strong> <?= h($project['situation_data_source'] ?: '-') ?>
        </div>
      </div>
    </div></div>
  </div>

  <!-- กิจกรรม -->
  <div class="tab-pane fade" id="tab-activities">
    <div class="card mb-3"><div class="card-body">
      <h6>เพิ่มกิจกรรมใหม่</h6>
      <form method="post" action="/modules/projects/add_activity.php" class="row g-2">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <div class="col-md-4"><input type="text" name="activity_name" class="form-control" placeholder="ชื่อกิจกรรม *" required></div>
        <div class="col-md-3"><input type="text" name="responsible_person" class="form-control" placeholder="ผู้รับผิดชอบ"></div>
        <div class="col-md-2"><input type="number" step="0.01" min="0" name="budget_plan" class="form-control" placeholder="งบประมาณ" value="0" required></div>
        <div class="col-md-2">
          <select name="budget_source_id" class="form-select">
            <option value="">แหล่งงบ</option>
            <?php foreach ($budgetSources as $bs): ?><option value="<?= $bs['id'] ?>"><?= h($bs['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i></button></div>
      </form>
    </div></div>

    <div class="card"><div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead class="table-light"><tr><th>กิจกรรม</th><th>ผู้รับผิดชอบ</th><th class="text-end">งบ</th><th>แหล่งงบ</th><th>สถานะ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($activities as $a): ?>
          <tr>
            <td><?= h(mb_substr($a['activity_name'], 0, 120)) ?><?= mb_strlen($a['activity_name']) > 120 ? '…' : '' ?></td>
            <td><?= h($a['responsible_person'] ?: '-') ?></td>
            <td class="text-end"><?= money($a['budget_plan']) ?></td>
            <td><?= h($a['budget_source_name'] ?? '-') ?></td>
            <td>
              <?php if ($canEdit): ?>
              <form method="post" action="/modules/projects/activity_status.php" class="d-flex gap-1">
                <input type="hidden" name="activity_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="project_id" value="<?= $id ?>">
                <select name="status_id" class="form-select form-select-sm" onchange="this.form.submit()">
                  <?php foreach ($activityStatuses as $as): ?>
                    <option value="<?= $as['id'] ?>" <?= $as['code'] === $a['status_code'] ? 'selected' : '' ?>><?= h($as['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
              <?php else: ?>
                <span class="badge bg-secondary"><?= h($a['status_name']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-success" data-bs-toggle="collapse" data-bs-target="#disb-<?= $a['id'] ?>">บันทึกเบิกจ่าย</button>
            </td>
          </tr>
          <tr class="collapse" id="disb-<?= $a['id'] ?>"><td colspan="6">
            <form method="post" action="/modules/projects/record_disbursement.php" class="row g-2">
              <input type="hidden" name="activity_id" value="<?= $a['id'] ?>">
              <input type="hidden" name="project_id" value="<?= $id ?>">
              <div class="col-md-3"><input type="number" step="0.01" min="0.01" name="amount" class="form-control" placeholder="จำนวนเงิน *" required></div>
              <div class="col-md-3"><input type="date" name="disbursement_date" class="form-control" required></div>
              <div class="col-md-3"><button class="btn btn-success btn-sm">บันทึกการเบิกจ่าย</button></div>
            </form>
          </td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div></div>
  </div>

  <!-- งบประมาณ -->
  <div class="tab-pane fade" id="tab-budget">
    <div class="row g-3 mb-3">
      <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">งบประมาณอนุมัติ (รวมจากทุกกิจกรรม)</div>
        <div class="fs-4 fw-bold"><?= money($project['budget_approved']) ?></div>
      </div></div></div>
      <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">เบิกจ่ายแล้ว</div>
        <div class="fs-4 fw-bold text-success"><?= money($totalDisbursed) ?></div>
      </div></div></div>
      <div class="col-md-4"><div class="card"><div class="card-body">
        <div class="text-muted small">คงเหลือ</div>
        <div class="fs-4 fw-bold"><?= money($project['budget_approved'] - $totalDisbursed) ?></div>
      </div></div></div>
    </div>
    <div class="card"><div class="table-responsive">
      <table class="table mb-0"><thead class="table-light"><tr><th>วันที่</th><th>กิจกรรม</th><th class="text-end">จำนวนเงิน</th></tr></thead>
      <tbody>
        <?php if (!$disbursements): ?><tr><td colspan="3" class="text-center text-muted py-3">ยังไม่มีการเบิกจ่าย</td></tr><?php endif; ?>
        <?php foreach ($disbursements as $d): ?>
          <tr><td><?= thai_date($d['disbursement_date']) ?></td><td><?= h(mb_substr($d['activity_name'],0,80)) ?></td><td class="text-end"><?= money($d['amount']) ?></td></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div></div>
  </div>

  <?php if ($project['project_type'] === 'material_procurement'): ?>
  <!-- รายการวัสดุ -->
  <div class="tab-pane fade" id="tab-materials">
    <div class="card mb-3"><div class="card-body">
      <form method="post" action="/modules/projects/add_material.php" class="row g-2">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <div class="col-md-4"><input type="text" name="item_name" class="form-control" placeholder="รายการวัสดุ *" required></div>
        <div class="col-md-2"><input type="number" step="0.01" name="quantity" class="form-control" placeholder="จำนวน *" required></div>
        <div class="col-md-2"><input type="number" step="0.01" name="unit_price" class="form-control" placeholder="ราคาต่อหน่วย *" required></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">เพิ่ม</button></div>
      </form>
    </div></div>
    <div class="card"><div class="table-responsive">
      <table class="table mb-0"><thead class="table-light"><tr><th>รายการ</th><th class="text-end">จำนวน</th><th class="text-end">ราคา/หน่วย</th><th class="text-end">รวม</th></tr></thead>
      <tbody>
        <?php $matTotal = 0; foreach ($materials as $m): $matTotal += $m['total_price']; ?>
          <tr><td><?= h($m['item_name']) ?></td><td class="text-end"><?= $m['quantity'] ?></td><td class="text-end"><?= money($m['unit_price']) ?></td><td class="text-end"><?= money($m['total_price']) ?></td></tr>
        <?php endforeach; ?>
        <tr class="table-light fw-bold"><td colspan="3" class="text-end">รวมทั้งหมด</td><td class="text-end"><?= money($matTotal) ?></td></tr>
      </tbody></table>
    </div></div>
  </div>
  <?php endif; ?>

  <!-- ประวัติสถานะ -->
  <div class="tab-pane fade" id="tab-status">
    <div class="card mb-3"><div class="card-body">
      <h6>เปลี่ยนสถานะโครงการ</h6>
      <?php if ($canChangeStatus): ?>
        <form method="post" action="/modules/projects/status_change.php" class="d-flex flex-wrap gap-2 align-items-center">
          <input type="hidden" name="project_id" value="<?= $id ?>">
          <?php if ($nextStatus): ?>
            <button name="action" value="approve" class="btn btn-success btn-sm"><i class="bi bi-check-lg"></i> อนุมัติ → <?= h($nextStatus['name']) ?></button>
          <?php endif; ?>
          <?php if ($returnStatus): ?>
            <button name="action" value="return" class="btn btn-warning btn-sm"><i class="bi bi-arrow-return-left"></i> ตีกลับ → <?= h($returnStatus['name']) ?></button>
          <?php endif; ?>
          <input type="text" name="remark" class="form-control form-control-sm" style="max-width:250px" placeholder="หมายเหตุ (ถ้ามี)">
        </form>
        <?php if ($canFreeSelect): ?>
        <form method="post" action="/modules/projects/status_change.php" class="d-flex gap-2 mt-2 align-items-center">
          <input type="hidden" name="project_id" value="<?= $id ?>">
          <input type="hidden" name="action" value="free_select">
          <select name="status_id" class="form-select form-select-sm" style="max-width:250px">
            <?php foreach ($allStatuses as $s): ?>
              <option value="<?= $s['id'] ?>" <?= $s['id'] == $project['status_id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-outline-dark btn-sm">เลือกสถานะอิสระ (สสจ./admin)</button>
        </form>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted mb-0">คุณไม่มีสิทธิ์เปลี่ยนสถานะโครงการนี้</p>
      <?php endif; ?>
    </div></div>

    <div class="card"><div class="table-responsive">
      <table class="table mb-0"><thead class="table-light"><tr><th>วันที่</th><th>สถานะ</th><th>หมายเหตุ</th><th>ผู้บันทึก</th></tr></thead>
      <tbody>
        <?php foreach ($trackingLog as $t): ?>
          <tr><td><?= thai_date($t['tracking_date']) ?></td><td><?= h($t['status_name']) ?></td><td><?= h($t['remark'] ?: '-') ?></td><td><?= h($t['pm_name'] ?: '-') ?></td></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div></div>
  </div>

  <!-- KPI/OKR -->
  <div class="tab-pane fade" id="tab-kpi">
    <div class="card mb-3"><div class="card-body">
      <h6>ผูก KPI หลักเข้ากับโครงการ</h6>
      <form method="post" action="/modules/projects/add_kpi.php" class="row g-2">
        <input type="hidden" name="project_id" value="<?= $id ?>">
        <div class="col-md-6">
          <select name="kpi_id" class="form-select" required>
            <option value="">-- เลือก KPI --</option>
            <?php foreach ($allKpis as $k): ?><option value="<?= $k['id'] ?>"><?= h($k['code'] . ' - ' . $k['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><input type="text" name="target_value" class="form-control" placeholder="ค่าเป้าหมาย"></div>
        <div class="col-md-2"><input type="text" name="target_unit" class="form-control" placeholder="หน่วย"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i></button></div>
      </form>
    </div></div>
    <div class="card"><div class="table-responsive">
      <table class="table mb-0"><thead class="table-light"><tr><th>KPI</th><th>ค่าเป้าหมาย</th><th>หน่วย</th></tr></thead>
      <tbody>
        <?php if (!$projectKpis): ?><tr><td colspan="3" class="text-center text-muted py-3">ยังไม่ได้ผูก KPI</td></tr><?php endif; ?>
        <?php foreach ($projectKpis as $pk): ?>
          <tr><td><?= h($pk['kpi_code'] . ' - ' . $pk['kpi_name']) ?></td><td><?= h($pk['target_value'] ?? '-') ?></td><td><?= h($pk['target_unit'] ?? '-') ?></td></tr>
        <?php endforeach; ?>
      </tbody></table>
    </div></div>
    <p class="text-muted small mt-2">* ระบบ Objective/Key Results (OKR) แบบเต็มรูปแบบยังไม่ได้พัฒนาในเวอร์ชันนี้</p>
  </div>

</div>
<script>
if (location.hash) {
  const btn = document.querySelector('button[data-bs-target="' + location.hash + '"]');
  if (btn) new bootstrap.Tab(btn).show();
}
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
