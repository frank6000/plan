<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getPDO();
$mainProjectId = $_GET['main_project_id'] ?? null;
if (!$mainProjectId) { echo '[]'; exit; }

$stmt = $pdo->prepare('SELECT id, code, name FROM kpi_master WHERE main_project_id = ? ORDER BY code');
$stmt->execute([$mainProjectId]);
$rows = $stmt->fetchAll();
if (!$rows) {
    $rows = $pdo->query('SELECT id, code, name FROM kpi_master ORDER BY code')->fetchAll();
}
echo json_encode($rows, JSON_UNESCAPED_UNICODE);
