<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Pegawai - talk caffe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: Inter, system-ui, sans-serif; background: #f7f6f2; color: #263a31; }
        .btn-primary { --bs-btn-bg:#356b4c; --bs-btn-border-color:#356b4c; --bs-btn-hover-bg:#2d5b41; --bs-btn-hover-border-color:#2d5b41; }
        .login-image { min-height: calc(100vh - 3rem); background: linear-gradient(180deg,rgba(20,44,32,.2),rgba(13,39,27,.86)),url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1300&q=85') center/cover; }
        .login-form-column { min-height: calc(100vh - 3rem); }
    </style>
</head>
<body>
<main class="container-fluid min-vh-100 d-flex align-items-center justify-content-center px-3"><section class="w-100 d-flex justify-content-center"><form method="post" class="w-100" style="max-width: 440px">
        <input type="hidden" name="action" value="login">
        <h1 class="display-6 mt-4">Selamat datang kembali</h1><p class="lead text-secondary mt-3">Masuk untuk mulai melayani dan mengelola pesanan di talk caffe.</p>
        <?php if ($error): ?><div class="alert alert-danger mt-4"><?= e($error) ?></div><?php endif; ?>
        <label class="form-label fw-semibold mt-4">Email atau username</label><div class="input-group"><input name="identity" type="text" class="form-control form-control-lg" required><span class="input-group-text bg-white"><i data-lucide="user" width="18"></i></span></div>
        <label class="form-label fw-semibold mt-4">Kata sandi</label><div class="input-group"><input name="password" type="password" class="form-control form-control-lg" required><span class="input-group-text bg-white"><i data-lucide="eye-off" width="18"></i></span></div>
        <div class="d-flex justify-content-between align-items-center mt-4 small"><label class="text-secondary"><input type="checkbox" checked class="form-check-input me-2">Ingat saya</label><a class="fw-semibold text-success" href="#">Lupa kata sandi?</a></div>
        <button class="btn btn-primary btn-lg w-100 mt-4"><i data-lucide="arrow-right" width="18" class="me-2"></i>Masuk ke ruang kerja</button>
        <div class="border-top mt-4 pt-4 small text-secondary"><i data-lucide="shield-check" width="18" class="me-2 text-success"></i>Akses khusus pegawai. Belum memiliki akun? Hubungi pengelola restoran Anda.</div><p class="text-center text-secondary small mt-5">© 2026 talk caffe • Dibuat untuk pelayanan terbaik</p>
    </form></section></main><script>lucide.createIcons();</script>
</body>
</html>