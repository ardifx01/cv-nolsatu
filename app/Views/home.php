<?php helper('settings'); ?>

<!doctype html>
<?php $activeLang = session('lang') ?? setting('site.lang','id'); ?>
<html lang="<?= esc($activeLang) ?>" data-bs-theme="<?= esc(setting('site.theme','light')) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

<?php
  // ====== Title & Meta ======
  $siteTitle  = setting('site.title','Imigrasi Jambi — Pelayanan Keimigrasian');
  $pageTitle  = trim($this->renderSection('title') ?? '');
  $fullTitle  = $pageTitle ? ($pageTitle.' — '.$siteTitle) : $siteTitle;

  $metaDesc   = trim($this->renderSection('meta_desc') ?? '')
                ?: setting('site.meta_description','Layanan resmi Imigrasi Jambi: paspor, izin tinggal, informasi WNA/WNI, pengaduan, dan status permohonan.');
  $metaRobots = setting('site.meta_robots','index,follow');

  // Helper URL absolut (sekali saja di layout)
  $toAbs = function (?string $u): string {
    $u = trim((string)$u);
    if ($u === '') return '';
    if (preg_match('#^(https?:)?//#', $u) || str_starts_with($u, 'data:')) return $u;
    return base_url(ltrim($u,'/'));
  };

  $ogImageRaw = trim($this->renderSection('meta_image') ?? '');
  $ogImage    = $ogImageRaw !== '' ? $toAbs($ogImageRaw)
                                   : $toAbs(setting('site.og_image', 'assets/img/og-default.jpg'));
?>

<title><?= esc($fullTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>" />
<meta name="robots" content="<?= esc($metaRobots) ?>">

<!-- OpenGraph / Twitter -->
<meta property="og:title" content="<?= esc($fullTitle) ?>">
<meta property="og:description" content="<?= esc($metaDesc) ?>">
<meta property="og:type" content="<?= $this->renderSection('og_type') ?: 'website' ?>">
<meta property="og:url" content="<?= current_url() ?>">
<meta property="og:image" content="<?= esc($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="<?= esc($ogImage) ?>">

<link rel="preconnect" href="https://cdn.jsdelivr.net"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

<!-- Inisialisasi tema paling awal: hormati localStorage, fallback ke setting('site.theme') -->
<script>
(function(){
  try{
    var pref   = localStorage.getItem('theme') || "<?= esc(setting('site.theme','light')) ?>";
    var isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (pref === 'auto') {
      document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    } else {
      document.documentElement.setAttribute('data-bs-theme', pref);
    }
  }catch(e){}
})();
</script>

<?php
// ====== THEME VARS from settings ======
$hex2rgb = function(string $hex): string {
  $hex = trim($hex);
  if (preg_match('/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i', $hex) !== 1) return '13,110,253';
  $hex = ltrim($hex, '#');
  if (strlen($hex) === 3) {
    $r = hexdec(str_repeat($hex[0],2));
    $g = hexdec(str_repeat($hex[1],2));
    $b = hexdec(str_repeat($hex[2],2));
  } else {
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
  }
  return "{$r},{$g},{$b}";
};

$primary   = setting('theme.primary',   '#0d6efd');
$success   = setting('theme.success',   '#198754');
$secondary = setting('theme.secondary', '#6c757d');
$info      = setting('theme.info',      '#0dcaf0');
$warning   = setting('theme.warning',   '#ffc107');
$danger    = setting('theme.danger',    '#dc3545');
$light     = setting('theme.light',     '#f8f9fa');
$dark      = setting('theme.dark',      '#212529');

$radius2xl    = setting('theme.radius_2xl', '1.25rem');
$shadowSoft   = setting('theme.shadow_soft', '0 .5rem 1.25rem rgba(0,0,0,.06)');
$shadowHover  = setting('theme.shadow_hover','0 1rem 2rem rgba(0,0,0,.12)');

$darkPrimary  = setting('theme.dark.primary', $primary);

$primaryRgb     = $hex2rgb($primary);
$successRgb     = $hex2rgb($success);
$secondaryRgb   = $hex2rgb($secondary);
$infoRgb        = $hex2rgb($info);
$warningRgb     = $hex2rgb($warning);
$dangerRgb      = $hex2rgb($danger);
$lightRgb       = $hex2rgb($light);
$darkRgb        = $hex2rgb($dark);
$darkPrimaryRgb = $hex2rgb($darkPrimary);
?>

<meta name="theme-color" content="<?= esc($primary) ?>" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="<?= esc($darkPrimary) ?>" media="(prefers-color-scheme: dark)">

<style>
/* ====== THEME VARIABLES FROM SETTINGS ====== */
:root{
  --bs-primary:   <?= esc($primary) ?>;
  --bs-secondary: <?= esc($secondary) ?>;
  --bs-success:   <?= esc($success) ?>;
  --bs-info:      <?= esc($info) ?>;
  --bs-warning:   <?= esc($warning) ?>;
  --bs-danger:    <?= esc($danger) ?>;
  --bs-light:     <?= esc($light) ?>;
  --bs-dark:      <?= esc($dark) ?>;

  --bs-primary-rgb:   <?= esc($primaryRgb) ?>;
  --bs-secondary-rgb: <?= esc($secondaryRgb) ?>;
  --bs-success-rgb:   <?= esc($successRgb) ?>;
  --bs-info-rgb:      <?= esc($infoRgb) ?>;
  --bs-warning-rgb:   <?= esc($warningRgb) ?>;
  --bs-danger-rgb:    <?= esc($dangerRgb) ?>;
  --bs-light-rgb:     <?= esc($lightRgb) ?>;
  --bs-dark-rgb:      <?= esc($darkRgb) ?>;

  --bs-link-color: var(--bs-primary);
  --bs-link-hover-color: color-mix(in srgb, var(--bs-primary), #000 20%);

  /* Tokens kustom */
  --brand: var(--bs-primary);
  --accent: var(--bs-success);
  --radius-2xl: <?= esc($radius2xl) ?>;
  --shadow-soft: <?= esc($shadowSoft) ?>;
  --shadow-hover: <?= esc($shadowHover) ?>;

  /* Subtle/emphasis opsional */
  --bs-primary-bg-subtle: color-mix(in srgb, var(--bs-primary), #fff 88%);
  --bs-primary-border-subtle: color-mix(in srgb, var(--bs-primary), #000 82%);
  --bs-primary-text-emphasis: color-mix(in srgb, var(--bs-primary), #000 30%);
}
[data-bs-theme="dark"]{
  --bs-primary: <?= esc($darkPrimary) ?>;
  --bs-primary-rgb: <?= esc($darkPrimaryRgb) ?>;
  --bs-link-color: var(--bs-primary);
  --bs-link-hover-color: color-mix(in srgb, var(--bs-primary), #fff 20%);
  --bs-primary-bg-subtle: color-mix(in srgb, var(--bs-primary), #000 85%);
  --bs-primary-border-subtle: color-mix(in srgb, var(--bs-primary), #fff 75%);
  --bs-primary-text-emphasis: color-mix(in srgb, var(--bs-primary), #fff 35%);
}

/* =========================================================
   GLOBAL & ACCESSIBILITY
   ========================================================= */
html { scroll-behavior: smooth; }
img { max-width:100%; height:auto; }
@media (prefers-reduced-motion: reduce) {
  *,*::before,*::after{
    animation-duration:.001ms !important;
    animation-iteration-count:1 !important;
    transition-duration:.001ms !important;
    scroll-behavior:auto !important;
  }
}

/* =========================================================
   DARK MODE EXCEPTIONS
   ========================================================= */
/* Navbar brand tetap hitam di mode gelap bila diperlukan */
[data-bs-theme="dark"] .navbar{
  --bs-navbar-brand-color:#000;
  --bs-navbar-brand-hover-color:#000;
}

/* Topbar selalu terang meski dark mode aktif */
.topbar{
  font-size:.9rem;
  border-bottom:1px solid rgba(0,0,0,.06);
  color: var(--bs-body-color);
}
.topbar a{ color:inherit; text-decoration:none; }
[data-bs-theme="dark"] .topbar{
  background-color:#fff;
  border-bottom-color: rgba(0,0,0,.08);
  --bs-body-color:#000;
  --bs-link-color:#000;
  --bs-link-hover-color: var(--brand);
}
[data-bs-theme="dark"] .topbar a,
[data-bs-theme="dark"] .topbar i{ color:#000; }
[data-bs-theme="dark"] .topbar a:hover{ color:var(--brand); }

/* =========================================================
   NAVBAR
   ========================================================= */
.navbar-brand img{ height:44px; }
.nav-link{ font-weight:500; }
.nav-link.active{ color:var(--brand) !important; }

/* =========================================================
   HERO
   ========================================================= */
.hero{ position:relative; min-height:62vh; display:grid; place-items:center; }
.hero::after{ content:""; position:absolute; inset:0; background:rgba(0,0,0,.25); }
.hero .hero-inner{ position:relative; z-index:2; }

/* Glass card */
.glass{
  backdrop-filter:saturate(180%) blur(10px);
  -webkit-backdrop-filter:saturate(180%) blur(10px);
  background:rgba(255,255,255,.75);
}

/* =========================================================
   QUICK ACTIONS
   ========================================================= */
.quick-actions .btn{ border-radius:1rem; }

/* =========================================================
   CARDS & UTILITIES
   ========================================================= */
.shadow-soft{ box-shadow: var(--shadow-soft); }
.rounded-2xl{ border-radius: var(--radius-2xl); }

.card-service{ transition: transform .2s ease, box-shadow .2s ease; }
.card-service:hover{ transform: translateY(-4px); box-shadow: var(--shadow-hover); }

/* Feature card */
.feature-card{
  border:0; border-radius:1rem;
  background:rgba(255,255,255,.7);
  backdrop-filter:saturate(160%) blur(8px);
  -webkit-backdrop-filter:saturate(160%) blur(8px);
  box-shadow: var(--shadow-soft);
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.feature-card:hover{
  transform: translateY(-4px);
  box-shadow: var(--shadow-hover);
  background: rgba(255,255,255,.8);
}
[data-bs-theme="dark"] .feature-card{
  background: rgba(22,22,26,.6);
  box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.25);
}
[data-bs-theme="dark"] .feature-card:hover{
  background: rgba(38,38,44,.7);
}
.feature-top{ display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
.feature-badge{
  display:inline-grid; place-items:center;
  width:40px; height:40px; border-radius:50%;
  background: var(--bs-primary-bg-subtle, rgba(13,110,253,.1));
  color: var(--bs-primary, #0d6efd);
  font-size:1.1rem;
}
.arrow-corner{ opacity:.4; font-size:1.25rem; transition: transform .18s ease, opacity .18s ease; }
.feature-card:hover .arrow-corner{ transform: translate(2px,-2px); opacity:.7; }
.feature-desc{ margin:0; font-size:.9rem; color: var(--bs-secondary-color, #6c757d); }

/* =========================================================
   SIDEBAR / OFFCANVAS / DROPDOWN
   ========================================================= */
.dropdown-menu{ z-index:1050; }
.sidebar-nav .nav-link{ color: var(--bs-body-color); }
.sidebar-nav .nav-link:hover{ background: var(--bs-light); }
.sidebar-nav .toggle-icon{ transition: transform .2s ease; }
.sidebar-nav .collapse.show ~ .toggle-icon,
.sidebar-nav .nav-link[aria-expanded="true"] .toggle-icon{ transform: rotate(180deg); }
.offcanvas{ width:320px; } /* lebar sidebar */

/* =========================================================
   APP BANNERS / DOWNLOADS
   ========================================================= */
.app_img{ max-width:420px; }
.app_download{ max-width:180px; height:auto; }
.unduh_desc{ color: var(--bs-secondary-color, #6c757d); }
@media (max-width:576px){
  .app_img{ max-width:320px; }
  .app_download{ max-width:160px; }
  #layanan-online .row{ row-gap:.75rem; }
}

/* =========================================================
   TICKER ANNOUNCEMENT
   ========================================================= */
.ticker{ white-space:nowrap; overflow:hidden; }
.ticker span{
  display:inline-block; padding-left:100%;
  animation: ticker 20s linear infinite;
  will-change: transform;
}
@keyframes ticker{
  from{ transform: translate3d(0,0,0); }
  to{ transform: translate3d(-100%,0,0); }
}

/* =========================================================
   FOOTER & MISC
   ========================================================= */
footer a{ text-decoration:none; }
#toTop{ position:fixed; right:1rem; bottom:1rem; display:none; z-index:1030; }
.section-title{ font-weight:800; letter-spacing:.2px; }

/* Tombol tema ikut warna brand */
.btn-theme { border-color: var(--bs-primary); color: var(--bs-primary); }
.btn-theme:hover { background: var(--bs-primary); color: #fff; }

/* Sticky wrapper untuk navbar */
.nav-sticky{
  position: -webkit-sticky; /* Safari */
  position: sticky;
  top: 0;
  z-index: 1025; /* di atas konten, di bawah modal/offcanvas */
  background: var(--bs-body-bg, #fff);
}

/* Pastikan tidak ada ancestor yang memutus sticky */
.site-header,
.site-header * {
  overflow: visible !important;
}

/* Kadang library (AOS/animasi) menyuntik transform di header: matikan */
.site-header{
  transform: none !important;
  filter: none !important;
  will-change: auto !important;
}

/* (Opsional) Hindari horizontal scroll yang bisa ganggu sticky di mobile */
html, body { overflow-x: clip; }

</style>





</head>

<body>
<?php helper('settings'); ?>

<!-- =========================
     HEADER (Topbar saja)
     ========================= -->
<header class="site-header">
  <!-- Topbar (ikut scroll, TIDAK sticky) -->
  <div class="topbar py-1 bg-light">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-3 small">
        <a href="tel:<?= esc(setting('org.phone','08117431888')) ?>" class="text-body" aria-label="Telepon">
          <i class="fa-solid fa-phone me-1" aria-hidden="true"></i>
          <?= esc(setting('org.phone','08117431888')) ?>
        </a>
        <a href="mailto:<?= esc(setting('org.email','knm.jambi@kemenkumham.go.id')) ?>" class="text-body" aria-label="Email">
          <i class="fa-solid fa-envelope me-1" aria-hidden="true"></i>
          <?= esc(setting('org.email','knm.jambi@kemenkumham.go.id')) ?>
        </a>
      </div>

      <div class="d-flex align-items-center gap-2">
        <!-- Theme toggle -->
        <button id="themeToggle" class="btn btn-sm btn-outline-primary btn-theme" type="button" aria-label="Ganti mode tema">
          <i id="themeIcon" class="fa-regular fa-moon me-1" aria-hidden="true"></i>
          <span id="themeLabel">Mode</span>
        </button>

        <!-- Lang dropdown -->
        <div class="dropdown">
          <?php $lang = setting('site.lang','id'); ?>
         <button class="btn btn-sm btn-outline-primary btn-theme dropdown-toggle" data-bs-toggle="dropdown">
  <i class="fa-solid fa-language me-1"></i> <?= strtoupper(esc($activeLang)) ?>
</button>

<ul class="dropdown-menu dropdown-menu-end shadow">
  <li><a class="dropdown-item <?= $activeLang==='id'?'active':'' ?>" href="?lang=id" hreflang="id">Bahasa Indonesia</a></li>
  <li><a class="dropdown-item <?= $activeLang==='en'?'active':'' ?>" href="?lang=en" hreflang="en">English</a></li>
</ul>

        </div>
      </div>
    </div>
  </div>
</header>

<!-- =========================
     NAVBAR (di luar header) — STICKY
     ========================= -->
<div class="nav-sticky sticky-top"><!-- biarkan wrapper yang sticky -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm"><!-- HAPUS class sticky-top di sini -->
    <div class="container align-items-center">
      <!-- Sidebar toggle -->
      <button class="btn btn-outline-secondary me-2"
              type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#sidebarOffcanvas"
              aria-controls="sidebarOffcanvas"
              aria-label="Buka menu">
        <i class="bi bi-list" aria-hidden="true"></i>
      </button>

      <!-- Brand -->
      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('/') ?>">
        <img src="<?= esc(setting('site.logo_url', base_url('assets/img/logo_header_2025.webp'))) ?>"
             alt="Logo"
             height="36" width="auto" decoding="async">
        <span class="lh-sm">
          <strong class="d-none d-sm-inline">
            <?= esc(setting('site.title_short','Kementerian Imigrasi dan Pemasyarakatan')) ?>
          </strong>
          <strong class="d-inline d-sm-none">
            <?= esc(setting('site.title_mobile','Imigrasi Kelas 1 TPI Jambi')) ?>
          </strong><br>
          <small class="text-secondary d-none d-sm-inline">
            <?= esc(setting('site.subtitle','Kantor Imigrasi Kelas 1 TPI Jambi')) ?>
          </small>
        </span>
      </a>

      <!-- Search -->
      <div class="flex-grow-1">
        <form class="d-flex ms-lg-auto mt-2 mt-lg-0" role="search" method="get" action="<?= site_url('search') ?>" aria-label="Pencarian situs">
          <input class="form-control" type="search" name="q"
                 placeholder="Cari layanan, berita, atau persyaratan..." aria-label="Kata kunci"
                 value="<?= esc(service('request')->getGet('q')) ?>">
          <button class="btn btn-primary ms-2" type="submit">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="d-none d-sm-inline ms-1">Cari</span>
          </button>
        </form>
      </div>
    </div>
  </nav>
</div>

<!-- Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarLabel">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
  </div>
  <div class="offcanvas-body p-0">
    <nav class="py-2">
      <?= $menuHtml ?? '' ?>
    </nav>
  </div>
</div>

<!-- Announcement Ticker -->
<div class="bg-primary text-white py-2">
  <div class="container ticker small">
    <span>
      <?php if (!empty($ann)): ?>
        <?php foreach ($ann as $i => $a): ?>
          <?php if ($i>0): ?> &bull; <?php endif; ?>
          <?php if (!empty($a['url'])): ?>
            <a class="text-white text-decoration-underline" href="<?= esc($a['url']) ?>"><?= esc($a['text']) ?></a>
          <?php else: ?>
            <?= esc($a['text']) ?>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
        Selamat datang di portal resmi Imigrasi Jambi.
      <?php endif; ?>
    </span>
  </div>
</div>

<?php
// Settings Hero
$heroBg     = setting('hero.bg_image', '/assets/img/hero-default.jpg');
$ov1        = setting('hero.overlay_start', 'rgba(13,110,253,.10)');
$ov2        = setting('hero.overlay_end',   'rgba(25,135,84,.08)');
$minVH      = (int) setting('hero.min_height_vh', 62);

$heroTitle  = setting('hero.title', 'Pelayanan Keimigrasian Cepat, Transparan, dan Humanis');
$heroSub    = setting('hero.subtitle', 'Portal resmi Imigrasi Jambi. Ajukan paspor, cek status permohonan, baca informasi persyaratan, dan sampaikan pengaduan secara daring.');

$btn1Show   = (int) setting('hero.btn1.show', 1);
$btn1Text   = setting('hero.btn1.text', 'Ajukan Paspor');
$btn1Href   = setting('hero.btn1.href', '#layanan-online');
$btn1Var    = setting('hero.btn1.variant', 'primary');

$btn2Show   = (int) setting('hero.btn2.show', 1);
$btn2Text   = setting('hero.btn2.text', 'Pengaduan');
$btn2Href   = setting('hero.btn2.href', '#pengaduan');
$btn2Var    = setting('hero.btn2.variant', 'success');

$leaderShow = (int) setting('hero.leader.show', 1);
$leaderPhoto= setting('hero.leader.photo', '/assets/img/og-default.jpg');
$leaderName = setting('hero.leader.name', 'Nama Kepala Imigrasi');
$leaderJob  = setting('hero.leader.title', 'Kepala Kantor Imigrasi Jambi');

$heroStyle = sprintf(
  "background: linear-gradient(120deg, %s, %s), url('%s') center/cover no-repeat; min-height: %dvh; display:grid; place-items:center; position:relative;",
  $ov1, $ov2, esc($toAbs($heroBg)), $minVH
);
?>

<!-- Hero -->
<section class="hero text-white" id="beranda" style="<?= esc($heroStyle) ?>">
  <div class="container hero-inner">
    <div class="row g-4 align-items-stretch">
      <?php $leftLgCols = $leaderShow ? '8' : '12'; ?>

      <!-- Kiri -->
      <div class="col-12 col-lg-<?= $leftLgCols ?> d-flex">
        <div class="p-4 p-lg-5 rounded-2xl glass shadow-soft flex-fill">
          <h1 class="display-5 fw-bold mb-2"><?= esc($heroTitle) ?></h1>
          <?php if ($heroSub): ?>
            <p class="lead mb-4"><?= esc($heroSub) ?></p>
          <?php endif; ?>

          <div class="d-flex flex-wrap gap-2 quick-actions">
            <?php if ($btn1Show): ?>
              <a href="<?= esc($btn1Href) ?>" class="btn btn-<?= esc($btn1Var) ?> btn-lg">
                <i class="fa-solid fa-passport me-2" aria-hidden="true"></i><?= esc($btn1Text) ?>
              </a>
            <?php endif; ?>
            <?php if ($btn2Show): ?>
              <a href="<?= esc($btn2Href) ?>" class="btn btn-<?= esc($btn2Var) ?> btn-lg">
                <i class="fa-solid fa-headset me-2" aria-hidden="true"></i><?= esc($btn2Text) ?>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Kanan (opsional) -->
      <?php if ($leaderShow): ?>
        <div class="col-12 col-lg-4 d-none d-lg-flex">
          <div class="card shadow-soft rounded-2xl text-center border-0 flex-fill">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
              <div class="mb-3">
                <img src="<?= esc($toAbs($leaderPhoto)) ?>"
                     alt="<?= esc($leaderName) ?>"
                     class="img-fluid rounded-circle shadow-sm"
                     style="width: 160px; height: 160px; object-fit: cover;">
              </div>
              <h5 class="fw-bold mb-1"><?= esc($leaderName) ?></h5>
              <p class="text-secondary mb-0"><?= esc($leaderJob) ?></p>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Layanan Unggulan -->
<section class="py-5 bg-body-tertiary" id="layanan-online" aria-labelledby="layanan-title">
  <div class="container">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-end mb-4">
      <div>
        <h2 id="layanan-title" class="section-title mb-1">Layanan Daring</h2>
        <p class="text-secondary mb-0 small">Akses cepat ke layanan utama Imigrasi Jambi.</p>
      </div>
      <a href="#" class="btn btn-sm btn-outline-primary">
        Lihat Semua <i class="bi bi-arrow-right-short ms-1" aria-hidden="true"></i>
      </a>
    </div>

    <?php if (!empty($services)): ?>
    <div class="row g-3 g-md-4">
      <?php foreach ($services as $k => $s): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <article class="card feature-card h-100" data-aos="fade-up" data-aos-delay="<?= 100 + ($k%4)*100 ?>">
            <div class="card-body">
              <div class="feature-top">
                <span class="feature-badge" aria-hidden="true"><i class="bi <?= esc($s['icon'] ?: 'bi-stars') ?>"></i></span>
                <i class="bi bi-arrow-up-right arrow-corner" aria-hidden="true"></i>
              </div>
              <h3 class="h5 mb-2">
                <a href="<?= esc($s['url']) ?>" class="stretched-link text-decoration-none">
                  <?= esc($s['title']) ?>
                </a>
              </h3>
              <?php if (!empty($s['description'])): ?>
                <p class="feature-desc"><?= esc($s['description']) ?></p>
              <?php endif; ?>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== Cek Status & Unduh App ===== -->
<section class="py-5" id="status">
  <div class="container">
    <div class="row gy-4 align-items-center">
      <div class="col-lg-6 order-1 order-lg-2 text-lg-end text-center">
        <img class="img-fluid app_img"
             src="<?= esc($toAbs('assets/img/phone_mockup.webp')) ?>"
             alt="Tampilan aplikasi M-PASPOR"
             loading="lazy" width="480" height="480">
      </div>

      <div class="col-lg-6 order-2 order-lg-1">
        <div class="section-title mb-3">
          <h1 class="fw-semibold text-center text-lg-start mb-2">
            Ayo unduh aplikasi <span class="text-nowrap">M-PASPOR</span> sekarang!
          </h1>
        </div>

        <h4 class="mb-lg-5 mb-4 text-center text-lg-start unduh_desc">
          Ajukan permohonan paspor baru atau penggantian secara daring. Mudah dan nyaman.
        </h4>

        <div class="d-flex gap-3 justify-content-lg-start justify-content-center">
          <a href="https://play.google.com/store/apps/details?id=id.go.imigrasi.paspor_online" target="_blank" rel="noopener">
            <img class="app_download img-fluid"
                 src="<?= esc($toAbs('assets/img/pstore.webp')) ?>"
                 alt="Unduh di Google Play"
                 loading="lazy" width="180" height="54">
          </a>
          <a href="https://apps.apple.com/id/app/m-paspor/id1576336459" target="_blank" rel="noopener">
            <img class="app_download img-fluid"
                 src="<?= esc($toAbs('assets/img/appstore.webp')) ?>"
                 alt="Unduh di App Store"
                 loading="lazy" width="180" height="54">
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Berita (Swiper) ===== -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<section class="py-5 bg-body-tertiary" id="berita">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <h2 class="section-title mb-0">Berita</h2>
      <div class="d-flex align-items-center gap-2">
        <a href="<?= site_url('berita') ?>" class="btn btn-sm btn-outline-primary">Arsip Berita</a>
        <button type="button" class="news-prev btn btn-sm btn-light border" aria-label="Sebelumnya">
          <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="news-next btn btn-sm btn-light border" aria-label="Berikutnya">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </div>

    <?php if (!empty($posts)): ?>
      <div class="swiper news-swiper">
        <div class="swiper-wrapper">
          <?php foreach (array_slice($posts, 0, 12) as $p):
            $badgeClass = [
              'news'  => 'text-bg-primary',
              'press' => 'text-bg-success',
              'tips'  => 'text-bg-warning'
            ][$p['type']] ?? 'text-bg-secondary';

            $thumb = $toAbs($p['thumbnail'] ?: 'https://picsum.photos/800/500?blur=1');
          ?>
            <div class="swiper-slide">
              <article class="card h-100 shadow-soft">
                <img src="<?= esc($thumb) ?>" class="card-img-top" alt="<?= esc($p['title']) ?>" loading="lazy" decoding="async">
                <div class="card-body">
                  <span class="badge <?= $badgeClass ?> mb-2"><?= strtoupper(esc($p['type'])) ?></span>
                  <h5 class="card-title mb-2"><?= esc($p['title']) ?></h5>
                  <?php if (!empty($p['excerpt'])): ?>
                    <p class="card-text small text-secondary mb-3"><?= esc($p['excerpt']) ?></p>
                  <?php endif; ?>
                  <a href="<?= site_url('berita/'.urlencode($p['slug'])) ?>" class="stretched-link">Baca Selengkapnya</a>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="news-pagination swiper-pagination mt-3"></div>
      </div>

      <noscript>
        <div class="row g-4 mt-2">
          <?php foreach (array_slice($posts, 0, 6) as $p):
            $badgeClass = [
              'news'=>'text-bg-primary','press'=>'text-bg-success','tips'=>'text-bg-warning'
            ][$p['type']] ?? 'text-bg-secondary';
            $thumb = $toAbs($p['thumbnail'] ?: 'https://picsum.photos/800/500?blur=1');
          ?>
            <div class="col-md-6 col-lg-4">
              <article class="card h-100 shadow-soft">
                <img src="<?= esc($thumb) ?>" class="card-img-top" alt="<?= esc($p['title']) ?>">
                <div class="card-body">
                  <span class="badge <?= $badgeClass ?> mb-2"><?= strtoupper(esc($p['type'])) ?></span>
                  <h5 class="card-title"><?= esc($p['title']) ?></h5>
                  <?php if (!empty($p['excerpt'])): ?>
                    <p class="card-text small text-secondary"><?= esc($p['excerpt']) ?></p>
                  <?php endif; ?>
                  <a href="<?= site_url('berita/'.urlencode($p['slug'])) ?>" class="stretched-link">Baca Selengkapnya</a>
                </div>
              </article>
            </div>
          <?php endforeach; ?>
        </div>
      </noscript>
    <?php else: ?>
      <div class="alert alert-light border shadow-sm p-4 text-center">
        <i class="bi bi-info-circle text-primary fs-3 d-block mb-2"></i>
        <strong>Belum ada berita untuk saat ini.</strong>
      </div>
    <?php endif; ?>
  </div>
</section>

<style>
  .news-swiper .card { border: 0; border-radius: 1rem; overflow: hidden; }
  .news-prev, .news-next { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; }
  .swiper-pagination-bullet { width: 8px; height: 8px; }
  .swiper-pagination-bullet-active { transform: scale(1.2); }
</style>

<!-- ===== FAQ & Persyaratan ===== -->
<section class="py-5" id="faq">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-6">
        <h2 class="section-title mb-3">Pertanyaan yang Sering Diajukan</h2>
        <div class="accordion" id="faqAcc">
          <?php foreach ($faqs as $i => $f):
            $qid = 'q'.$f['id']; $aid = 'a'.$f['id'];
            $show = $i === 0 ? 'show' : ''; $collapsed = $i === 0 ? '' : 'collapsed';
          ?>
            <div class="accordion-item">
              <h2 class="accordion-header" id="<?= $qid ?>">
                <button class="accordion-button <?= $collapsed ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $aid ?>">
                  <?= esc($f['question']) ?>
                </button>
              </h2>
              <div id="<?= $aid ?>" class="accordion-collapse collapse <?= $show ?>" data-bs-parent="#faqAcc">
                <div class="accordion-body small"><?= esc($f['answer']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-lg-6">
        <h2 class="section-title mb-3">Unduhan & Regulasi</h2>
        <ul class="list-group list-group-flush shadow-sm rounded-2xl">
          <?php foreach ($docs as $d): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= esc($d['title']) ?>
              <a href="<?= esc($d['file_url']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Unduh</a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ===== Pengaduan & Kontak ===== -->
<section class="py-5 bg-body-tertiary" id="kontak">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <h2 class="section-title">Hubungi Kami</h2>
        <p class="text-secondary">Silakan tinggalkan pesan. Kami akan merespons pada hari/jam kerja.</p>
        <div class="card shadow-soft rounded-2xl mb-3">
          <div class="card-body">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-location-dot mt-1 me-3 text-primary"></i>
              <div>
                <strong>Kantor Imigrasi Jambi</strong><br>
                Jl. Arif Rahman Hakim No.63, Simpang IV Sipin, Kec. Telanaipura, Kota Jambi, Jambi 36125
              </div>
            </div>
            <hr>
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-bus mt-1 me-3 text-primary"></i>
              <div>
                <strong>Transportasi</strong><br>
                Angkot Koridor A — Turun di Halte Imigrasi; Parkir kendaraan tersedia.
              </div>
            </div>
          </div>
        </div>
        <iframe title="Peta Kantor" class="rounded-2xl shadow-soft w-100" height="260" loading="lazy"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3853.207964368843!2d103.57462059999999!3d-1.6187143999999998!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e25886e387122fb%3A0xcf9015e56a82f95a!2sKantor%20Imigrasi%20Kelas%20I%20TPI%20Jambi!5e1!3m2!1sid!2sid!4v1755622952922!5m2!1sid!2sid"></iframe>
      </div>

      <div class="col-lg-7" id="pengaduan">
        <div class="card shadow-soft rounded-2xl">
          <div class="card-body p-4">
            <h5 class="mb-3">Form Pengaduan / Pertanyaan</h5>
            <form id="formPengaduan" class="row g-3" method="post" action="<?= site_url('pengaduan'); ?>">
              <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

              <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" name="name" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">No. WhatsApp</label>
                <input type="tel" name="whatsapp" class="form-control" placeholder="08xxxxxxxxxx" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select" required>
                  <option value="">Pilih...</option>
                  <option>Informasi Paspor</option>
                  <option>Pengaduan Layanan</option>
                  <option>WNA/Izin Tinggal</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label">Pesan</label>
                <textarea name="message" class="form-control" rows="4" required></textarea>
              </div>

              <div class="col-12 d-grid d-md-flex gap-2">
                <button id="btnKirim" class="btn btn-success" type="submit">
                  <i class="fa-solid fa-paper-plane me-2"></i>Kirim
                </button>
                <button class="btn btn-outline-secondary" type="reset">Reset</button>
              </div>

              <div class="small text-secondary">Dengan mengirim, Anda setuju terhadap <a href="#">Kebijakan Privasi</a>.</div>
            </form>

            <!-- SweetAlert2: load SEKALI di layout -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
            (function(){
              const form = document.getElementById('formPengaduan');
              const btn  = document.getElementById('btnKirim');

              function getCsrfInput(){ return form.querySelector('input[type="hidden"][name^="csrf_"]'); }
              function updateCsrfToken(csrf){
                if (!csrf || !csrf.name || !csrf.hash) return;
                let el = getCsrfInput();
                if (!el) { el = document.createElement('input'); el.type='hidden'; form.prepend(el); }
                el.setAttribute('name', csrf.name); el.value = csrf.hash;
              }

              form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const oldHtml = btn.innerHTML; btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
                try {
                  const fd = new FormData(form);
                  const res = await fetch(form.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd });
                  const data = await res.json().catch(() => ({}));
                  if (data.csrf) updateCsrfToken(data.csrf);
                  if (data.ok) {
                    await Swal.fire({ icon:'success', title:'Terkirim', html:'Pengaduan berhasil dikirim.<br>Nomor tiket: <b>'+ (data.ticket || '-') +'</b>' });
                    form.reset();
                  } else {
                    const list = (data.errors || [data.msg || 'Terjadi kesalahan.']).map(e => '<li>'+e+'</li>').join('');
                    Swal.fire({ icon:'error', title:'Gagal mengirim', html:'<ul class="text-start mb-0">'+ list +'</ul>' });
                  }
                } catch (err) {
                  Swal.fire({ icon:'error', title:'Kesalahan jaringan', text:'Silakan coba lagi.' });
                } finally {
                  btn.disabled = false; btn.innerHTML = oldHtml;
                }
              });
            })();
            </script>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Footer ===== -->
<footer class="pt-5 pb-4 bg-dark text-light">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <h5>Imigrasi Jambi</h5>
        <p class="small text-secondary"><?= esc(setting('site.tagline','Portal layanan resmi keimigrasian wilayah Jambi.')) ?></p>
        <div class="d-flex gap-3">
          <?php if ($ig = setting('social.instagram')): ?>
            <a class="text-light" href="<?= esc($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
          <?php endif; ?>
          <?php if ($fb = setting('social.facebook')): ?>
            <a class="text-light" href="<?= esc($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
          <?php endif; ?>
          <?php if ($yt = setting('social.youtube')): ?>
            <a class="text-light" href="<?= esc($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube fa-lg"></i></a>
          <?php endif; ?>
          <?php if ($x = setting('social.x')): ?>
            <a class="text-light" href="<?= esc($x) ?>" target="_blank" rel="noopener" aria-label="X"><i class="fab fa-x-twitter fa-lg"></i></a>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <h6>Layanan</h6>
        <ul class="list-unstyled small">
          <li><a class="text-decoration-none text-light-50" href="#">Paspor</a></li>
          <li><a class="text-decoration-none text-light-50" href="#">Izin Tinggal</a></li>
          <li><a class="text-decoration-none text-light-50" href="#">Status Permohonan</a></li>
          <li><a class="text-decoration-none text-light-50" href="#">Pengaduan</a></li>
        </ul>
      </div>
      <div class="col-md-6 col-lg-4">
        <h6>Buletin</h6>
        <p class="small text-secondary">Dapatkan info terbaru seputar layanan.</p>
        <form id="formSubscribe" class="d-flex gap-2" method="post" action="<?= site_url('subscribe'); ?>">
          <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
          <input type="email" name="email" class="form-control" placeholder="Email Anda" required>
          <button id="btnSubscribe" class="btn btn-primary" type="submit">Daftar</button>
        </form>
        <script>
        (function(){
          const form = document.getElementById('formSubscribe');
          const btn  = document.getElementById('btnSubscribe');
          function getCsrfInput(){ return form.querySelector('input[type="hidden"][name^="csrf_"]'); }
          function updateCsrfToken(csrf){
            if (!csrf || !csrf.name || !csrf.hash) return;
            let el = getCsrfInput();
            if (!el){ el=document.createElement('input'); el.type='hidden'; form.prepend(el); }
            el.setAttribute('name', csrf.name); el.value = csrf.hash;
          }
          form.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const oldHtml = btn.innerHTML; btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';
            try {
              const fd = new FormData(form);
              const res = await fetch(form.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:fd });
              const data = await res.json().catch(()=>({}));
              if (data.csrf) updateCsrfToken(data.csrf);
              if (data.ok) {
                await Swal.fire({ icon:'success', title:'Berhasil', text: data.msg || 'Terdaftar.' });
                form.reset();
              } else {
                const msgs = (data.errors || [data.msg || 'Gagal mendaftar.']);
                Swal.fire({ icon:'error', title:'Gagal',
                  html:'<ul class="text-start mb-0">'+msgs.map(m=>'<li>'+m+'</li>').join('')+'</ul>' });
              }
            } catch (err) {
              Swal.fire({ icon:'error', title:'Kesalahan jaringan', text:'Silakan coba lagi.' });
            } finally {
              btn.disabled = false; btn.innerHTML = oldHtml;
            }
          });
        })();
        </script>
      </div>
    </div>

    <hr class="border-secondary my-4">
    <div class="d-flex flex-column flex-md-row justify-content-between small">
      <div>© <span id="y"></span> Imigrasi Jambi • Kementerian Hukum dan HAM RI</div>
      <div class="text-secondary">Versi 1.0</div>
    </div>
  </div>
</footer>

<!-- Back to top -->
<button id="toTop" class="btn btn-primary rounded-pill" aria-label="Kembali ke atas"><i class="fa-solid fa-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
  // Tahun footer
  document.getElementById('y').textContent = new Date().getFullYear();

  // Theme toggle: cycle light → dark → auto
  (function(){
    const btn   = document.getElementById('themeToggle');
    const label = document.getElementById('themeLabel');
    const icon  = document.getElementById('themeIcon');
    if(!btn || !label || !icon) return;

    const cycle = ['light','dark','auto'];
    const apply = (t) => {
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      const mode = (t === 'auto') ? (prefersDark ? 'dark' : 'light') : t;
      document.documentElement.setAttribute('data-bs-theme', mode);
      localStorage.setItem('theme', t);

      // label & icon
      label.textContent = (t === 'auto') ? 'Auto' : (mode === 'dark' ? 'Dark' : 'Light');
      icon.className = 'me-1 ' + (t === 'auto' ? 'fa-regular fa-circle-half-stroke' : (mode === 'dark' ? 'fa-regular fa-moon' : 'fa-regular fa-sun'));
      // tombol pakai outline-primary sudah mengikuti brand
    };

    // init dari saved
    let saved = localStorage.getItem('theme') || "<?= esc(setting('site.theme','light')) ?>";
    apply(saved);

    btn.addEventListener('click', ()=>{
      const cur = localStorage.getItem('theme') || 'light';
      const next = cycle[(cycle.indexOf(cur)+1)%cycle.length];
      apply(next);
    });

    // sinkron saat auto & OS berubah
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', e=>{
      const st = localStorage.getItem('theme') || 'light';
      if (st === 'auto') apply('auto');
    });
  })();

  // Back to top
  (function(){
    const toTop = document.getElementById('toTop');
    window.addEventListener('scroll',()=>{ toTop.style.display = window.scrollY > 400 ? 'inline-flex' : 'none'; });
    toTop.addEventListener('click',()=> window.scrollTo({top:0,behavior:'smooth'}));
  })();

  // Tutup offcanvas ketika klik link
  document.querySelectorAll('#sidebarOffcanvas a.nav-link').forEach(a=>{
    a.addEventListener('click', ()=>{
      const el = document.getElementById('sidebarOffcanvas');
      const oc = bootstrap.Offcanvas.getInstance(el);
      if (oc) oc.hide();
    });
  });

  // Putar chevron untuk collapse di dalam offcanvas (aman walau tidak ada)
  document.querySelectorAll('#sidebarOffcanvas .collapse').forEach(col => {
    col.addEventListener('show.bs.collapse', () => {
      const btn = document.querySelector('[data-bs-target="#' + col.id + '"] .toggle-icon');
      btn && (btn.style.transform = 'rotate(180deg)');
    });
    col.addEventListener('hide.bs.collapse', () => {
      const btn = document.querySelector('[data-bs-target="#' + col.id + '"] .toggle-icon');
      btn && (btn.style.transform = 'rotate(0deg)');
    });
  });

  // AOS
  AOS.init({ once:true, duration:600, easing:'ease-out' });

  // Swiper Berita
  const newsSwiper = new Swiper('.news-swiper', {
    slidesPerView: 1.1,
    spaceBetween: 16,
    loop: false,
    keyboard: { enabled: true },
    grabCursor: true,
    autoplay: { delay: 5000, disableOnInteraction: false },
    pagination: { el: '.news-pagination', clickable: true },
    navigation: { nextEl: '.news-next', prevEl: '.news-prev' },
    breakpoints: { 576: { slidesPerView: 2, spaceBetween: 16 }, 992: { slidesPerView: 3, spaceBetween: 20 } },
    observer: true,
    observeParents: true
  });
</script>
<script>
(function(){
  const tb  = document.querySelector('.topbar');
  const nav = document.querySelector('.navbar.fixed-top');
  if(!nav) return;

  function setVars(){
    const th = tb ? tb.offsetHeight : 0;
    document.documentElement.style.setProperty('--topbar-h', th + 'px');
    // beri padding agar konten tidak ketutup navbar fixed
    document.body.style.paddingTop = (nav.offsetHeight) + 'px';
  }

  function onScroll(){
    const th = tb ? tb.offsetHeight : 0;
    if (window.scrollY >= th) document.body.classList.add('scrolled');
    else document.body.classList.remove('scrolled');
  }

  // jalankan secepatnya
  setVars(); onScroll();
  window.addEventListener('resize', setVars);
  window.addEventListener('scroll', onScroll, {passive:true});
})();
</script>



</body>
</html>
