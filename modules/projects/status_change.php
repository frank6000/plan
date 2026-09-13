<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$projectId = (int)($_POST['project_id'] ?? 0);
$action = $_POST['action'] ?? '';
$remark = trim($_POST['remark'] ?? '') ?: null;

$stmt = $pdo->prepare('SELECT p.*, s.next_status_id, s.return_status_id FROM projects p JOIN project_status s ON p.status_id = s.id WHERE p.id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canChangeStatus($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์เปลี่ยนสถานะโครงการนี้');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

if ($action === 'approve') {
    $targetStatusId = $project['next_status_id'];
} elseif ($action === 'return') {
    $targetStatusId = $project['return_status_id'];
} elseif ($action === 'free_select') {
    if (!canFreeSelectStatus($user)) {
        flash('danger', 'คุณไม่มีสิทธิ์เลือกสถานะอิสระ');
        redirect('/modules/projects/view.php?id=' . $projectId);
    }
    $targetStatusId = (int)($_POST['status_id'] ?? 0);
} else {
    $targetStatusId = null;
}

if (!$targetStatusId) {
    flash('danger', 'ไม่สามารถเปลี่ยนสถานะได้ (ไม่มีสถานะปลายทาง)');
    redirect('/modules/projects/view.php?id=' . $projectId . '#tab-status');
}

$targetCode = $pdo->prepare('SELECT code, name FROM project_status WHERE id = ?');
$targetCode->execute([$targetStatusId]);
$target = $targetCode->fetch();

// กฎตรวจสอบอัตโนมัติ (ข้อ 17.3)
if ($target['code'] === '2st1') {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM project_priority_levels WHERE project_id=?');
    $cnt->execute([$projectId]);
    $priorityCount = (int)$cnt->fetchColumn();
    if (trim($project['situation_analysis']) === '' || trim($project['situation_data_source']) === '' || $priorityCount < 1) {
        flash('danger', 'กรุณากรอกสถานการณ์ปัญหาและเลือกระดับความสำคัญอย่างน้อย 1 รายการ ก่อนเสนอโครงการ');
        redirect('/modules/projects/view.php?id=' . $projectId . '#tab-status');
    }
}

if ($target['code'] === '13st8') {
    $cnt = $pdo->prepare('SELECT COUNT(*) FROM activities WHERE project_id=?');
    $cnt->execute([$projectId]);
    if ((int)$cnt->fetchColumn() === 0) {
        flash('danger', 'ไม่สามารถอนุมัติโครงการที่ยังไม่มีกิจกรรมได้');
        redirect('/modules/projects/view.php?id=' . $projectId . '#tab-status');
    }
}

if ($target['code'] === '14d9' && (float)$project['budget_approved'] <= 0) {
    flash('danger', 'ไม่สามารถส่งขอโอนเงินได้ เนื่องจากโครงการนี้ยังไม่มีงบประมาณ');
    redirect('/modules/projects/view.php?id=' . $projectId . '#tab-status');
}

$pdo->prepare('UPDATE projects SET status_id = ? WHERE id = ?')->execute([$targetStatusId, $projectId]);
$pdo->prepare('INSERT INTO project_tracking_log (project_id, status_id, tracking_date, remark, pm_name, recorded_by) VALUES (?,?,CURDATE(),?,?,?)')
    ->execute([$projectId, $targetStatusId, $remark, $user['name'], $user['id']]);

flash('success', 'เปลี่ยนสถานะเป็น "' . $target['name'] . '" เรียบร้อยแล้ว');
redirect('/modules/projects/view.php?id=' . $projectId . '#tab-status');
