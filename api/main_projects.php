<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getPDO();
$planId = $_GET['plan_id'] ?? null;
if (!$planId) { echo '[]'; exit; }

$stmt = $pdo->prepare('SELECT id, code, name FROM main_projects WHERE plan_id = ? ORDER BY code');
$stmt->execute([$planId]);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
