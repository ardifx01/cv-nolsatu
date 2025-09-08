<?php
/** @var string $title */
helper('settings','nav');
$uri   = service('uri');
$path = '/' . trim(service('uri')->getPath(), '/');
function is_active($path, $needle){
  return str_starts_with($path, $needle) ? 'active' : '';
}
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= esc($title ?? 'Admin') ?> — <?= esc(setting('site.title','Imigrasi Jambi')) ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- DataTables v2 + Bootstrap 5 theme -->
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.css">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    :root{
      --brand: #0d6efd;
    }
    body{ background: linear-gradient(180deg, rgba(0,0,0,.02), transparent 50%) }
    .navbar-brand img{ height: 28px; }
    .app-sidebar{
      min-height: calc(100vh - 56px); /* tinggi - navbar */
      background: var(--bs-body-bg);
      border-right: 1px solid var(--bs-border-color);
    }
    .sidebar-inner{ position: sticky; top: 56px; padding: 1rem .75rem; }
    .side-head{ padding: .5rem .75rem; text-transform: uppercase; letter-spacing: .04em; font-size: .75rem; color: var(--bs-secondary-color); }
    .nav-aside .nav-link{
      display: flex; align-items: center; gap: .5rem;
      padding: .5rem .75rem; border-radius: .5rem; color: var(--bs-body-color);
      transition: background .15s ease, color .15s ease;
    }
    .nav-aside .nav-link:hover{ background: var(--bs-secondary-bg); }
    .nav-aside .nav-link.active{
      background: var(--bs-primary-bg-subtle); color: var(--bs-primary);
      font-weight: 600;
    }
    .content-wrap{ padding: 1.25rem; }
    .page-title{ margin: 0 0 .25rem 0; }
    .page-subtitle{ color: var(--bs-secondary-color); margin-bottom: 0; }
    .toolbar{ display:flex; gap:.5rem; align-items:center; }
    .card-soft{ border:0; border-radius: .75rem; box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.06); }
    [data-bs-theme="dark"] .card-soft{ box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.25); }
    @media (max-width: 991.98px){
      .app-sidebar{ display:none; }
    }
  </style>

  <?= $this->renderSection('styles') ?>
  <script>
    // Pakai tema tersimpan lebih dulu (sebelum paint)
    (function(){
      try {
        var t = localStorage.getItem('theme');
        if (t) document.documentElement.setAttribute('data-bs-theme', t);
      } catch(e){}
    })();
  </script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand bg-white border-bottom sticky-top">
  <div class="container-fluid">
    <!-- Toggle sidebar (mobile) -->
    <button class="btn btn-outline-secondary d-lg-none me-2" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"
            aria-controls="offcanvasSidebar" aria-label="Menu">
      <i class="bi bi-list"></i>
    </button>

    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= site_url('admin') ?>">
      <img src="<?= esc(setting('site.logo_url', base_url('logo_header_2025.webp'))) ?>" alt="Logo">
      <span class="fw-semibold">Admin</span>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <!-- Tempatkan toolbar khusus halaman -->
      <div class="d-none d-md-block">
        <?= $this->renderSection('toolbar') ?>
      </div>

      <!-- Theme toggle -->
      <button class="btn btn-outline-secondary" type="button" id="themeToggle" title="Terang/Gelap">
        <i class="bi bi-moon"></i>
      </button>

      <!-- User dropdown -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle me-1"></i><?= esc(session('admin_username') ?? 'Admin') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">Signed in</h6></li>
          <li><a class="dropdown-item" href="<?= site_url('admin/settings') ?>"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= site_url('admin/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    <!-- SIDEBAR DESKTOP -->
    <aside class="col-lg-2 app-sidebar d-none d-lg-block">
      <div class="sidebar-inner">
        <div class="side-head">Navigasi</div>
        <nav class="nav-aside flex-column">
          <a class="nav-link " href="<?= site_url('admin') ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/settings') ?>" href="<?= site_url('admin/settings') ?>">
            <i class="bi bi-gear"></i><span>Settings</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/announcements') ?>" href="<?= site_url('admin/announcements') ?>">
            <i class="bi bi-megaphone"></i><span>Announcements</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/services') ?>" href="<?= site_url('admin/services') ?>">
            <i class="bi bi-box"></i><span>Services</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/posts') ?>" href="<?= site_url('admin/posts') ?>">
            <i class="bi bi-newspaper"></i><span>Posts</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/faqs') ?>" href="<?= site_url('admin/faqs') ?>">
            <i class="bi bi-question-circle"></i><span>FAQs</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/documents') ?>" href="<?= site_url('admin/documents') ?>">
            <i class="bi bi-file-earmark-arrow-down"></i><span>Documents</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/pages') ?>" href="<?= site_url('admin/pages') ?>">
  <i class="bi bi-file-earmark-text"></i><span>Pages</span>
</a>

          <a class="nav-link <?= is_active($path, '/admin/menu') ?>" href="<?= site_url('admin/menu') ?>">
            <i class="bi bi-list-nested"></i><span>Menu</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/complaints') ?>" href="<?= site_url('admin/complaints') ?>">
            <i class="bi bi-inboxes"></i><span>Complaints</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/subscribers') ?>" href="<?= site_url('admin/subscribers') ?>">
            <i class="bi bi-people"></i><span>Subscribers</span>
          </a>
        </nav>
      </div>
    </aside>

    <!-- SIDEBAR MOBILE: OFFCANVAS -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasSidebarLabel" class="mb-0">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
      </div>
      <div class="offcanvas-body">
        <nav class="nav-aside flex-column">
          <a class="nav-link <?= is_active($path, '/admin$') ?: is_active($path, '/admin/') ?>" href="<?= site_url('admin') ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/settings') ?>" href="<?= site_url('admin/settings') ?>">
            <i class="bi bi-gear"></i><span>Settings</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/announcements') ?>" href="<?= site_url('admin/announcements') ?>">
            <i class="bi bi-megaphone"></i><span>Announcements</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/services') ?>" href="<?= site_url('admin/services') ?>">
            <i class="bi bi-box"></i><span>Services</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/posts') ?>" href="<?= site_url('admin/posts') ?>">
            <i class="bi bi-newspaper"></i><span>Posts</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/faqs') ?>" href="<?= site_url('admin/faqs') ?>">
            <i class="bi bi-question-circle"></i><span>FAQs</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/documents') ?>" href="<?= site_url('admin/documents') ?>">
            <i class="bi bi-file-earmark-arrow-down"></i><span>Documents</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/menu') ?>" href="<?= site_url('admin/menu') ?>">
            <i class="bi bi-list-nested"></i><span>Menu</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/complaints') ?>" href="<?= site_url('admin/complaints') ?>">
            <i class="bi bi-inboxes"></i><span>Complaints</span>
          </a>
          <a class="nav-link <?= is_active($path, '/admin/subscribers') ?>" href="<?= site_url('admin/subscribers') ?>">
            <i class="bi bi-people"></i><span>Subscribers</span>
          </a>
        </nav>
      </div>
    </div>

    <!-- CONTENT -->
    <main class="col-lg-10 content-wrap">
      <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div>
          <h2 class="page-title"><?= esc($title ?? 'Dashboard') ?></h2>
          <?php if (isset($subtitle) && $subtitle): ?>
            <p class="page-subtitle"><?= esc($subtitle) ?></p>
          <?php endif; ?>
        </div>
        <div class="toolbar d-block d-md-none">
          <?= $this->renderSection('toolbar') ?>
        </div>
      </div>

      <?= $this->renderSection('content') ?>
    </main>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables v2 (tanpa jQuery) + Bootstrap 5 integration -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Toggle tema terang/gelap
  document.getElementById('themeToggle')?.addEventListener('click', function(){
    const root = document.documentElement;
    const current = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-bs-theme', current);
    try{ localStorage.setItem('theme', current); }catch(e){}
  });

  // Tutup sidebar mobile saat klik link
  document.querySelectorAll('#offcanvasSidebar .nav-link').forEach(a=>{
    a.addEventListener('click', ()=> {
      const off = bootstrap.Offcanvas.getInstance('#offcanvasSidebar');
      off && off.hide();
    });
  });
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
