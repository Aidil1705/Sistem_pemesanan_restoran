<?php
$dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['date_from'] ?? ''))
    ? $_GET['date_from']
    : null;
$dateTo = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['date_to'] ?? ''))
    ? $_GET['date_to']
    : null;
$search = trim((string) ($_GET['q'] ?? ''));
$orders = $orderModel->history($dateFrom, $dateTo, $search);
$totalValue = array_sum(array_map(static fn ($order) => (int) $order['total'], $orders));
$doneCount = count(array_filter(
    $orders,
    static fn ($order) => in_array($order['status'], ['done', 'closed'], true)
));
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
    <div>
        <h1 class="display-6">Riwayat Pemesanan</h1>
        <p class="lead text-secondary">Telusuri pesanan dan siapkan laporan untuk pengelola.</p>
    </div>
    <a href="index.php?page=pdf" class="btn btn-primary btn-lg">
        <i data-lucide="file-down" width="18" class="me-2"></i>Ekspor PDF
    </a>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <article class="card soft-card h-100 p-4">
            <span class="text-secondary">Jumlah pesanan</span>
            <strong class="display-6 mt-2"><?= count($orders) ?></strong>
            <small class="text-secondary">Pesanan sesuai filter</small>
        </article>
    </div>
    <div class="col-md-4">
        <article class="card soft-card h-100 p-4">
            <span class="text-secondary">Nilai seluruh pesanan</span>
            <strong class="display-6 mt-2"><?= rupiah($totalValue) ?></strong>
            <small class="text-secondary">Termasuk pesanan aktif</small>
        </article>
    </div>
    <div class="col-md-4">
        <article class="card soft-card h-100 p-4">
            <span class="text-secondary">Pesanan selesai</span>
            <strong class="display-6 mt-2"><?= $doneCount ?></strong>
            <small class="text-secondary">Dari hasil pencarian</small>
        </article>
    </div>
</div>

<form method="get" class="row g-3 mt-2">
    <input type="hidden" name="page" value="history">
    <div class="col-lg-3">
        <label class="form-label small fw-semibold">Dari tanggal</label>
        <input type="date" name="date_from" value="<?= e($dateFrom ?? '') ?>" class="form-control">
    </div>
    <div class="col-lg-3">
        <label class="form-label small fw-semibold">Sampai tanggal</label>
        <input type="date" name="date_to" value="<?= e($dateTo ?? '') ?>" class="form-control">
    </div>
    <div class="col-lg-4">
        <label class="form-label small fw-semibold">Cari pesanan</label>
        <div class="input-group">
            <span class="input-group-text bg-white"><i data-lucide="search" width="18"></i></span>
            <input name="q" value="<?= e($search) ?>" class="form-control" placeholder="Nama pembeli atau nomor pesanan...">
        </div>
    </div>
    <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary flex-grow-1">
            <i data-lucide="filter" width="16" class="me-1"></i>Filter
        </button>
        <a href="index.php?page=history" class="btn btn-outline-secondary" title="Reset filter">
            <i data-lucide="rotate-ccw" width="16"></i>
        </a>
    </div>
</form>

<section class="card border-0 shadow-sm mt-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light small text-uppercase">
                <tr>
                    <th>Pembeli</th>
                    <th>Meja</th>
                    <th>No. pesanan</th>
                    <th>Waktu</th>
                    <th>Item pesanan</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php $meta = statusMeta($order['status']); ?>
                    <tr>
                        <td class="fw-semibold"><?= e($order['customer_name']) ?></td>
                        <td><?= str_pad((string) $order['table_number'], 2, '0', STR_PAD_LEFT) ?></td>
                        <td class="text-success"><?= e($order['order_number']) ?></td>
                        <td>
                            <span class="d-block text-secondary"><?= date('d M Y', strtotime($order['created_at'])) ?></span>
                            <strong><?= date('H.i', strtotime($order['created_at'])) ?></strong>
                        </td>
                        <td class="text-secondary" style="white-space: pre-line"><?= e($order['item_summary']) ?></td>
                        <td class="fw-semibold"><?= rupiah((int) $order['total']) ?></td>
                        <td><span class="badge rounded-pill <?= $meta[1] ?>">• <?= $meta[0] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between p-3 text-secondary">
        <span>Menampilkan <?= count($orders) ?> pesanan</span>
        <span class="badge bg-success-subtle text-success-emphasis">1</span>
    </div>
</section>