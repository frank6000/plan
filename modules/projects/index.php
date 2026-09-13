<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

[$visSql, $visParams] = projectVisibilitySql($user);

$q = trim($_GET['q'] ?? '');
$statusId = $_GET['status_id'] ?? '';
$deptId = $_GET['owner_office_id'] ?? '';
$projectType = $_GET['project_type'] ?? '';

$where = [$visSql];
$params = $visParams;

if ($q !== '') {
    $where[] = 'p.sub_project_name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($statusId !== '') {
    $where[] = 'p.status_id = ?';
    $params[] = $statusId;
}
if ($deptId !== '') {
    $where[] = 'p.owner_office_id = ?';
    $params[] = $deptId;
}
if ($projectType !== '') {
    $where[] = 'p.project_type = ?';
    $params[] = $projectType;
}

$sql = "
  SELECT p.id, p.project_code, p.sub_project_name, p.project_type, p.budget_approved,
         s.name AS status_name, s.code AS status_code,
         d.name AS office_name, f.name AS facility_name,
         (SELECT COUNT(*) FROM activities a WHERE a.project_id = p.id) AS activity_count,
         (SELECT COUNT(*) FROM activities a JOIN activity_status ast ON a.status_id=ast.id WHERE a.project_id=p.id AND ast.code='act3') AS activity_done_count
  FROM projects p
  JOIN project_status s ON p.status_id = s.id
  LEFT JOIN departments d ON p.owner_office_id = d.id
  LEFT JOIN health_facilities f ON p.owner_facility_id = f.id
  WHERE " . implode(' AND ', $where) . "
  ORDER BY p.created_at DESC
  LIMIT 200
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

$statusList = $pdo->query('SELECT id, code, name FROM project_status ORDER BY sort_order')->fetchAll();
$deptList = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();

$pageTitle = 'รายการโครงการ';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-folder"></i> รายการโครงการ</h4>
  <a href="/modules/projects/add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> เพิ่มโครงการ</a>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="get" class="row g-2">
      <div class="col-md-4">
        <input type="text" name="q" class="form-control" placeholder="ค้นหาชื่อโครงการ..." value="<?= h($q) ?>">
      </div>
      <div class="col-md-3">
        <select name="status_id" class="form-select">
          <option value="">-- ทุกสถานะ --</option>
          <?php foreach ($statusList as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $statusId == $s['id'] ? 'selected' : '' ?>><?= h($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select name="owner_office_id" class="form-select">
          <option value="">-- ทุกกลุ่มงาน --</option>
          <?php foreach ($deptList as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $deptId == $d['id'] ? 'selected' : '' ?>><?= h($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="project_type" class="form-select">
          <option value="">-- ทุกประเภท --</option>
          <option value="general" <?= $projectType === 'general' ? 'selected' : '' ?>>ทั่วไป</option>
          <option value="material_procurement" <?= $projectType === 'material_procurement' ? 'selected' : '' ?>>จัดซื้อวัสดุ</option>
        </select>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i> ค้นหา</button>
        <a href="/modules/projects/index.php" class="btn btn-outline-secondary btn-sm">ล้างตัวกรอง</a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>รหัสโครงการ</th>
          <th>ชื่อโครงการ</th>
          <th>หน่วยงาน</th>
          <th>ประเภท</th>
          <th class="text-end">งบประมาณ</th>
          <th>กิจกรรม</th>
          <th>สถานะ</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$projects): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">ไม่พบโครงการ</td></tr>
      <?php endif; ?>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td><code><?= h($p['project_code']) ?></code></td>
          <td><a href="/modules/projects/view.php?id=<?= $p['id'] ?>"><?= h($p['sub_project_name']) ?></a></td>
          <td><?= h($p['office_name'] ?? $p['facility_name'] ?? '-') ?></td>
          <td><?= $p['project_type'] === 'material_procurement' ? 'จัดซื้อวัสดุ' : 'ทั่วไป' ?></td>
          <td class="text-end"><?= money($p['budget_approved']) ?></td>
          <td><?= (int)$p['activity_done_count'] ?>/<?= (int)$p['activity_count'] ?></td>
          <td><span class="badge bg-secondary status-badge"><?= h($p['status_name']) ?></span></td>
          <td><a href="/modules/projects/view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">ดู</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="text-muted small mt-2">แสดงสูงสุด 200 รายการ ทั้งหมด <?= count($projects) ?> รายการ (ตามสิทธิ์การมองเห็นของคุณ)</p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
