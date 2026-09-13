<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
redirect('/modules/projects/index.php');
