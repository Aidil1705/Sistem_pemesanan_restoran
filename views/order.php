<?php
$menus = $menuModel->visible();
$emptyTables = $tableModel->emptyTables();
?>

<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="display-6">Pemesanan</h1>
        <p class="lead text-secondary">Catat pembeli, pilih hidangan, lalu simpan pesanan.</p>
    </div>
    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis fs-6 p-3">Draf #DD-1049</span>
</div>

<form method="post" id="orderForm">
    <input type="hidden" name="action" value="create_order">
    <div class="row g-4">
        <section class="col-xl-8">
            <div class="card soft-card border-0 p-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Nama pembeli
                            <input name="customer_name" type="text" required class="form-control form-control-lg">
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Nomor meja
                            <select name="table_id" required class="form-select form-select-lg">
                                <option value="">Pilih meja kosong</option>
                                <?php foreach ($emptyTables as $table): ?>
                                    <option value="<?= (int) $table['id'] ?>">
                                        Meja <?= str_pad((string) $table['number'], 2, '0', STR_PAD_LEFT) ?> • Kosong
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                </div>
            </div>

            <div class="input-group mt-4">
                <span class="input-group-text bg-white"><i data-lucide="search" width="18"></i></span>
                <input id="menuSearch" type="search" class="form-control form-control-lg" placeholder="Cari nama makanan atau minuman...">
            </div>
            <div class="btn-group mt-3" role="group" aria-label="Filter kategori menu">
                <button type="button" data-category="all" class="btn btn-success">Semua</button>
                <button type="button" data-category="Makanan" class="btn btn-outline-secondary">Makanan</button>
                <button type="button" data-category="Minuman" class="btn btn-outline-secondary">Minuman</button>
                <button type="button" data-category="Camilan" class="btn btn-outline-secondary">Camilan</button>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <h2 class="h4">Pilihan dari dapur kami</h2>
                <span id="menuResultCount" class="text-secondary"><?= count($menus) ?> menu tersedia</span>
            </div>
            <div id="menuGrid" class="row g-3 mt-1">
                <?php foreach ($menus as $menu): ?>
                    <div class="col-md-6 col-xxl-4" data-menu-card data-category="<?= e($menu['category']) ?>" data-menu-name="<?= e(strtolower($menu['name'])) ?>">
                        <article class="card h-100 overflow-hidden">
                            <img src="<?= e($menu['image_url']) ?>" alt="<?= e($menu['name']) ?>" class="object-fit-cover" height="140">
                            <div class="card-body">
                                <small class="text-secondary"><?= e($menu['category']) ?></small>
                                <h3 class="h6 mt-2"><?= e($menu['name']) ?></h3>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <span class="text-success"><?= rupiah((int) $menu['price']) ?></span>
                                    <button type="button" data-add-menu="<?= (int) $menu['id'] ?>" data-name="<?= e($menu['name']) ?>" data-price="<?= (int) $menu['price'] ?>" class="btn btn-sm btn-success-subtle text-success-emphasis">
                                        <i data-lucide="plus" width="15"></i> Tambah
                                    </button>
                                </div>
                                <input type="hidden" name="items[<?= (int) $menu['id'] ?>]" value="0" data-qty="<?= (int) $menu['id'] ?>">
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <p id="emptyMenuMessage" class="alert alert-light border d-none mt-3">Menu tidak ditemukan.</p>
        </section>

        <aside class="col-xl-4">
            <div class="card soft-card border-0 p-4 sticky-xl-top" style="top: 90px">
                <div class="d-flex justify-content-between mb-3">
                    <h2 class="h4">Rangkuman pesanan</h2>
                    <i data-lucide="archive" class="text-success"></i>
                </div>
                <div class="d-flex justify-content-between bg-success-subtle text-success-emphasis rounded p-3 fw-semibold">
                    <span id="summaryCustomer"></span>
                    <span id="summaryTable">Meja dipilih</span>
                </div>
                <div id="cartItems" class="vstack gap-3 mt-3"></div>
                <div class="border-top pt-3 mt-4">
                    <div class="d-flex justify-content-between text-secondary">
                        <span>Subtotal • <span id="itemCount">0</span> item</span>
                        <strong id="subtotal" class="text-dark">Rp0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <span class="h5">Total</span>
                        <strong id="total" class="fs-2 text-success">Rp0</strong>
                    </div>
                    <small class="text-secondary">Harga akhir, tanpa biaya tambahan.</small>
                </div>
                <p class="alert alert-warning small mt-3"><i data-lucide="clock" width="16" class="me-1"></i>Pesanan baru akan berstatus Menunggu.</p>
                <button class="btn btn-primary btn-lg w-100"><i data-lucide="check" width="18" class="me-1"></i>Simpan pesanan</button>
            </div>
        </aside>
    </div>
</form>

<script>
    const cart = new Map();
    const menuSearch = document.getElementById('menuSearch');
    const menuCards = [...document.querySelectorAll('[data-menu-card]')];
    const categoryButtons = [...document.querySelectorAll('[data-category]')];
    let activeCategory = 'all';

    const format = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value).replace('IDR', 'Rp');

    const applyMenuFilter = () => {
        const query = menuSearch.value.trim().toLowerCase();
        let visibleCount = 0;

        menuCards.forEach((card) => {
            const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
            const matchesSearch = card.dataset.menuName.includes(query);
            const visible = matchesCategory && matchesSearch;

            card.classList.toggle('d-none', !visible);
            if (visible) {
                visibleCount++;
            }
        });

        document.getElementById('menuResultCount').textContent = `${visibleCount} menu tersedia`;
        document.getElementById('emptyMenuMessage').classList.toggle('d-none', visibleCount > 0);
    };

    categoryButtons.forEach((button) => button.addEventListener('click', () => {
        activeCategory = button.dataset.category.toLowerCase() === 'all' ? 'all' : button.dataset.category;
        categoryButtons.forEach((item) => item.classList.toggle('btn-success', item === button));
        categoryButtons.forEach((item) => item.classList.toggle('btn-outline-secondary', item !== button));
        applyMenuFilter();
    }));

    menuSearch.addEventListener('input', applyMenuFilter);

    const renderCart = () => {
        const target = document.getElementById('cartItems');
        let total = 0;
        let count = 0;
        target.innerHTML = '';

        cart.forEach((item, id) => {
            total += item.price * item.qty;
            count += item.qty;
            document.querySelector(`[data-qty="${id}"]`).value = item.qty;
            target.insertAdjacentHTML('beforeend', `
                <div>
                    <div class="d-flex justify-content-between">
                        <strong>${item.name}</strong>
                        <button type="button" data-remove="${id}" class="btn btn-link btn-sm text-secondary p-0">
                            <i data-lucide="trash-2" width="15"></i>
                        </button>
                    </div>
                    <small class="text-secondary">${format(item.price)} / porsi</small>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="btn-group btn-group-sm">
                            <button type="button" data-minus="${id}" class="btn btn-outline-secondary">−</button>
                            <span class="btn btn-light">${item.qty}</span>
                            <button type="button" data-plus="${id}" class="btn btn-outline-success">+</button>
                        </div>
                        <strong>${format(item.price * item.qty)}</strong>
                    </div>
                </div>
            `);
        });

        document.getElementById('subtotal').textContent = format(total);
        document.getElementById('total').textContent = format(total);
        document.getElementById('itemCount').textContent = count;
        lucide.createIcons();
    };

    document.querySelector('[name="customer_name"]').addEventListener('input', (event) => {
        document.getElementById('summaryCustomer').textContent = event.target.value;
    });

    document.addEventListener('click', (event) => {
        const add = event.target.closest('[data-add-menu]');
        const plus = event.target.closest('[data-plus]');
        const minus = event.target.closest('[data-minus]');
        const remove = event.target.closest('[data-remove]');

        if (add) {
            const id = add.dataset.addMenu;
            const item = cart.get(id) || {
                name: add.dataset.name,
                price: Number(add.dataset.price),
                qty: 0,
            };
            item.qty++;
            cart.set(id, item);
        }
        if (plus) cart.get(plus.dataset.plus).qty++;
        if (minus) {
            const item = cart.get(minus.dataset.minus);
            item.qty--;
            if (item.qty <= 0) cart.delete(minus.dataset.minus);
        }
        if (remove) cart.delete(remove.dataset.remove);
        if (add || plus || minus || remove) renderCart();
    });
</script>