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

$itemName = trim($_POST['item_name'] ?? '');
$qty = (float)($_POST['quantity'] ?? 0);
$price = (float)($_POST['unit_price'] ?? 0);

if ($itemName === '' || $qty <= 0 || $price < 0) {
    flash('danger', 'กรุณากรอกรายการวัสดุ จำนวน และราคาต่อหน่วยให้ถูกต้อง');
    redirect('/modules/projects/view.php?id=' . $projectId . '#tab-materials');
}

$pdo->prepare('INSERT INTO project_materials (project_id, item_name, quantity, unit_price) VALUES (?,?,?,?)')
    ->execute([$projectId, $itemName, $qty, $price]);

flash('success', 'เพิ่มรายการวัสดุเรียบร้อยแล้ว');
redirect('/modules/projects/view.php?id=' . $projectId . '#tab-materials');
