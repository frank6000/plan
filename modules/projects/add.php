<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$project = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['sub_project_name'] ?? '');
    if ($name === '') $errors[] = 'กรุณากรอก "ชื่อโครงการย่อย" ก่อนบันทึก';
    if (empty($_POST['fiscal_year_id'])) $errors[] = 'กรุณาเลือก "ปีงบประมาณ" ก่อนบันทึก';

    $ownerMode = $_POST['owner_mode'] ?? 'office';
    $ownerOfficeId = $ownerMode === 'office' ? ($_POST['owner_office_id'] ?: null) : null;
    $ownerFacilityId = $ownerMode === 'facility' ? ($_POST['owner_facility_id'] ?: null) : null;

    if (!$errors) {
        $draftStatus = $pdo->query("SELECT id FROM project_status WHERE code='1st0'")->fetchColumn();
        $projectCode = 'PJ' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $stmt = $pdo->prepare('INSERT INTO projects
            (project_code, sub_project_name, fiscal_year_id, network_id, plan_id, main_project_id,
             strategic_issue_id, moph_excellence_id, project_type, owner_office_id, owner_facility_id,
             planned_start_date, planned_end_date, budget_approved, situation_analysis, situation_data_source,
             status_id, created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?,?)');
        $stmt->execute([
            $projectCode,
            $name,
            $_POST['fiscal_year_id'],
            $_POST['network_id'] ?: null,
            $_POST['plan_id'] ?: null,
            $_POST['main_project_id'] ?: null,
            $_POST['strategic_issue_id'] ?: null,
            $_POST['moph_excellence_id'] ?: null,
            $_POST['project_type'] ?? 'general',
            $ownerOfficeId,
            $ownerFacilityId,
            $_POST['planned_start_date'] ?: null,
            $_POST['planned_end_date'] ?: null,
            trim($_POST['situation_analysis'] ?? ''),
            trim($_POST['situation_data_source'] ?? ''),
            $draftStatus,
            $user['id'],
        ]);
        $projectId = $pdo->lastInsertId();

        foreach (($_POST['priority_levels'] ?? []) as $plId) {
            $pdo->prepare('INSERT INTO project_priority_levels (project_id, priority_level_id) VALUES (?,?)')
                ->execute([$projectId, $plId]);
        }

        flash('success', 'บันทึกโครงการเรียบร้อยแล้ว');
        redirect('/modules/projects/view.php?id=' . $projectId);
    }
}

$pageTitle = 'เพิ่มโครงการ';
require_once __DIR__ . '/../../includes/header.php';
?>
<h4 class="mb-3"><i class="bi bi-plus-lg"></i> เพิ่มโครงการใหม่</h4>
<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><?= h($e) ?></div>
<?php endforeach; ?>
<div class="card">
  <div class="card-body">
    <form method="post">
      <?php require __DIR__ . '/_form.php'; ?>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary">บันทึกโครงการ</button>
        <a href="/modules/projects/index.php" class="btn btn-outline-secondary">ยกเลิก</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
