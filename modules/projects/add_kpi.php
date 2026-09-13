<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$projectId = (int)($_POST['project_id'] ?? 0);
$kpiId = (int)($_POST['kpi_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canEditProjectContent($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์แก้ไขโครงการนี้');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

if (!$kpiId) {
    flash('danger', 'กรุณาเลือก KPI ก่อนบันทึก');
    redirect('/modules/projects/view.php?id=' . $projectId . '#tab-kpi');
}

try {
    $pdo->prepare('INSERT INTO project_kpi (project_id, kpi_id, target_value, target_unit) VALUES (?,?,?,?)')
        ->execute([$projectId, $kpiId, $_POST['target_value'] ?: null, trim($_POST['target_unit'] ?? '') ?: null]);
    flash('success', 'ผูก KPI เข้ากับโครงการเรียบร้อยแล้ว');
} catch (PDOException $e) {
    flash('danger', 'KPI นี้ถูกผูกกับโครงการนี้อยู่แล้ว');
}

redirect('/modules/projects/view.php?id=' . $projectId . '#tab-kpi');
