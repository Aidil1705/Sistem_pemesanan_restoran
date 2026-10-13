<?php
$visibility = $_GET['visibility'] ?? null;
$search = $_GET['q'] ?? null;
$menus = $menuModel->all($search, $visibility);
$counts = $menuModel->counts();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
    <div>
        <h1 class="display-6">Menu</h1>
        <p class="lead text-secondary">Kelola hidangan, harga, dan ketersediaan untuk tamu Anda.</p>
    </div>
    <button data-modal-target="#menuModal" class="btn btn-primary">
        <i data-lucide="plus" width="17" class="me-1"></i>Tambah menu
    </button>
</div>

<div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
    <form class="input-group" style="max-width: 420px">
        <input type="hidden" name="page" value="menu">
        <span class="input-group-text bg-white"><i data-lucide="search" width="18"></i></span>
        <input name="q" value="<?= e($search ?? '') ?>" placeholder="Cari nama menu..." class="form-control">
    </form>
    <div class="btn-group">
        <a class="btn <?= !$visibility ? 'btn-success' : 'btn-outline-secondary' ?>" href="index.php?page=menu">Semua (<?= $counts['all'] ?>)</a>
        <a class="btn <?= $visibility === 'available' ? 'btn-success' : 'btn-outline-secondary' ?>" href="index.php?page=menu&visibility=available">Tampil (<?= $counts['available'] ?>)</a>
        <a class="btn <?= $visibility === 'hidden' ? 'btn-success' : 'btn-outline-secondary' ?>" href="index.php?page=menu&visibility=hidden">Disembunyikan (<?= $counts['hidden'] ?>)</a>
    </div>
</div>

<section class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Visibilitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= e($menu['image_url']) ?>" class="rounded-2 object-fit-cover" width="64" height="64" alt="">
                                <div>
                                    <strong><?= e($menu['name']) ?></strong>
                                    <p class="small text-secondary mb-0"><?= e($menu['description']) ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary"><?= e($menu['category']) ?></td>
                        <td class="fw-semibold"><?= rupiah((int) $menu['price']) ?></td>
                        <td>
                            <span class="badge rounded-pill <?= $menu['is_available'] ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?>">
                                • <?= $menu['is_available'] ? 'Tampil' : 'Disembunyikan' ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-3">
                                <button class="btn btn-link p-0 text-success">
                                    <i data-lucide="pencil" width="16"></i>Edit
                                </button>
                                <form method="post">
                                    <input type="hidden" name="action" value="toggle_menu">
                                    <input type="hidden" name="id" value="<?= (int) $menu['id'] ?>">
                                    <input type="hidden" name="available" value="<?= $menu['is_available'] ? '0' : '1' ?>">
                                    <button class="btn btn-link p-0 text-secondary">
                                        <i data-lucide="<?= $menu['is_available'] ? 'eye-off' : 'eye' ?>" width="16"></i>
                                        <?= $menu['is_available'] ? 'Sembunyikan' : 'Tampilkan' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between p-3 text-secondary">
        <span>Menampilkan <?= count($menus) ?> dari <?= $counts['all'] ?> menu</span>
        <span class="badge bg-success-subtle text-success-emphasis">1</span>
    </div>
</section>

<p class="alert alert-success mt-4">
    <i data-lucide="info" width="18" class="me-2"></i>
    Menu habis? Pilih Sembunyikan. Menu yang disembunyikan tidak muncul di halaman Pemesanan, tetapi tetap tersimpan di sini.
</p>

<div id="menuModal" data-modal class="d-none position-fixed top-0 start-0 end-0 bottom-0 modal-backdrop-custom p-3" style="z-index:1050">
    <form method="post" enctype="multipart/form-data" class="card mx-auto mt-2 p-3 shadow" style="max-width: 540px">
        <input type="hidden" name="action" value="create_menu">
        <div class="d-flex justify-content-between border-bottom pb-2">
            <div>
                <h2 class="h4 mb-1">Tambah menu</h2>
                <p class="text-secondary small mb-0">Tambahkan hidangan baru.</p>
            </div>
            <button type="button" data-modal-close class="btn btn-link text-success p-0"><i data-lucide="x"></i></button>
        </div>
        <div class="row g-2 pt-3">
            <div class="col-12">
                <label class="form-label fw-semibold mb-1">Gambar makanan *</label>
                <input name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control" required>
                <small class="form-text text-secondary">JPG, PNG, WEBP, atau GIF. Maksimal 5 MB.</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold mb-1">Nama menu *</label>
                <input name="name" required class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Kategori *</label>
                <select name="category" class="form-select">
                    <option>Makanan</option>
                    <option>Minuman</option>
                    <option>Camilan</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold mb-1">Harga (Rp) *</label>
                <input name="price" type="number" min="0" required class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold mb-1">Deskripsi</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between bg-success-subtle rounded p-2">
                    <label class="form-check-label" for="menuAvailable">
                        <strong class="d-block">Tampilkan di katalog pemesanan</strong>
                        <small class="text-secondary">Menu dapat langsung ditambahkan ke pesanan.</small>
                    </label>
                    <div class="form-check form-switch mb-0">
                        <input id="menuAvailable" name="is_available" type="checkbox" checked class="form-check-input">
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 border-top pt-2 mt-3">
            <button type="button" data-modal-close class="btn btn-outline-secondary">Batal</button>
            <button class="btn btn-primary"><i data-lucide="check" width="16" class="me-1"></i>Simpan menu</button>
        </div>
    </form>
</div>