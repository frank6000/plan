<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
$user = requireLogin();
$pdo = getPDO();

$projectId = (int)($_POST['project_id'] ?? 0);
$activityId = (int)($_POST['activity_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$date = $_POST['disbursement_date'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
$stmt->execute([$projectId]);
$project = $stmt->fetch();
if (!$project) { http_response_code(404); die('ไม่พบโครงการ'); }

if (!canEditProjectContent($project, $user)) {
    flash('danger', 'คุณไม่มีสิทธิ์บันทึกการเบิกจ่ายของโครงการนี้');
    redirect('/modules/projects/view.php?id=' . $projectId);
}

if ($amount <= 0 || !$date) {
    flash('danger', 'จำนวนเงินต้องมากกว่า 0 บาท และต้องระบุวันที่เบิกจ่าย');
    redirect('/modules/projects/view.php?id=' . $projectId . '#tab-activities');
}

$stmt = $pdo->prepare('INSERT INTO budget_disbursements (activity_id, project_id, amount, disbursement_date, recorded_by) VALUES (?,?,?,?,?)');
$stmt->execute([$activityId, $projectId, $amount, $date, $user['id']]);

flash('success', 'บันทึกการเบิกจ่ายเรียบร้อยแล้ว');
redirect('/modules/projects/view.php?id=' . $projectId . '#tab-budget');
