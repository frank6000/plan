<?php
require_once __DIR__ . '/includes/auth.php';
$site = require __DIR__ . '/config/site.php';
$s = fn(string $key, string $default = '') => h((string)($site[$key] ?? $default));
$user = currentUser();
$loginHref = $user ? '/modules/projects/index.php' : '/login.php';
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $s('company_name') ?> — พัฒนาเว็บ แอปพลิเคชัน และระบบจัดการองค์กร</title>
<meta name="description" content="<?= $s('tagline') ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@200;300;500&family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --teal: #12B5C8;
    --teal-dark: #0A8FA0;
    --navy: #0B2545;
    --navy-2: #13406E;
    --ink: #1F2937;
    --muted: #6B7280;
    --line: #E5E7EB;
  }
  * { box-sizing: border-box; }
  body { font-family: 'Prompt', sans-serif; color: var(--ink); background: #fff; font-weight: 300; }
  .display-en { font-family: 'Exo 2', 'Prompt', sans-serif; letter-spacing: .08em; text-transform: uppercase; }

  /* ---------- Top bar ---------- */
  .topbar { background: var(--teal); color: #fff; font-size: .82rem; }
  .topbar a { color: #fff; text-decoration: none; }
  .topbar .ico {
    width: 22px; height: 22px; border-radius: 50%; background: #fff; color: var(--teal);
    display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; margin-right: 6px;
  }
  .topbar .social a {
    width: 24px; height: 24px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;
    font-size: .8rem; margin-left: 4px;
  }

  /* ---------- Header / nav ---------- */
  .site-header { background: #fff; box-shadow: 0 1px 0 var(--line); position: sticky; top: 0; z-index: 1030; }
  .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
  .brand-name { font-weight: 600; font-size: 1.35rem; color: var(--navy); line-height: 1; }
  .brand-name small { display: block; font-family: 'Exo 2', sans-serif; font-size: .62rem; letter-spacing: .28em; color: var(--teal); font-weight: 500; margin-top: 4px; }
  .site-nav .nav-link {
    font-family: 'Exo 2', 'Prompt', sans-serif; font-weight: 500; font-size: .86rem; color: #374151;
    padding: 1.6rem .9rem !important; position: relative;
  }
  .site-nav .nav-link::after {
    content: ''; position: absolute; left: .9rem; right: .9rem; bottom: 1.2rem; height: 2px;
    background: var(--teal); transform: scaleX(0); transition: transform .2s ease;
  }
  .site-nav .nav-link:hover, .site-nav .nav-link.active { color: var(--teal); }
  .site-nav .nav-link:hover::after, .site-nav .nav-link.active::after { transform: scaleX(1); }
  .btn-teal { background: var(--teal); color: #fff; border: none; border-radius: 2px; font-weight: 500; }
  .btn-teal:hover { background: var(--teal-dark); color: #fff; }
  .btn-outline-teal { border: 1px solid var(--teal); color: var(--teal); border-radius: 2px; font-weight: 500; }
  .btn-outline-teal:hover { background: var(--teal); color: #fff; }

  /* ---------- Hero ---------- */
  .hero {
    position: relative; overflow: hidden; min-height: 560px;
    background:
      radial-gradient(ellipse at 20% 30%, rgba(80,170,230,.45), transparent 55%),
      radial-gradient(ellipse at 80% 70%, rgba(18,181,200,.25), transparent 50%),
      linear-gradient(180deg, #0B2545 0%, #1B5C93 55%, #D6E6F2 100%);
  }
  #hero-canvas { position: absolute; inset: 0; width: 100%; height: 100%; }
  .hero-glow {
    position: absolute; left: -5%; top: 18%; width: 55%; height: 60%;
    background: radial-gradient(ellipse, rgba(255,255,255,.28), transparent 65%);
    filter: blur(10px); pointer-events: none;
  }
  .hex-cluster { position: absolute; right: 6%; top: 40px; width: 430px; height: 400px; }
  .hex {
    position: absolute; width: 104px; height: 120px;
    clip-path: polygon(50% 0, 100% 25%, 100% 75%, 50% 100%, 0 75%, 0 25%);
    background: rgba(255,255,255,.85);
    display: flex; align-items: center; justify-content: center;
    animation: float 6s ease-in-out infinite;
  }
  .hex > span {
    width: 94px; height: 110px;
    clip-path: polygon(50% 0, 100% 25%, 100% 75%, 50% 100%, 0 75%, 0 25%);
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem;
  }
  .hex.ghost { background: rgba(255,255,255,.35); }
  .hex.ghost > span { background: rgba(255,255,255,.08) !important; }
  .hex.lg { width: 140px; height: 162px; }
  .hex.lg > span { width: 128px; height: 150px; font-size: 2.8rem; }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

  .hero-card-wrap { position: relative; z-index: 2; margin-top: -170px; }
  .hero-card {
    background: #fff; border-top: 5px solid var(--teal); max-width: 640px; margin: 0 auto;
    padding: 2.6rem 2.8rem 2.2rem; text-align: center; box-shadow: 0 18px 50px rgba(11,37,69,.18);
  }
  .hero-card h1 { font-weight: 300; font-size: clamp(1.8rem, 4.4vw, 2.9rem); color: #111827; margin: 0; line-height: 1.1; }
  .hero-card h2 { font-weight: 300; font-size: clamp(.85rem, 1.9vw, 1.2rem); letter-spacing: .22em; color: #374151; margin: .5rem 0 1.2rem; }
  .hero-card p { color: var(--muted); font-size: .95rem; line-height: 1.8; margin-bottom: 1.4rem; }

  /* ---------- Sections ---------- */
  section { scroll-margin-top: 90px; }
  .sec-head { text-align: center; margin-bottom: 3rem; }
  .sec-head .eyebrow { color: var(--teal); font-size: .8rem; letter-spacing: .25em; font-weight: 500; }
  .sec-head h3 { font-weight: 500; font-size: 2rem; margin: .4rem 0 .6rem; color: var(--navy); }
  .sec-head .bar { width: 56px; height: 3px; background: var(--teal); margin: 0 auto; }

  .service { text-align: center; padding: 0 .5rem; }
  .service-frame {
    position: relative; width: 170px; height: 150px; margin: 0 auto 1.4rem;
  }
  .service-frame::before {
    content: ''; position: absolute; inset: 10px -10px -10px 10px; border: 2px solid var(--teal);
  }
  .service-frame .inner {
    position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 3rem; box-shadow: 0 10px 24px rgba(11,37,69,.15); transition: transform .25s ease;
  }
  .service:hover .service-frame .inner { transform: translate(-4px,-4px); }
  .service h5 { font-weight: 500; color: var(--navy); }
  .service p { color: var(--muted); font-size: .92rem; }

  .about { background: #F3F7FA; }
  .about-num { font-family: 'Exo 2', sans-serif; font-weight: 300; font-size: 2.6rem; color: var(--teal); line-height: 1; }

  .showcase { background: linear-gradient(180deg, #0B2545, #13406E); color: #E5EEF5; }
  .showcase .sec-head h3 { color: #fff; }
  .mock-window { background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.35); color: var(--ink); }
  .mock-bar { background: #E5E7EB; padding: 8px 12px; display: flex; gap: 6px; align-items: center; }
  .mock-bar i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
  .mock-bar .url { margin-left: 10px; background: #fff; border-radius: 3px; flex: 1; font-size: .7rem; color: var(--muted); padding: 2px 8px; }
  .mock-nav { background: var(--navy); color: #fff; font-size: .75rem; padding: 8px 14px; }
  .mock-body { padding: 14px; font-size: .74rem; }
  .mock-stat { border: 1px solid var(--line); border-radius: 4px; padding: 8px; }
  .mock-stat b { display: block; font-size: 1rem; color: var(--navy); font-weight: 600; }
  .mock-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid var(--line); }
  .chip { font-size: .66rem; padding: 2px 8px; border-radius: 999px; }
  .progress-thin { height: 5px; background: #E5E7EB; border-radius: 3px; overflow: hidden; width: 70px; }
  .progress-thin span { display: block; height: 100%; background: var(--teal); }
  .tech-badge {
    display: inline-block; border: 1px solid rgba(255,255,255,.35); color: #fff; padding: .3rem .8rem;
    margin: 0 .35rem .5rem 0; font-size: .82rem; border-radius: 2px;
  }
  .showcase ul li { margin-bottom: .45rem; }
  .showcase ul li i { color: var(--teal); margin-right: .4rem; }

  .contact-band { background: var(--teal); color: #fff; }
  .contact-item .ico {
    width: 56px; height: 56px; border-radius: 50%; border: 2px solid rgba(255,255,255,.7);
    display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: .7rem;
  }
  .contact-item a, .contact-item span.val { color: #fff; text-decoration: none; font-weight: 500; }

  footer { background: #0A1D35; color: #9FB3C8; font-size: .88rem; }
  footer a { color: #9FB3C8; text-decoration: none; }
  footer a:hover { color: #fff; }

  @media (max-width: 991.98px) {
    .site-nav .nav-link { padding: .6rem 0 !important; }
    .site-nav .nav-link::after { display: none; }
    .hex-cluster { transform: scale(.7); transform-origin: top right; right: 0; opacity: .55; }
  }
  @media (max-width: 575.98px) {
    .hero { min-height: 420px; }
    .hex-cluster { display: none; }
    .hero-card-wrap { margin-top: -120px; }
    .hero-card { padding: 2rem 1.3rem 1.6rem; }
  }
  @media (prefers-reduced-motion: reduce) {
    .hex { animation: none; }
  }
</style>
</head>
<body>

<!-- Top bar -->
<div class="topbar py-2">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex flex-wrap gap-3">
      <a href="tel:<?= $s('phone') ?>"><span class="ico"><i class="bi bi-telephone-fill"></i></span><?= $s('phone') ?></a>
      <a href="mailto:<?= $s('email') ?>" class="d-none d-sm-inline"><span class="ico"><i class="bi bi-envelope-fill"></i></span><?= $s('email') ?></a>
    </div>
    <div class="social">
      <?php if (!empty($site['facebook_url'])): ?>
        <a href="<?= $s('facebook_url') ?>" target="_blank" rel="noopener" style="background:#3B5998" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
      <?php endif; ?>
      <a href="<?= !empty($site['line_url']) ? $s('line_url') : '#contact' ?>" <?= !empty($site['line_url']) ? 'target="_blank" rel="noopener"' : '' ?> style="background:#06C755" aria-label="Line"><i class="bi bi-line"></i></a>
    </div>
  </div>
</div>

<!-- Header -->
<header class="site-header">
  <nav class="navbar navbar-expand-lg py-0">
    <div class="container">
      <a class="brand py-2" href="#top">
        <svg width="42" height="46" viewBox="0 0 42 46" aria-hidden="true">
          <defs><linearGradient id="lg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#12B5C8"/><stop offset="1" stop-color="#13406E"/></linearGradient></defs>
          <polygon points="21,1 41,12 41,34 21,45 1,34 1,12" fill="url(#lg)"/>
          <path d="M14 23h14M21 16v14" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
        </svg>
        <span class="brand-name"><?= $s('company_name') ?><small><?= $s('company_name_en') ?></small></span>
      </a>
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-label="เมนู">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse site-nav" id="siteNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link active" href="#top">หน้าแรก</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">เกี่ยวกับเรา</a></li>
          <li class="nav-item"><a class="nav-link" href="#services">บริการ</a></li>
          <li class="nav-item"><a class="nav-link" href="#showcase">ผลงาน</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">ติดต่อเรา</a></li>
          <li class="nav-item ms-lg-3 my-2 my-lg-0">
            <a href="<?= $loginHref ?>" class="btn btn-teal btn-sm px-3"><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>

<!-- Hero -->
<div id="top"></div>
<section class="hero">
  <canvas id="hero-canvas" aria-hidden="true"></canvas>
  <div class="hero-glow"></div>
  <div class="hex-cluster d-none d-md-block" aria-hidden="true">
    <div class="hex lg" style="left:150px;top:0;animation-delay:0s"><span style="background:linear-gradient(135deg,#1B7FC4,#0B2545)"><i class="bi bi-kanban"></i></span></div>
    <div class="hex" style="left:40px;top:40px;animation-delay:.8s"><span style="background:linear-gradient(135deg,#12B5C8,#0A6C8A)"><i class="bi bi-phone"></i></span></div>
    <div class="hex ghost" style="left:300px;top:40px;animation-delay:1.6s"><span></span></div>
    <div class="hex" style="left:300px;top:150px;animation-delay:.4s"><span style="background:linear-gradient(135deg,#3AA0E0,#13406E)"><i class="bi bi-graph-up-arrow"></i></span></div>
    <div class="hex lg" style="left:95px;top:150px;animation-delay:1.2s"><span style="background:linear-gradient(135deg,#12B5C8,#13406E)"><i class="bi bi-database-gear"></i></span></div>
    <div class="hex ghost" style="left:210px;top:240px;animation-delay:2s"><span></span></div>
    <div class="hex" style="left:250px;top:280px;animation-delay:.2s"><span style="background:linear-gradient(135deg,#1B7FC4,#0B2545)"><i class="bi bi-shield-check"></i></span></div>
  </div>
</section>

<div class="container hero-card-wrap">
  <div class="hero-card">
    <h1 class="display-en"><?= $s('hero_title') ?></h1>
    <h2 class="display-en"><?= $s('hero_subtitle') ?></h2>
    <p><?= $s('company_name') ?> — <?= $s('tagline') ?> รับพัฒนาเว็บไซต์ แอปพลิเคชัน และระบบบริหารจัดการภายในองค์กร ออกแบบตามขั้นตอนการทำงานจริงของคุณ</p>
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a href="#contact" class="btn btn-teal px-4 py-2">ปรึกษาโครงการ</a>
      <a href="#showcase" class="btn btn-outline-teal px-4 py-2">ดูผลงาน</a>
    </div>
  </div>
</div>

<!-- Services -->
<section id="services" class="py-5">
  <div class="container py-lg-4">
    <div class="sec-head">
      <div class="eyebrow display-en">Service Solution</div>
      <h3>บริการของเรา</h3>
      <div class="bar"></div>
    </div>
    <div class="row g-4 g-lg-5">
      <div class="col-sm-6 col-lg-3 service">
        <div class="service-frame"><div class="inner" style="background:linear-gradient(135deg,#12B5C8,#13406E)"><i class="bi bi-window-stack"></i></div></div>
        <h5>พัฒนาเว็บไซต์</h5>
        <p>เว็บไซต์องค์กรและเว็บแอปพลิเคชัน ดีไซน์ทันสมัย รองรับทุกอุปกรณ์</p>
      </div>
      <div class="col-sm-6 col-lg-3 service">
        <div class="service-frame"><div class="inner" style="background:linear-gradient(135deg,#3AA0E0,#0B2545)"><i class="bi bi-phone"></i></div></div>
        <h5>พัฒนาแอปพลิเคชัน</h5>
        <p>แอปมือถือและระบบหลังบ้าน เชื่อมต่อฐานข้อมูลและ API ได้ครบ</p>
      </div>
      <div class="col-sm-6 col-lg-3 service">
        <div class="service-frame"><div class="inner" style="background:linear-gradient(135deg,#0A8FA0,#13406E)"><i class="bi bi-diagram-3"></i></div></div>
        <h5>ระบบจัดการภายในองค์กร</h5>
        <p>ติดตามงาน อนุมัติเอกสาร งบประมาณ และ KPI ตามขั้นตอนจริงขององค์กร</p>
      </div>
      <div class="col-sm-6 col-lg-3 service">
        <div class="service-frame"><div class="inner" style="background:linear-gradient(135deg,#1B7FC4,#0A1D35)"><i class="bi bi-lightbulb"></i></div></div>
        <h5>ที่ปรึกษาด้านไอที</h5>
        <p>วางแผนระบบ เลือกเทคโนโลยีที่เหมาะสม และดูแลหลังส่งมอบ</p>
      </div>
    </div>
  </div>
</section>

<!-- About -->
<section id="about" class="about py-5">
  <div class="container py-lg-4">
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        <div class="sec-head text-lg-start mb-4">
          <div class="eyebrow display-en">About Us</div>
          <h3>วินิจฉัยก่อน แล้วค่อยพัฒนา</h3>
          <div class="bar ms-lg-0"></div>
        </div>
        <p class="text-muted" style="line-height:1.9">เราทำงานแบบ "คลินิก" — เริ่มจากทำความเข้าใจปัญหาและขั้นตอนการทำงานจริงขององค์กร ออกแบบฐานข้อมูลและสถาปัตยกรรมให้เหมาะกับงาน แล้วจึงพัฒนาระบบที่ใช้งานได้จริง ดูแลต่อได้ และเติบโตไปพร้อมกับองค์กรของคุณ</p>
      </div>
      <div class="col-lg-6">
        <div class="row text-center g-4">
          <div class="col-6"><div class="about-num">01</div><div class="mt-2 fw-medium">วิเคราะห์ความต้องการ</div></div>
          <div class="col-6"><div class="about-num">02</div><div class="mt-2 fw-medium">ออกแบบระบบ</div></div>
          <div class="col-6"><div class="about-num">03</div><div class="mt-2 fw-medium">พัฒนาและทดสอบ</div></div>
          <div class="col-6"><div class="about-num">04</div><div class="mt-2 fw-medium">ส่งมอบและดูแล</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Showcase -->
<section id="showcase" class="showcase py-5">
  <div class="container py-lg-4">
    <div class="sec-head">
      <div class="eyebrow display-en">Showcase</div>
      <h3>ผลงานที่ผ่านมา</h3>
      <div class="bar"></div>
    </div>
    <div class="row align-items-center g-4 g-lg-5">
      <div class="col-lg-6">
        <div class="mock-window" aria-hidden="true">
          <div class="mock-bar"><i style="background:#EF4444"></i><i style="background:#F59E0B"></i><i style="background:#22C55E"></i><span class="url">projects / dashboard</span></div>
          <div class="mock-nav d-flex justify-content-between"><span>ระบบติดตามโครงการ</span><span>admin</span></div>
          <div class="mock-body">
            <div class="row g-2 mb-3">
              <div class="col-4"><div class="mock-stat">โครงการ<b>117</b></div></div>
              <div class="col-4"><div class="mock-stat">กิจกรรม<b>302</b></div></div>
              <div class="col-4"><div class="mock-stat">งบประมาณ<b>6.92M</b></div></div>
            </div>
            <div class="mock-row"><span>ส่งเสริมพัฒนาการเด็กปฐมวัย</span><span class="chip" style="background:#DCFCE7;color:#166534">อนุมัติแล้ว</span><div class="progress-thin"><span style="width:80%"></span></div></div>
            <div class="mock-row"><span>ป้องกันโรคไม่ติดต่อในชุมชน</span><span class="chip" style="background:#FEF3C7;color:#92400E">รอตรวจสอบ</span><div class="progress-thin"><span style="width:45%"></span></div></div>
            <div class="mock-row"><span>ชุมชนปลอดไข้เลือดออก</span><span class="chip" style="background:#DBEAFE;color:#1E40AF">ส่ง สสจ.</span><div class="progress-thin"><span style="width:60%"></span></div></div>
            <div class="mock-row" style="border:0"><span>พัฒนาศักยภาพ อสม.</span><span class="chip" style="background:#DCFCE7;color:#166534">เสร็จสิ้น</span><div class="progress-thin"><span style="width:100%"></span></div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="eyebrow display-en mb-2" style="color:var(--teal);font-size:.78rem;letter-spacing:.2em">Government / Public Health</div>
        <h4 class="fw-medium text-white mb-3">ระบบติดตามโครงการ กิจกรรม งบประมาณ และ KPI</h4>
        <p style="line-height:1.9">ระบบบริหารจัดการโครงการสำหรับหน่วยงานสาธารณสุขระดับอำเภอ ตั้งแต่เสนอโครงการ อนุมัติหลายระดับ ติดตามกิจกรรมและงบประมาณ ไปจนถึงเชื่อมโยงตัวชี้วัดระดับประเทศ</p>
        <ul class="list-unstyled mb-4">
          <li><i class="bi bi-check2-circle"></i>Workflow อนุมัติ 15 ขั้นตอน พร้อมประวัติการเปลี่ยนสถานะ</li>
          <li><i class="bi bi-check2-circle"></i>งบประมาณระดับกิจกรรม เทียบอนุมัติกับเบิกจ่ายจริง</li>
          <li><i class="bi bi-check2-circle"></i>กำหนดสิทธิ์ตามบทบาทและหน่วยงาน (RBAC)</li>
          <li><i class="bi bi-check2-circle"></i>เชื่อมโยง KPI ระดับโครงการและระดับประเทศ</li>
        </ul>
        <div>
          <span class="tech-badge">PHP 8</span><span class="tech-badge">MySQL</span><span class="tech-badge">Bootstrap 5</span><span class="tech-badge">Node.js</span><span class="tech-badge">JavaScript</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact -->
<section id="contact" class="contact-band py-5">
  <div class="container py-lg-3">
    <div class="sec-head mb-4">
      <div class="eyebrow display-en" style="color:#fff;opacity:.85">Contact Us</div>
      <h3 style="color:#fff">สนใจพัฒนาระบบให้องค์กรของคุณ</h3>
      <div class="bar" style="background:#fff"></div>
    </div>
    <div class="row text-center g-4 justify-content-center">
      <div class="col-sm-4 contact-item">
        <div class="ico"><i class="bi bi-telephone"></i></div>
        <div class="small opacity-75">โทรศัพท์</div>
        <a href="tel:<?= $s('phone') ?>"><?= $s('phone') ?></a>
      </div>
      <div class="col-sm-4 contact-item">
        <div class="ico"><i class="bi bi-envelope"></i></div>
        <div class="small opacity-75">อีเมล</div>
        <a href="mailto:<?= $s('email') ?>"><?= $s('email') ?></a>
      </div>
      <div class="col-sm-4 contact-item">
        <div class="ico"><i class="bi bi-line"></i></div>
        <div class="small opacity-75">Line</div>
        <?php if (!empty($site['line_url'])): ?>
          <a href="<?= $s('line_url') ?>" target="_blank" rel="noopener"><?= $s('line_id') ?></a>
        <?php else: ?>
          <span class="val"><?= $s('line_id') ?></span>
        <?php endif; ?>
      </div>
    </div>
    <?php if (!empty($site['address'])): ?>
      <p class="text-center mt-4 mb-0"><i class="bi bi-geo-alt"></i> <?= $s('address') ?></p>
    <?php endif; ?>
  </div>
</section>

<footer class="py-4">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
    <div>&copy; <?= date('Y') + 543 ?> <?= $s('company_name') ?> สงวนลิขสิทธิ์</div>
    <div class="d-flex flex-wrap gap-3">
      <a href="#services">บริการ</a><a href="#showcase">ผลงาน</a><a href="#contact">ติดต่อเรา</a><a href="<?= $loginHref ?>">เข้าสู่ระบบ</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  const canvas = document.getElementById('hero-canvas');
  const ctx = canvas.getContext('2d');
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let w, h, points;

  function resize() {
    const dpr = window.devicePixelRatio || 1;
    w = canvas.clientWidth; h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    const count = Math.min(90, Math.floor(w * h / 12000));
    points = Array.from({ length: count }, () => ({
      x: Math.random() * w, y: Math.random() * h * 0.85,
      vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35,
      r: Math.random() * 1.8 + .6
    }));
  }

  function draw() {
    ctx.clearRect(0, 0, w, h);
    for (const p of points) {
      if (!reduce) {
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > w) p.vx *= -1;
        if (p.y < 0 || p.y > h * 0.85) p.vy *= -1;
      }
    }
    for (let i = 0; i < points.length; i++) {
      for (let j = i + 1; j < points.length; j++) {
        const a = points[i], b = points[j];
        const d = Math.hypot(a.x - b.x, a.y - b.y);
        if (d < 120) {
          ctx.strokeStyle = 'rgba(160,220,255,' + (0.28 * (1 - d / 120)) + ')';
          ctx.lineWidth = 1;
          ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
        }
      }
    }
    for (const p of points) {
      ctx.fillStyle = 'rgba(200,240,255,.85)';
      ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill();
    }
    if (!reduce) requestAnimationFrame(draw);
  }

  resize(); draw();
  window.addEventListener('resize', () => { resize(); if (reduce) draw(); });

  // ไฮไลต์เมนูตาม section ที่กำลังดู
  const links = document.querySelectorAll('.site-nav .nav-link[href^="#"]');
  const targets = [
    [document.querySelector('.hero'), '#top'],
    [document.getElementById('services'), '#services'],
    [document.getElementById('about'), '#about'],
    [document.getElementById('showcase'), '#showcase'],
    [document.getElementById('contact'), '#contact'],
  ];
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const href = targets.find(t => t[0] === e.target)[1];
      links.forEach(l => l.classList.toggle('active', l.getAttribute('href') === href));
    });
  }, { rootMargin: '-45% 0px -50% 0px' });
  targets.forEach(t => obs.observe(t[0]));
})();
</script>
</body>
</html>
