<div class="mb-4">
    <h1 class="display-6">Tambah akun</h1>
    <p class="lead text-secondary">Buat akun pegawai baru untuk mengakses ruang kerja restoran.</p>
</div>

<?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">Akun berhasil dibuat.</div>
<?php endif; ?>

<form method="post" class="card border-0 shadow-sm p-4" style="max-width: 720px">
    <input type="hidden" name="action" value="create_account">

    <div class="row g-4">
        <div class="col-12">
            <label class="form-label fw-semibold" for="name">Nama lengkap</label>
            <input id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" class="form-control form-control-lg" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="username">Username</label>
            <input id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" class="form-control form-control-lg" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($_POST['email'] ?? '') ?>" class="form-control form-control-lg" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="password">Kata sandi</label>
            <input id="password" name="password" type="password" class="form-control form-control-lg" minlength="8" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="role">Jabatan</label>
            <input id="role" name="role" value="<?= e($_POST['role'] ?? 'Pegawai') ?>" class="form-control form-control-lg" required>
        </div>
    </div>

    <button class="btn btn-primary mt-4">
        <i data-lucide="user-plus" width="18" class="me-2"></i>Simpan akun
    </button>
</form>