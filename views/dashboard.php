<?php
$tables = $tableModel->allWithLatestOrder();
$activeOrders = $orderModel->active();
$stats = $orderModel->stats();
$selectedOrder = isset($_GET['order'])
    ? $orderModel->findWithItems((int) $_GET['order'])
    : null;
$selectedActivities = $selectedOrder
    ? $orderModel->activities((int) $selectedOrder['id'])
    : [];
$emptyCount = count(array_filter($tables, static fn ($table) => $table['order_id'] === null));
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
    <div>
        <h1 class="display-6">Dashboard</h1>
        <p class="lead text-secondary">Selamat siang, Ayu. Mari buat pelayanan hari ini lebih hangat.</p>
    </div>
    <a href="index.php?page=order" class="btn btn-primary btn-lg">
        <i data-lucide="plus" width="18" class="me-2"></i>Buat pesanan
    </a>
</div>

<?php
$statCards = [
    ['Meja kosong', $emptyCount, 'dari 12 meja tersedia', 'bed-double', 'bg-secondary-subtle text-secondary-emphasis'],
    ['Menunggu', $stats['waiting'], 'pesanan perlu diproses', 'clock', 'bg-warning-subtle text-warning-emphasis'],
    ['Sedang dibuat', $stats['cooking'], 'pesanan di dapur', 'chef-hat', 'bg-info-subtle text-info-emphasis'],
    ['Selesai', $stats['done'], 'meja perlu dikonfirmasi', 'check-circle-2', 'bg-success-subtle text-success-emphasis'],
];
?>
<div class="row g-3">
    <?php foreach ($statCards as $card): ?>
        <div class="col-md-6 col-xl-3">
            <article class="card soft-card h-100 p-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="rounded-3 p-3 <?= $card[4] ?>"><i data-lucide="<?= $card[3] ?>" width="24"></i></span>
                    <div>
                        <span class="d-block text-secondary"><?= $card[0] ?></span>
                        <strong class="fs-2"><?= $card[1] ?></strong>
                        <small class="d-block text-secondary"><?= $card[2] ?></small>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mt-2">
    <section class="col-xl-8">
        <div class="card soft-card h-100 p-4">
            <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                <div>
                    <h2 class="h4">Ruang makan utama</h2>
                    <p class="text-secondary mb-0">Pilih meja aktif untuk melihat pesanan.</p>
                </div>
                <span class="text-secondary">12 meja</span>
            </div>
            <div class="row g-3">
                <?php foreach ($tables as $table): ?>
                    <?php $meta = statusMeta($table['order_status']); ?>
                    <div class="col-md-6 col-xxl-4">
                        <a href="<?= $table['order_id'] ? 'index.php?page=dashboard&order=' . (int) $table['order_id'] : '#' ?>" class="d-block text-decoration-none text-dark border rounded-3 p-3 h-100 <?= $table['order_id'] ? '' : 'opacity-50 pe-none' ?>">
                            <div class="d-flex justify-content-between">
                                <h3 class="h6"><i data-lucide="bed-double" width="17" class="me-1"></i>Meja <?= str_pad((string) $table['number'], 2, '0', STR_PAD_LEFT) ?></h3>
                                <i data-lucide="<?= $table['order_id'] ? 'arrow-up-right' : 'lock' ?>" width="16"></i>
                            </div>
                            <span class="badge rounded-pill <?= $meta[1] ?> mt-2">• <?= $meta[0] ?></span>
                            <div class="d-flex justify-content-between text-secondary small mt-4">
                                <span><?= $table['customer_name'] ? e($table['customer_name']) . ' • ' . (int) $table['item_count'] . ' item' : 'Belum ada pesanan' ?></span>
                                <span><?= $table['created_at'] ? date('H.i', strtotime($table['created_at'])) : '' ?></span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="text-secondary small mt-3 mb-0"><i data-lucide="info" width="16" class="me-1"></i>Meja kosong tidak dapat dipilih. Buat pesanan melalui Pemesanan.</p>
        </div>
    </section>

    <aside class="col-xl-4">
        <div class="card soft-card h-100 p-4">
            <div class="d-flex justify-content-between mb-3">
                <div><h2 class="h4">Pesanan aktif</h2><p class="text-secondary mb-0">Urut dari pesanan terbaru</p></div>
                <span class="badge bg-success-subtle text-success-emphasis align-self-start fs-6"><?= count($activeOrders) ?></span>
            </div>
            <div class="vstack gap-3">
                <?php foreach ($activeOrders as $order): ?>
                    <?php $meta = statusMeta($order['status']); ?>
                    <article class="border rounded-3 p-3">
                        <div class="d-flex justify-content-between">
                            <h3 class="h6">Meja <?= str_pad((string) $order['table_number'], 2, '0', STR_PAD_LEFT) ?> • <?= e($order['customer_name']) ?></h3>
                            <small class="text-secondary"><?= date('H.i', strtotime($order['created_at'])) ?></small>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <div><small class="text-secondary d-block"><?= e($order['order_number']) ?> • <?= (int) $order['item_count'] ?> item</small><span class="badge rounded-pill <?= $meta[1] ?> mt-2">• <?= $meta[0] ?></span></div>
                            <strong><?= rupiah((int) $order['total']) ?></strong>
                        </div>
                        <a class="d-inline-flex align-items-center gap-1 small fw-semibold text-success mt-3" href="index.php?page=dashboard&order=<?= (int) $order['id'] ?>">
                            <?= $order['status'] === 'done' ? 'Kosongkan' : 'Lihat pesanan' ?><i data-lucide="chevron-right" width="15"></i>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="bg-light rounded-3 p-3 text-secondary small mt-3 mb-0"><i data-lucide="refresh-cw" width="16" class="me-1 text-success"></i>Perbarui status melalui detail pesanan sesuai progres pelayanan.</p>
        </div>
    </aside>
</div>

<?php if ($selectedOrder): ?>
    <?php $selectedMeta = statusMeta($selectedOrder['status']); ?>
    <div class="position-fixed top-0 start-0 end-0 bottom-0 d-flex justify-content-end modal-backdrop-custom" style="z-index:1050">
        <aside class="order-drawer bg-white h-100 overflow-auto p-4 shadow">
            <div class="d-flex justify-content-between">
                <div><small class="text-uppercase text-secondary">Detail pesanan</small><h2 class="h3 mt-2">Meja <?= str_pad((string) $selectedOrder['table_number'], 2, '0', STR_PAD_LEFT) ?></h2></div>
                <a href="index.php?page=dashboard" class="text-success"><i data-lucide="x"></i></a>
            </div>
            <div class="bg-light rounded-3 p-3 mt-3">
                <div class="d-flex justify-content-between"><h3 class="h5"><?= e($selectedOrder['customer_name']) ?></h3><span class="badge rounded-pill <?= $selectedMeta[1] ?>"><?= $selectedMeta[0] ?></span></div>
                <div class="d-flex justify-content-between text-secondary small mt-3"><span><?= e($selectedOrder['order_number']) ?></span><span><?= date('d M Y • H.i', strtotime($selectedOrder['created_at'])) ?></span></div>
            </div>
            <div class="d-flex justify-content-between mt-4"><h3 class="h5">Item pesanan</h3><span class="text-secondary"><?= count($selectedOrder['items']) ?> item</span></div>
            <div class="vstack gap-3 mt-3">
                <?php foreach ($selectedOrder['items'] as $item): ?>
                    <div class="d-flex justify-content-between"><div><span class="badge bg-success-subtle text-success-emphasis me-2"><?= (int) $item['quantity'] ?>×</span><?= e($item['name']) ?><small class="d-block text-secondary ms-5"><?= rupiah((int) $item['unit_price']) ?> / porsi</small></div><strong><?= rupiah((int) $item['subtotal']) ?></strong></div>
                <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between border-top border-bottom py-3 mt-4"><strong>Total pesanan</strong><strong class="fs-3 text-success"><?= rupiah((int) $selectedOrder['total']) ?></strong></div>
            <h3 class="h5 mt-4">Perbarui status pesanan</h3><p class="text-secondary small">Pilih status sesuai progres di dapur.</p>
            <form method="post" class="vstack gap-2"><input type="hidden" name="action" value="update_status"><input type="hidden" name="order_id" value="<?= (int) $selectedOrder['id'] ?>"><?php foreach ([['waiting','Menunggu'],['cooking','Sedang dibuat'],['done','Selesai']] as [$value, $label]): ?><label class="form-check border rounded-2 p-2"><input class="form-check-input ms-0 me-2" type="radio" name="status" value="<?= $value ?>" <?= $selectedOrder['status'] === $value ? 'checked' : '' ?>><?= $label ?></label><?php endforeach; ?><button class="btn btn-primary"><i data-lucide="check" width="16" class="me-1"></i>Simpan perubahan</button></form>
            <?php if ($selectedOrder['status'] === 'done'): ?><form method="post" class="mt-2"><input type="hidden" name="action" value="update_status"><input type="hidden" name="order_id" value="<?= (int) $selectedOrder['id'] ?>"><input type="hidden" name="status" value="closed"><button class="btn btn-outline-success w-100">Ya, kosongkan meja</button></form><?php endif; ?>
            <div class="bg-light rounded-3 p-3 mt-4"><small class="text-uppercase text-secondary d-block mb-2">Aktivitas terakhir</small><?php foreach ($selectedActivities as $activity): ?><p class="small text-secondary mb-1"><?= date('H.i', strtotime($activity['created_at'])) ?> • <?= e($activity['message']) ?></p><?php endforeach; ?></div>
        </aside>
    </div>
<?php endif; ?>