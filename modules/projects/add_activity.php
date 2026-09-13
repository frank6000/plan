<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$projectId = (int)($_POST['project_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canEditProjectContent($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์แก้ไขโครงการนี้');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

$name = trim($_POST['activity_name'] ?? '');
if ($name === '') {
    flash('danger', 'กรุณากรอก "ชื่อกิจกรรม" ก่อนบันทึก');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

$pendingStatus = $pdo->query("SELECT id FROM activity_status WHERE code='act1'")->fetchColumn();

$stmt = $pdo->prepare('INSERT INTO activities
    (project_id, activity_name, responsible_person, budget_plan, budget_source_id, status_id, created_by)
    VALUES (?,?,?,?,?,?,?)');
$stmt->execute([
    $projectId,
    $name,
    trim($_POST['responsible_person'] ?? '') ?: null,
    (float)($_POST['budget_plan'] ?? 0),
    $_POST['budget_source_id'] ?: null,
    $pendingStatus,
    $user['id'],
]);

$pdo->prepare('UPDATE projects SET budget_approved = (SELECT COALESCE(SUM(budget_plan),0) FROM activities WHERE project_id=?) WHERE id=?')
    ->execute([$projectId, $projectId]);

flash('success', 'เพิ่มกิจกรรมเรียบร้อยแล้ว');
redirect('/modules/projects/view.php?id=' . $projectId . '#tab-activities');
