<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
$site = require __DIR__ . '/config/site.php';
$user = currentUser();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($site['company_name']) ?> — พัฒนาเว็บ แอปพลิเคชัน และระบบจัดการองค์กร</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #4F46E5;
    --primary-2: #7C3AED;
    --ink: #1E293B;
    --muted: #64748B;
    --bg: #F8F9FC;
  }
  body { font-family: 'Prompt', sans-serif; color: var(--ink); background: #fff; }
  .navbar { background: rgba(255,255,255,.9); backdrop-filter: blur(6px); }
  .navbar-brand { font-weight: 700; color: var(--ink) !important; }
  .navbar-brand span { color: var(--primary); }
  .btn-gradient {
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    color: #fff; border: none; font-weight: 600;
  }
  .btn-gradient:hover { color: #fff; opacity: .92; }
  .hero {
    background: linear-gradient(160deg, #EEF0FF 0%, #F8F9FC 60%);
    padding: 100px 0 80px;
  }
  .hero h1 { font-weight: 800; font-size: clamp(2rem, 5vw, 3.2rem); line-height: 1.25; }
  .hero .lead { color: var(--muted); font-size: 1.15rem; }
  .badge-soft {
    background: #EEF0FF; color: var(--primary); font-weight: 600;
    padding: .4rem .9rem; border-radius: 999px; font-size: .85rem;
  }
  .section-title { font-weight: 700; font-size: 2rem; }
  .section-sub { color: var(--muted); }
  .service-card, .portfolio-card {
    border: none; border-radius: 20px; box-shadow: 0 2px 16px rgba(0,0,0,.06);
    transition: transform .2s ease, box-shadow .2s ease; height: 100%;
  }
  .service-card:hover, .portfolio-card:hover {
    transform: translateY(-4px); box-shadow: 0 12px 28px rgba(79,70,229,.15);
  }
  .service-icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--primary-2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.5rem;
  }
  .portfolio-preview {
    border-radius: 14px; background: #0F172A; padding: 14px 18px;
    font-family: 'Consolas', monospace; font-size: .78rem; color: #94A3B8;
  }
  .portfolio-preview .dots span { width: 9px; height: 9px; border-radius: 50%; display: inline-block; margin-right: 5px; }
  .stats-bar { background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; }
  .stats-bar .num { font-weight: 800; font-size: 2.2rem; }
  footer { background: var(--ink); color: #CBD5E1; }
  footer a { color: #CBD5E1; text-decoration: none; }
  footer a:hover { color: #fff; }
  .contact-card {
    border-radius: 20px; background: #fff; box-shadow: 0 2px 16px rgba(0,0,0,.06);
  }
  section { scroll-margin-top: 80px; }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top py-3">
  <div class="container">
    <a class="navbar-brand" href="#top"><span><?= h(mb_substr($site['company_name'],0,1)) ?></span><?= h(mb_substr($site['company_name'],1)) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav1">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav1">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-4">
        <li class="nav-item"><a class="nav-link" href="#services">บริการ</a></li>
        <li class="nav-item"><a class="nav-link" href="#portfolio">ผลงาน</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">ติดต่อเรา</a></li>
        <li class="nav-item mt-2 mt-lg-0">
          <?php if ($user): ?>
            <a href="/modules/projects/index.php" class="btn btn-gradient btn-sm px-3">เข้าสู่ระบบ <i class="bi bi-arrow-right"></i></a>
          <?php else: ?>
            <a href="/login.php" class="btn btn-gradient btn-sm px-3">เข้าสู่ระบบ <i class="bi bi-box-arrow-in-right"></i></a>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div id="top"></div>
<section class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="badge-soft"><i class="bi bi-stars"></i> รับพัฒนาเว็บไซต์ แอปพลิเคชัน และระบบองค์กร</span>
        <h1 class="mt-3 mb-3"><?= h($site['company_name']) ?></h1>
        <p class="lead"><?= h($site['tagline']) ?></p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a href="#contact" class="btn btn-gradient btn-lg px-4">ปรึกษาโครงการของคุณ</a>
          <a href="#portfolio" class="btn btn-outline-dark btn-lg px-4">ดูผลงานของเรา</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="portfolio-preview">
          <div class="dots mb-3">
            <span style="background:#EF4444"></span><span style="background:#F59E0B"></span><span style="background:#22C55E"></span>
          </div>
          <div>&gt; วิเคราะห์ความต้องการ...</div>
          <div>&gt; ออกแบบฐานข้อมูล...</div>
          <div>&gt; พัฒนาระบบอนุมัติ 15 ขั้นตอน...</div>
          <div>&gt; เชื่อมต่อ Dashboard และ KPI...</div>
          <div style="color:#4ADE80">&gt; deploy สำเร็จ ✓</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="stats-bar py-4">
  <div class="container">
    <div class="row text-center g-3">
      <div class="col-6 col-md-3"><div class="num">100%</div><div>ใช้งานผ่านเว็บเบราว์เซอร์</div></div>
      <div class="col-6 col-md-3"><div class="num">Real-time</div><div>ติดตามสถานะได้ทันที</div></div>
      <div class="col-6 col-md-3"><div class="num">RBAC</div><div>กำหนดสิทธิ์ตามบทบาท</div></div>
      <div class="col-6 col-md-3"><div class="num">24/7</div><div>เข้าถึงได้ทุกที่ทุกเวลา</div></div>
    </div>
  </div>
</section>

<section id="services" class="py-5 py-lg-6">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="section-title">บริการของเรา</h2>
      <p class="section-sub">ครบวงจรตั้งแต่วิเคราะห์ปัญหา ออกแบบระบบ จนถึงส่งมอบและดูแลต่อเนื่อง</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3">
        <div class="card service-card p-4">
          <div class="service-icon mb-3"><i class="bi bi-window"></i></div>
          <h5>พัฒนาเว็บไซต์</h5>
          <p class="text-muted small mb-0">เว็บไซต์องค์กร เว็บแอปพลิเคชัน ดีไซน์ทันสมัย รองรับทุกอุปกรณ์</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card service-card p-4">
          <div class="service-icon mb-3"><i class="bi bi-phone"></i></div>
          <h5>พัฒนาแอปพลิเคชัน</h5>
          <p class="text-muted small mb-0">แอปมือถือและระบบหลังบ้าน เชื่อมต่อฐานข้อมูลและ API ได้ครบ</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card service-card p-4">
          <div class="service-icon mb-3"><i class="bi bi-diagram-3"></i></div>
          <h5>ระบบจัดการภายในองค์กร</h5>
          <p class="text-muted small mb-0">ระบบติดตามงาน อนุมัติเอกสาร งบประมาณ และ KPI ออกแบบตามขั้นตอนจริงขององค์กร</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="card service-card p-4">
          <div class="service-icon mb-3"><i class="bi bi-lightbulb"></i></div>
          <h5>ที่ปรึกษาด้านไอที</h5>
          <p class="text-muted small mb-0">วางแผนระบบ เลือกเทคโนโลยีที่เหมาะสม และดูแลระบบหลังส่งมอบ</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="portfolio" class="py-5 py-lg-6" style="background:var(--bg)">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="section-title">ผลงานที่ผ่านมา</h2>
      <p class="section-sub">ตัวอย่างระบบที่เราออกแบบและพัฒนาให้ใช้งานได้จริง</p>
    </div>
    <div class="row g-4 align-items-stretch">
      <div class="col-lg-6">
        <div class="card portfolio-card p-4 p-lg-5 h-100">
          <span class="badge-soft mb-3" style="width:fit-content">ระบบราชการ / สาธารณสุข</span>
          <h4 class="mb-3">ระบบติดตามโครงการ กิจกรรม งบประมาณ และ KPI</h4>
          <p class="text-muted">ระบบบริหารจัดการโครงการสำหรับหน่วยงานสาธารณสุขระดับอำเภอ ครอบคลุมตั้งแต่เสนอโครงการ อนุมัติหลายระดับ ติดตามกิจกรรมและงบประมาณ ไปจนถึงเชื่อมโยงตัวชี้วัด (KPI) ระดับประเทศ</p>
          <ul class="text-muted small">
            <li>Workflow อนุมัติ 15 ขั้นตอน พร้อมประวัติการเปลี่ยนสถานะ</li>
            <li>บริหารงบประมาณระดับกิจกรรม เทียบอนุมัติ vs เบิกจ่ายจริงแบบเรียลไทม์</li>
            <li>กำหนดสิทธิ์การเข้าถึงตามบทบาทและหน่วยงาน (RBAC)</li>
            <li>เชื่อมโยงตัวชี้วัด KPI ระดับโครงการและระดับประเทศ</li>
          </ul>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card portfolio-card p-4 p-lg-5 h-100 d-flex flex-column justify-content-center text-center">
          <i class="bi bi-kanban" style="font-size:4rem;color:var(--primary)"></i>
          <p class="text-muted mt-3 mb-0">เทคโนโลยีที่ใช้: PHP 8, MySQL, Bootstrap 5, Node.js, JavaScript<br>ออกแบบสถาปัตยกรรมและฐานข้อมูลตามความต้องการเฉพาะขององค์กร</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="contact" class="py-5 py-lg-6">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="section-title">ติดต่อเรา</h2>
      <p class="section-sub">สนใจพัฒนาระบบให้องค์กรของคุณ ทักหาเราได้เลย</p>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="contact-card p-4 p-lg-5">
          <div class="row g-4 text-center">
            <div class="col-md-4">
              <i class="bi bi-telephone-fill fs-3" style="color:var(--primary)"></i>
              <div class="fw-semibold mt-2">โทรศัพท์</div>
              <a href="tel:<?= h($site['phone']) ?>" class="text-dark"><?= h($site['phone']) ?></a>
            </div>
            <div class="col-md-4">
              <i class="bi bi-envelope-fill fs-3" style="color:var(--primary)"></i>
              <div class="fw-semibold mt-2">อีเมล</div>
              <a href="mailto:<?= h($site['email']) ?>" class="text-dark"><?= h($site['email']) ?></a>
            </div>
            <div class="col-md-4">
              <i class="bi bi-chat-dots-fill fs-3" style="color:var(--primary)"></i>
              <div class="fw-semibold mt-2">Line</div>
              <span class="text-dark"><?= h($site['line_id']) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="py-4">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
    <div>&copy; <?= date('Y') + 543 ?> <?= h($site['company_name']) ?> สงวนลิขสิทธิ์</div>
    <div class="d-flex gap-3">
      <a href="#services">บริการ</a>
      <a href="#portfolio">ผลงาน</a>
      <a href="#contact">ติดต่อเรา</a>
      <a href="/login.php">เข้าสู่ระบบ</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
