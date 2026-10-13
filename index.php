<?php

declare(strict_types=1);

session_save_path(__DIR__ . '/logs/sessions');
session_start();

require __DIR__ . '/vendor/autoload.php';

use App\Auth;
use App\Database;
use App\MenuItem;
use App\Order;
use App\Report;
use App\Table;

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function rupiah(int|float $value): string
{
    return 'Rp' . number_format((int) $value, 0, ',', '.');
}

function statusMeta(?string $status): array
{
    return match ($status) {
        'waiting' => ['Menunggu', 'bg-warning-subtle text-warning-emphasis', 'border-warning-subtle bg-warning-subtle', 'clock'],
        'cooking' => ['Sedang dibuat', 'bg-info-subtle text-info-emphasis', 'border-info-subtle bg-info-subtle', 'chef-hat'],
        'done' => ['Selesai', 'bg-success-subtle text-success-emphasis', 'border-success-subtle bg-success-subtle', 'check-circle-2'],
        'closed' => ['Kosong', 'bg-secondary-subtle text-secondary-emphasis', 'border-secondary-subtle bg-secondary-subtle', 'lock'],
        default => ['Kosong', 'bg-secondary-subtle text-secondary-emphasis', 'border-secondary-subtle bg-secondary-subtle', 'bed-double'],
    };
}

function redirectTo(string $page, array $params = []): never
{
    $query = http_build_query(['page' => $page] + $params);
    header('Location: index.php?' . $query);
    exit;
}

try {
    $pdo = Database::getConnection();
} catch (Throwable $exception) {
    http_response_code(500);
    echo '<h1>Database belum siap</h1><p>Pastikan MySQL Laragon aktif. Detail dicatat di logs/error.log.</p>';
    exit;
}

$auth = new Auth($pdo);
$menuModel = new MenuItem($pdo);
$tableModel = new Table($pdo);
$orderModel = new Order($pdo);
$page = $_GET['page'] ?? ($auth->user() ? 'dashboard' : 'login');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'login':
                if ($auth->attempt((string) ($_POST['identity'] ?? ''), (string) ($_POST['password'] ?? ''))) {
                    redirectTo('dashboard');
                }
                $error = 'Email atau kata sandi belum cocok.';
                $page = 'login';
                break;
            case 'create_account':
                $auth->requireLogin();
                $name = trim((string) ($_POST['name'] ?? ''));
                $username = trim((string) ($_POST['username'] ?? ''));
                $email = trim((string) ($_POST['email'] ?? ''));
                $password = (string) ($_POST['password'] ?? '');
                $role = trim((string) ($_POST['role'] ?? 'Pegawai')) ?: 'Pegawai';

                if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException('Nama, username, dan email yang valid wajib diisi.');
                }
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('Kata sandi harus terdiri dari minimal 8 karakter.');
                }

                $auth->createEmployee(compact('name', 'username', 'email', 'password', 'role'));
                redirectTo('account', ['created' => 1]);
            case 'logout':
                $auth->logout();
                redirectTo('login');
            case 'create_order':
                $items = array_map('intval', $_POST['items'] ?? []);
                $orderModel->create((string) ($_POST['customer_name'] ?? ''), (int) ($_POST['table_id'] ?? 0), $items);
                redirectTo('dashboard');
            case 'create_menu':
                $image = $_FILES['image'] ?? null;
                if (!is_array($image) || ($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    throw new InvalidArgumentException('Gambar menu wajib diunggah.');
                }
                if (($image['size'] ?? 0) > 5 * 1024 * 1024) {
                    throw new InvalidArgumentException('Ukuran gambar maksimal 5 MB.');
                }

                $imageInfo = @getimagesize((string) $image['tmp_name']);
                $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
                $mimeType = $imageInfo['mime'] ?? '';
                if ($imageInfo === false || !isset($allowedMimeTypes[$mimeType])) {
                    throw new InvalidArgumentException('Format gambar harus JPG, PNG, WEBP, atau GIF.');
                }

                $uploadDirectory = __DIR__ . '/public/assets';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    throw new RuntimeException('Folder upload gambar tidak dapat dibuat.');
                }
                $filename = 'menu-' . bin2hex(random_bytes(12)) . '.' . $allowedMimeTypes[$mimeType];
                if (!move_uploaded_file((string) $image['tmp_name'], $uploadDirectory . '/' . $filename)) {
                    throw new RuntimeException('Gambar menu gagal disimpan.');
                }

                $menuModel->create(array_merge($_POST, ['image_url' => 'public/assets/' . $filename]));
                redirectTo('menu');
            case 'toggle_menu':
                $menuModel->setAvailability((int) $_POST['id'], (bool) ($_POST['available'] ?? false));
                redirectTo('menu');
            case 'update_status':
                $orderModel->updateStatus((int) $_POST['order_id'], (string) $_POST['status']);
                redirectTo('dashboard', ['order' => (int) $_POST['order_id']]);
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

if ($page === 'pdf') {
    $auth->requireLogin();
    (new Report($pdo))->streamHistory($orderModel->history());
    exit;
}

if ($page === 'login') {
    require __DIR__ . '/views/login.php';
    exit;
}

$auth->requireLogin();
$user = $auth->user();
$activePage = $page;

require __DIR__ . '/views/layout/header.php';

match ($page) {
    'account' => require __DIR__ . '/views/account.php',
    'order' => require __DIR__ . '/views/order.php',
    'menu' => require __DIR__ . '/views/menu.php',
    'history' => require __DIR__ . '/views/history.php',
    default => require __DIR__ . '/views/dashboard.php',
};

require __DIR__ . '/views/layout/footer.php';
