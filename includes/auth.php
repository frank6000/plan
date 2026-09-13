<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    // บาง hosting (เช่น cPanel/CloudLinux) บล็อกการเขียน session ไปที่ system default path
    // ด้วย open_basedir จึงต้องกำหนด path ของเราเองในโฟลเดอร์ที่เขียนได้แน่นอน
    $sessionPath = __DIR__ . '/../storage/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0700, true);
    }
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): array
{
    $u = currentUser();
    if (!$u) {
        redirect('/login.php');
    }
    return $u;
}

function attemptLogin(string $username, string $password): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE auth_provider = 'local' AND username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) return null;

    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        return ['error' => 'locked'];
    }

    if (!password_verify($password, $user['password_hash'])) {
        $pdo->prepare('UPDATE users SET failed_login_count = failed_login_count + 1,
            locked_until = IF(failed_login_count + 1 >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), locked_until)
            WHERE id = ?')->execute([$user['id']]);
        return null;
    }

    if ($user['status'] !== 'Approve') {
        return ['error' => 'pending'];
    }

    $pdo->prepare('UPDATE users SET failed_login_count = 0, locked_until = NULL WHERE id = ?')->execute([$user['id']]);
    unset($user['password_hash']);
    return $user;
}

// ----- สิทธิ์การใช้งาน (RBAC) ตามข้อ 6 -----

function canEditProjectContent(array $project, array $user): bool
{
    if ($user['level'] === 'admin') return true;
    return (int)$project['created_by'] === (int)$user['id'];
}

function canChangeStatus(array $project, array $user): bool
{
    if (in_array($user['level'], ['สสจ.', 'admin'], true)) return true;
    if (in_array($user['level'], ['สสอ.', 'รพ.'], true)) {
        return $user['office_id'] !== null && (int)$project['owner_office_id'] === (int)$user['office_id'];
    }
    return false;
}

function canFreeSelectStatus(array $user): bool
{
    return in_array($user['level'], ['สสจ.', 'admin'], true);
}

// เงื่อนไข SQL (WHERE) สำหรับกรองรายการโครงการตามสิทธิ์การมองเห็น — คืนค่า [sql, params]
function projectVisibilitySql(array $user): array
{
    if (in_array($user['level'], ['สสจ.', 'admin'], true)) {
        return ['1=1', []];
    }
    if (in_array($user['level'], ['สสอ.', 'รพ.'], true)) {
        return ['(p.owner_office_id = ? OR p.created_by = ?)', [$user['office_id'], $user['id']]];
    }
    // หน่วยบริการ (ค่าเริ่มต้น)
    return ['(p.owner_facility_id = ? OR p.created_by = ?)', [$user['facility_id'], $user['id']]];
}
