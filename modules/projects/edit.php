<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canEditProjectContent($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect('/modules/projects/view.php?id=' . $id);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['sub_project_name'] ?? '');
    if ($name === '') $errors[] = 'กรุณากรอก "ชื่อโครงการย่อย" ก่อนบันทึก';
    if (empty($_POST['fiscal_year_id'])) $errors[] = 'กรุณาเลือก "ปีงบประมาณ" ก่อนบันทึก';

    $ownerMode = $_POST['owner_mode'] ?? 'office';
    $ownerOfficeId = $ownerMode === 'office' ? ($_POST['owner_office_id'] ?: null) : null;
    $ownerFacilityId = $ownerMode === 'facility' ? ($_POST['owner_facility_id'] ?: null) : null;

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE projects SET
            sub_project_name=?, fiscal_year_id=?, network_id=?, plan_id=?, main_project_id=?,
            strategic_issue_id=?, moph_excellence_id=?, project_type=?, owner_office_id=?, owner_facility_id=?,
            planned_start_date=?, planned_end_date=?, situation_analysis=?, situation_data_source=?
            WHERE id=?');
        $stmt->execute([
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
            $id,
        ]);

        $pdo->prepare('DELETE FROM project_priority_levels WHERE project_id = ?')->execute([$id]);
        foreach (($_POST['priority_levels'] ?? []) as $plId) {
            $pdo->prepare('INSERT INTO project_priority_levels (project_id, priority_level_id) VALUES (?,?)')
                ->execute([$id, $plId]);
        }

        flash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
        redirect('/modules/projects/view.php?id=' . $id);
    }
    $project = array_merge($project, $_POST);
}

$pageTitle = 'แก้ไขโครงการ';
require_once __DIR__ . '/../../includes/header.php';
?>
<h4 class="mb-3"><i class="bi bi-pencil"></i> แก้ไขโครงการ: <?= h($project['sub_project_name']) ?></h4>
<?php foreach ($errors as $e): ?>
  <div class="alert alert-danger"><?= h($e) ?></div>
<?php endforeach; ?>
<div class="card">
  <div class="card-body">
    <form method="post">
      <?php require __DIR__ . '/_form.php'; ?>
      <div class="mt-4">
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
        <a href="/modules/projects/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">ยกเลิก</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
