<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
$user = currentUser();
$pageTitle = $pageTitle ?? 'ระบบติดตามโครงการ';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($pageTitle) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php if ($user): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php">ระบบติดตามโครงการ กิจกรรม งบประมาณ และ KPI</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav1">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav1">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/modules/projects/index.php"><i class="bi bi-folder"></i> โครงการ</a></li>
      </ul>
      <span class="navbar-text text-white me-3">
        <?= h($user['name']) ?> <span class="badge bg-light text-dark"><?= h($user['level'] ?? '-') ?></span>
      </span>
      <a href="/logout.php" class="btn btn-outline-light btn-sm">ออกจากระบบ</a>
    </div>
  </div>
</nav>
<?php endif; ?>
<div class="container-fluid px-4">
<?php foreach (get_flashes() as $f): ?>
  <div class="alert alert-<?= h($f['type']) ?> alert-dismissible fade show" role="alert">
    <?= h($f['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endforeach; ?>
