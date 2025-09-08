<?php helper('settings'); ?>

<!doctype html>
<html lang="<?= esc(setting('site.lang','id')) ?>"
      data-bs-theme="<?= esc(setting('site.theme','light')) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

<?php
  $siteTitle  = setting('site.title','Imigrasi Jambi — Pelayanan Keimigrasian');
  $pageTitle  = trim($this->renderSection('title') ?? '');
  $fullTitle  = $pageTitle ? ($pageTitle.' — '.$siteTitle) : $siteTitle;

  $metaDesc   = trim($this->renderSection('meta_desc') ?? '')
                ?: setting('site.meta_description','Layanan resmi Imigrasi Jambi: paspor, izin tinggal, informasi WNA/WNI, pengaduan, dan status permohonan.');
  $metaRobots = setting('site.meta_robots','index,follow');

  // OpenGraph image (absolute URL)
  $ogImageRaw = trim($this->renderSection('meta_image') ?? '');
  $toAbsolute = function(string $u): string {
      if ($u === '') return $u;
      if (preg_match('~^https?://~i', $u)) return $u;
      return base_url(ltrim($u,'/'));
  };
  $ogImage = $ogImageRaw !== '' ? $toAbsolute($ogImageRaw)
            : $toAbsolute(setting('site.og_image', 'assets/img/og-default.jpg'));
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

<script>
  // Set theme sedini mungkin agar minim FOUC
  (function(){
    try{
      var saved = localStorage.getItem('theme');
      if(saved){ document.documentElement.setAttribute('data-bs-theme', saved === 'auto'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : saved);
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

  /* Subtle/emphasis */
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
[data-bs-theme="dark"] .navbar{
  --bs-navbar-brand-color:#000;
  --bs-navbar-brand-hover-color:#000;
}

/* Topbar selalu terang di dark mode */
.topbar{
  font-size:.9rem;
  border-bottom:1px solid rgba(0,0,0,.06);
  color: var(--bs-body-color);
}
.topbar a{ color:inherit; text-decoration:none; }
[data-bs-theme="dark"] .topbar{
  background-color:#fff !important;
  border-bottom-color: rgba(0,0,0,.08) !important;
  --bs-body-color:#000;
  --bs-link-color:#000;
  --bs-link-hover-color: var(--brand);
}
[data-bs-theme="dark"] .topbar a,
[data-bs-theme="dark"] .topbar i{ color:#000 !important; }
[data-bs-theme="dark"] .topbar a:hover{ color:var(--brand) !important; }

/* =========================================================
   NAVBAR
   ========================================================= */
.navbar-brand img{ height:44px; }
.nav-link{ font-weight:500; }
.nav-link.active{ color:var(--brand) !important; }

/* =========================================================
   HERO BASE (jaga kompatibilitas komponen hero)
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
   UTILITIES & CARDS
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

/* Sidebar / Offcanvas */
.dropdown-menu{ z-index:1050; }
.sidebar-nav .nav-link{ color: var(--bs-body-color); }
.sidebar-nav .nav-link:hover{ background: var(--bs-light); }
.sidebar-nav .toggle-icon{ transition: transform .2s ease; }
.sidebar-nav .collapse.show ~ .toggle-icon,
.sidebar-nav .nav-link[aria-expanded="true"] .toggle-icon{ transform: rotate(180deg); }
.offcanvas{ width:320px; }

/* App banners */
.app_img{ max-width:420px; }
.app_download{ max-width:180px; height:auto; }
.unduh_desc{ color: var(--bs-secondary-color, #6c757d); }
@media (max-width:576px){
  .app_img{ max-width:320px; }
  .app_download{ max-width:160px; }
  #layanan-online .row{ row-gap:.75rem; }
}

/* Ticker */
.ticker{ white-space:nowrap; overflow:hidden; }
.ticker span{ display:inline-block; padding-left:100%; animation: ticker 20s linear infinite; will-change: transform; }
@keyframes ticker{ from{ transform: translate3d(0,0,0); } to{ transform: translate3d(-100%,0,0); } }

/* Footer & misc */
footer a{ text-decoration:none; }
#toTop{ position:fixed; right:1rem; bottom:1rem; display:none; z-index:1030; }
.section-title{ font-weight:800; letter-spacing:.2px; }

/* Tombol "ikut tema" (opsional, untuk tombol outline yang selalu pakai primary) */
.btn-theme{
  --bs-btn-color: var(--bs-primary);
  --bs-btn-border-color: var(--bs-primary);
  --bs-btn-hover-color: #fff;
  --bs-btn-hover-bg: var(--bs-primary);
  --bs-btn-hover-border-color: var(--bs-primary);
  --bs-btn-active-color: #fff;
  --bs-btn-active-bg: var(--bs-primary);
  --bs-btn-active-border-color: var(--bs-primary);
  --bs-btn-disabled-color: var(--bs-primary);
  --bs-btn-disabled-border-color: var(--bs-primary);
}
</style>
<style>
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



<?= $this->renderSection('styles') ?>
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
          <button class="btn btn-sm btn-outline-primary btn-theme dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Ganti bahasa">
            <i class="fa-solid fa-language me-1" aria-hidden="true"></i> <?= strtoupper(esc($lang)) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="?lang=id" hreflang="id">Bahasa Indonesia</a></li>
            <li><a class="dropdown-item" href="?lang=en" hreflang="en">English</a></li>
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

<!-- ===== SLOT KONTEN HALAMAN ===== -->
<?= $this->renderSection('content') ?>

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
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init({ once:true, duration:600, easing:'ease-out' });</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Tahun footer
  document.getElementById('y').textContent = new Date().getFullYear();

  // Theme toggle: light → dark → auto
  (function(){
    const btn   = document.getElementById('themeToggle');
    const icon  = document.getElementById('themeIcon');
    const label = document.getElementById('themeLabel');
    if(!btn || !label) return;

    const cycle = ['light','dark','auto'];
    const applyIcon = (t) => {
      if(!icon) return;
      icon.classList.remove('fa-moon','fa-sun','fa-circle-half-stroke','fa-regular','fa-solid');
      if(t==='dark'){ icon.classList.add('fa-regular','fa-sun'); }
      else if(t==='auto'){ icon.classList.add('fa-solid','fa-circle-half-stroke'); }
      else { icon.classList.add('fa-regular','fa-moon'); }
    };
    const setTheme = (t) => {
      if(t==='auto'){
        document.documentElement.setAttribute('data-bs-theme',
          window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
        );
      } else {
        document.documentElement.setAttribute('data-bs-theme', t);
      }
      localStorage.setItem('theme', t);
      label.textContent = 'Mode: ' + (t.charAt(0).toUpperCase()+t.slice(1));
      applyIcon(t);
    };

    // init dari localStorage atau default setting
    let saved = '<?= esc(setting('site.theme','light')) ?>';
    try { const st = localStorage.getItem('theme'); if(st) saved = st; } catch(e){}
    setTheme(saved);

    btn.addEventListener('click', ()=>{
      const cur = localStorage.getItem('theme') || 'light';
      const idx = cycle.indexOf(cur);
      const next= cycle[(idx+1)%cycle.length];
      setTheme(next);
    });

    // auto mengikuti OS
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener?.('change', e=>{
      const st = localStorage.getItem('theme') || 'light';
      if(st === 'auto'){
        document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
      }
    });
  })();

  // Back to top
  (function(){
    const toTop = document.getElementById('toTop');
    window.addEventListener('scroll',()=>{ toTop.style.display = window.scrollY > 400 ? 'inline-flex' : 'none'; });
    toTop.addEventListener('click',()=> window.scrollTo({top:0,behavior:'smooth'}));
  })();

  // Tutup offcanvas saat klik link internal
  document.querySelectorAll('#sidebarOffcanvas a.nav-link').forEach(a=>{
    a.addEventListener('click', ()=> {
      const el = document.getElementById('sidebarOffcanvas');
      const oc = bootstrap.Offcanvas.getInstance(el);
      if (oc) oc.hide();
    });
  });

  // Putar chevron saat collapse di sidebar dibuka/tutup
  document.querySelectorAll('#sidebarOffcanvas .collapse').forEach(col => {
    col.addEventListener('show.bs.collapse', () => {
      const btn = document.querySelector('#sidebarOffcanvas [data-bs-target="#' + col.id + '"] .toggle-icon');
      if (btn) btn.style.transform = 'rotate(180deg)';
    });
    col.addEventListener('hide.bs.collapse', () => {
      const btn = document.querySelector('#sidebarOffcanvas [data-bs-target="#' + col.id + '"] .toggle-icon');
      if (btn) btn.style.transform = 'rotate(0deg)';
    });
  });

  // Subscribe AJAX + refresh CSRF
  (function(){
    const form = document.getElementById('formSubscribe');
    const btn  = document.getElementById('btnSubscribe');
    if(!form || !btn) return;

    function getCsrf(){ return form.querySelector('input[type="hidden"][name^="csrf_"]'); }
    function setCsrf(csrf){
      if (!csrf || !csrf.name || !csrf.hash) return;
      let el = getCsrf();
      if (!el) { el = document.createElement('input'); el.type='hidden'; form.prepend(el); }
      el.name = csrf.name; el.value = csrf.hash;
    }

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const old = btn.innerHTML; btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Memproses…';
      try{
        const res = await fetch(form.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:new FormData(form) });
        const data = await res.json().catch(()=>({}));
        if (data.csrf) setCsrf(data.csrf);
        if (data.ok) {
          await Swal.fire({icon:'success', title:'Berhasil', text:data.msg||'Terdaftar.'});
          form.reset();
        } else {
          const msgs = (data.errors || [data.msg || 'Gagal mendaftar.']);
          Swal.fire({icon:'error', title:'Gagal', html:'<ul class="text-start mb-0">'+msgs.map(m=>'<li>'+m+'</li>').join('')+'</ul>'});
        }
      }catch(err){
        Swal.fire({icon:'error', title:'Kesalahan jaringan', text:'Silakan coba lagi.'});
      }finally{
        btn.disabled=false; btn.innerHTML=old;
      }
    });
  })();
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



<?= $this->renderSection('scripts') ?>
</body>
</html>
