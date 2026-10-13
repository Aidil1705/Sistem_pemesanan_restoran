<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>talk caffe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        :root { --brand: #356b4c; --brand-dark: #2d5b41; --shell: #f7f6f2; --ink: #263a31; }
        body { font-family: Inter, system-ui, sans-serif; background: var(--shell); color: var(--ink); }
        .sidebar { width: 260px; }
        @media (min-width: 992px) { .ms-lg-260 { margin-left: 260px; } }
        .brand-logo { width: 44px; height: 44px; object-fit: contain; }
        .avatar { width: 40px; height: 40px; }
        .soft-card { border: 1px solid #deded5; background: rgba(255,255,255,.78); }
        .btn-primary { --bs-btn-bg: var(--brand); --bs-btn-border-color: var(--brand); --bs-btn-hover-bg: var(--brand-dark); --bs-btn-hover-border-color: var(--brand-dark); }
        .text-brand { color: var(--brand) !important; }
        .sidebar .nav-link { color: #78716c; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #065f46; background: #ecfdf5; }
        .page-content > .d-flex > .btn-lg { font-size: 1rem; padding: .375rem .75rem; border-radius: var(--bs-border-radius); }
        .object-fit-cover { object-fit: cover; }
        .page-content { min-height: calc(100vh - 73px); }
        .table > :not(caption) > * > * { padding: 1rem; }
        .modal-backdrop-custom { background: rgba(38,58,49,.45); }
        .order-drawer { width: min(520px, 100%); height: 100vh; max-height: 100vh; }
    </style>
</head>
<body>
<div class="d-flex min-vh-100">
    <aside class="sidebar position-fixed top-0 bottom-0 start-0 d-none d-lg-flex flex-column border-end bg-white px-4 py-4">
        <a href="index.php?page=dashboard" class="d-flex align-items-center gap-3 text-decoration-none text-dark">
            <img src="public/assets/Talk%20Caffe%20Chat%20Cup%20Logo.png" class="brand-logo rounded-3" alt="Logo talk caffe">
            <span class="fs-5 fw-semibold">Talk Caffe</span>
        </a>

        <nav class="nav flex-column gap-1 mt-5">
            <p class="mb-2 text-uppercase small fw-bold text-secondary">Menu</p>
            <?php $currentPage = $_GET['page'] ?? 'dashboard'; ?>
            <?php $nav = [['dashboard','layout-dashboard','Dashboard'],['order','receipt-text','Pemesanan'],['menu','utensils','Menu'],['history','history','Riwayat'],['account','user-plus','Tambah akun']]; ?>
            <?php foreach ($nav as [$key, $icon, $label]): ?>
                <a href="index.php?page=<?= $key ?>" class="nav-link d-flex align-items-center gap-3 rounded-2 px-3 py-2 <?= $currentPage === $key ? 'active' : '' ?>">
                    <i data-lucide="<?= $icon ?>" width="19"></i><?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="mt-auto border-top pt-3">
            <form method="post" class="d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="logout">
                <?php $sidebarUser = $_SESSION['employee'] ?? ['name' => '', 'role' => '']; ?>
                <?php $initials = strtoupper(substr(implode('', array_map(static fn (string $part): string => $part[0], preg_split('/\s+/', trim($sidebarUser['name'])) ?: [])), 0, 2)); ?>
                <span class="avatar d-flex align-items-center justify-content-center rounded-circle bg-secondary-subtle small"><?= e($initials) ?></span>
                <span class="flex-grow-1"><strong class="d-block text-truncate"><?= e($sidebarUser['name']) ?></strong><small class="d-block text-secondary text-truncate"><?= e($sidebarUser['role']) ?></small></span>
                <button type="submit" title="Keluar" class="btn btn-link p-0 text-secondary"><i data-lucide="log-out" width="17"></i></button>
            </form>
        </div>
    </aside>

    <main class="flex-grow-1 ms-lg-260">
        <section class="page-content container-fluid px-4 px-lg-5 py-4 py-lg-5">
            <?php if ($error): ?>
                <div class="alert alert-danger mb-4"><?= e($error) ?></div>
            <?php endif; ?>
