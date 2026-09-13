<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (currentUser()) {
    redirect('/index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่านให้ครบ';
    } else {
        $result = attemptLogin($username, $password);
        if ($result === null) {
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        } elseif (isset($result['error']) && $result['error'] === 'locked') {
            $error = 'บัญชีนี้ถูกล็อกชั่วคราวเนื่องจากลองรหัสผ่านผิดหลายครั้ง กรุณาลองใหม่ภายหลัง';
        } elseif (isset($result['error']) && $result['error'] === 'pending') {
            $error = 'บัญชีของท่านอยู่ระหว่างรอการอนุมัติจากผู้ดูแลระบบ';
        } else {
            $_SESSION['user'] = $result;
            redirect('/index.php');
        }
    }
}
$pageTitle = 'เข้าสู่ระบบ';
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center mt-5">
  <div class="col-md-4">
    <div class="card p-4">
      <h4 class="mb-3 text-center">เข้าสู่ระบบ</h4>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">ชื่อผู้ใช้ (เบอร์โทรศัพท์)</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">รหัสผ่าน</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
