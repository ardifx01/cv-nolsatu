<?php helper('settings'); ?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Masuk Admin — <?= esc(setting('site.title','Imigrasi Jambi')) ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --brand: #0d6efd;
      --glass-bg: rgba(255,255,255,.72);
      --glass-bg-dark: rgba(18,18,22,.72);
    }
    body{
      min-height: 100vh;
      background:
        radial-gradient(1000px 500px at -10% -10%, rgba(13,110,253,.12), transparent 60%),
        radial-gradient(1000px 500px at 110% 110%, rgba(25,135,84,.12), transparent 60%),
        linear-gradient(180deg, rgba(0,0,0,.02), transparent 60%);
      display: grid; place-items: center;
      padding: 2rem 1rem;
    }
    .auth-card{
      max-width: 420px;
      border: 0;
      border-radius: 1rem;
      backdrop-filter: saturate(160%) blur(10px);
      background: var(--glass-bg);
      box-shadow: 0 .75rem 2rem rgba(0,0,0,.1);
      overflow: hidden;
    }
    [data-bs-theme="dark"] .auth-card{
      background: var(--glass-bg-dark);
      box-shadow: 0 .75rem 2rem rgba(0,0,0,.35);
    }
    .brand-wrap{
      display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem;
    }
    .brand-wrap img{ height: 40px; }
    .brand-title{ line-height:1.1 }
    .brand-title small{ color: var(--bs-secondary-color); }
    .form-control:focus{ box-shadow: 0 0 0 .15rem rgba(13,110,253,.25); }
    .btn-primary{ border-radius:.75rem; }
    .input-group-text{ background: transparent; }
    .footer-copy{ color: var(--bs-secondary-color); }
  </style>

  <script>
    // Hormati tema tersimpan (opsional)
    (function(){
      try{ var t = localStorage.getItem('theme'); if(t){ document.documentElement.setAttribute('data-bs-theme', t); } }catch(e){}
    })();
  </script>
</head>
<body>

  <div class="card auth-card w-100">
    <div class="card-body p-4 p-md-4">
      <!-- Brand -->
      <div class="brand-wrap">
        <img src="<?= esc(setting('site.logo_url', base_url('logo_header_2025.webp'))) ?>" alt="Logo">
        <div class="brand-title">
          <strong><?= esc(setting('site.shortname','Imigrasi Jambi')) ?></strong><br>
          <small>Panel Administrator</small>
        </div>
        <button class="btn btn-sm btn-outline-secondary ms-auto" id="themeToggle" type="button" title="Mode Terang/Gelap">
          <i class="bi bi-moon"></i>
        </button>
      </div>

      <h4 class="mb-3">Masuk</h4>
      <p class="text-secondary mb-4 small">Gunakan kredensial admin Anda untuk mengakses dashboard.</p>

      <?php if(session('error')): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2"><i class="bi bi-exclamation-triangle"></i><div><?= esc(session('error')) ?></div></div>
      <?php endif; ?>
      <?php if(session('success')): ?>
        <div class="alert alert-success d-flex align-items-start gap-2"><i class="bi bi-check-circle"></i><div><?= esc(session('success')) ?></div></div>
      <?php endif; ?>

      <form method="post" action="<?= site_url('admin/login') ?>" class="needs-validation" novalidate id="loginForm">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input id="email" name="email" type="email" class="form-control" placeholder="nama@domain.go.id" required autofocus>
            <div class="invalid-feedback">Masukkan email yang valid.</div>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-key"></i></span>
            <input id="password" name="password" type="password" class="form-control" placeholder="••••••••" required>
            <button class="btn btn-outline-secondary" type="button" id="togglePwd" tabindex="-1"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback">Password wajib diisi.</div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
          </div>
          <a class="small text-decoration-none" href="#" tabindex="-1">Lupa password?</a>
        </div>

        <button class="btn btn-primary w-100 d-inline-flex justify-content-center align-items-center" id="submitBtn">
          <span class="btn-text">Masuk</span>
          <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
        </button>
      </form>
    </div>
  </div>

  <p class="text-center small mt-3 mb-0 footer-copy">&copy; <?= date('Y') ?> <?= esc(setting('site.title','Imigrasi Jambi')) ?></p>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle tema
    document.getElementById('themeToggle')?.addEventListener('click', function(){
      const root = document.documentElement;
      const current = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-bs-theme', current);
      try{ localStorage.setItem('theme', current); }catch(e){}
    });

    // Show/hide password
    document.getElementById('togglePwd')?.addEventListener('click', function(){
      const inp = document.getElementById('password');
      const isText = inp.type === 'text';
      inp.type = isText ? 'password' : 'text';
      this.firstElementChild.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
      inp.focus();
    });

    // Validasi & state loading
    (function(){
      const form = document.getElementById('loginForm');
      const btn  = document.getElementById('submitBtn');
      form?.addEventListener('submit', function(e){
        if (!form.checkValidity()){
          e.preventDefault();
          e.stopPropagation();
        } else {
          btn.disabled = true;
          btn.querySelector('.spinner-border').classList.remove('d-none');
          btn.querySelector('.btn-text').textContent = 'Memproses...';
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
</body>
</html>
