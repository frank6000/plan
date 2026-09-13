<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$projectId = (int)($_POST['project_id'] ?? 0);
$activityId = (int)($_POST['activity_id'] ?? 0);
$newStatusId = (int)($_POST['status_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canEditProjectContent($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์แก้ไขกิจกรรมนี้');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

// กันไม่ให้เลื่อนเป็น "ส่งขอจัดกิจกรรม" ถ้าโครงการแม่ยังไม่ถึง "อนุมัติโครงการ" (ข้อ 5.2)
$targetCode = $pdo->prepare('SELECT code FROM activity_status WHERE id=?');
$targetCode->execute([$newStatusId]);
$code = $targetCode->fetchColumn();

if ($code === 'act2') {
    $projectStatusOrder = $pdo->prepare('SELECT sort_order FROM project_status WHERE id=?');
    $projectStatusOrder->execute([$project['status_id']]);
    $currentOrder = (int)$projectStatusOrder->fetchColumn();
    $approvedOrder = (int)$pdo->query("SELECT sort_order FROM project_status WHERE code='13st8'")->fetchColumn();
    if ($currentOrder < $approvedOrder) {
        flash('danger', 'ไม่สามารถส่งขอจัดกิจกรรมได้ เนื่องจากโครงการยังไม่ได้รับการอนุมัติ');
        redirect('/modules/projects/view.php?id=' . $projectId . '#tab-activities');
    }
}

$pdo->prepare('UPDATE activities SET status_id=? WHERE id=? AND project_id=?')->execute([$newStatusId, $activityId, $projectId]);
$pdo->prepare('INSERT INTO activity_tracking_log (activity_id, status_id, tracking_date, recorded_by) VALUES (?,?,CURDATE(),?)')
    ->execute([$activityId, $newStatusId, $user['id']]);

flash('success', 'เปลี่ยนสถานะกิจกรรมเรียบร้อยแล้ว');
redirect('/modules/projects/view.php?id=' . $projectId . '#tab-activities');
