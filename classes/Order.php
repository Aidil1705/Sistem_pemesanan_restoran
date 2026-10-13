<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;

/**
 * Order model for operational transactions.
 */
class Order extends BaseModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function active(): array
    {
        return $this->pdo->query(
            "SELECT orders.*, restaurant_tables.number table_number,
                    (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = orders.id) item_count
             FROM orders
             JOIN restaurant_tables ON restaurant_tables.id = orders.table_id
             WHERE orders.status IN ('waiting', 'cooking', 'done')
             ORDER BY orders.created_at DESC"
        )->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function history(?string $dateFrom = null, ?string $dateTo = null, ?string $search = null): array
    {
        $conditions = [];
        $params = [];

        if ($dateFrom !== null && $dateFrom !== '') {
            $conditions[] = 'DATE(orders.created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null && $dateTo !== '') {
            $conditions[] = 'DATE(orders.created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }
        if ($search !== null && $search !== '') {
            $conditions[] = '(orders.customer_name LIKE :search_customer OR orders.order_number LIKE :search_order)';
            $params['search_customer'] = '%' . $search . '%';
            $params['search_order'] = '%' . $search . '%';
        }

        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);
        $statement = $this->pdo->prepare(
            "SELECT orders.*, restaurant_tables.number table_number,
                    GROUP_CONCAT(CONCAT(order_items.quantity, 'x ', menu_items.name) SEPARATOR '\n') item_summary
             FROM orders
             JOIN restaurant_tables ON restaurant_tables.id = orders.table_id
             JOIN order_items ON order_items.order_id = orders.id
             JOIN menu_items ON menu_items.id = order_items.menu_item_id
             {$where}
             GROUP BY orders.id
             ORDER BY orders.created_at DESC"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        $rows = $this->pdo->query("SELECT status, COUNT(*) total FROM orders WHERE status IN ('waiting', 'cooking', 'done') GROUP BY status")->fetchAll();
        $stats = ['waiting' => 0, 'cooking' => 0, 'done' => 0];
        foreach ($rows as $row) {
            $stats[$row['status']] = (int) $row['total'];
        }

        return $stats;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findWithItems(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT orders.*, restaurant_tables.number table_number FROM orders
             JOIN restaurant_tables ON restaurant_tables.id = orders.table_id WHERE orders.id = :id'
        );
        $statement->execute(['id' => $id]);
        $order = $statement->fetch();

        if (!$order) {
            return null;
        }

        $items = $this->pdo->prepare(
            'SELECT order_items.*, menu_items.name FROM order_items
             JOIN menu_items ON menu_items.id = order_items.menu_item_id
             WHERE order_items.order_id = :id'
        );
        $items->execute(['id' => $id]);
        $order['items'] = $items->fetchAll();

        return $order;
    }

    /**
     * @param array<int, int> $items
     */
    public function create(string $customerName, int $tableId, array $items): int
    {
        $customerName = trim($customerName);
        $items = array_filter($items, static fn (int $quantity): bool => $quantity > 0);
        if ($customerName === '' || $tableId <= 0 || $items === []) {
            throw new InvalidArgumentException('Data pesanan belum lengkap.');
        }

        $this->pdo->beginTransaction();

        try {
            $customer = new Customer($this->pdo);
            $customerId = $customer->firstOrCreate($customerName);
            $nextNumber = (int) $this->pdo->query(
                "SELECT COALESCE(MAX(CAST(SUBSTRING(order_number, 5) AS UNSIGNED)), 1040) + 1 FROM orders"
            )->fetchColumn();
            $orderNumber = '#DD-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $prices = $this->menuPrices(array_keys($items));
            $subtotal = 0;

            foreach ($items as $menuId => $quantity) {
                $subtotal += ($prices[(int) $menuId] ?? 0) * $quantity;
            }

            if ($subtotal <= 0) {
                throw new InvalidArgumentException('Menu tidak tersedia.');
            }

            $insert = $this->pdo->prepare(
                "INSERT INTO orders (order_number, customer_id, customer_name, table_id, status, subtotal, tax, total, created_by)
                 VALUES (:order_number, :customer_id, :customer_name, :table_id, 'waiting', :subtotal, 0, :total, :created_by)"
            );
            $insert->execute([
                'order_number' => $orderNumber,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'table_id' => $tableId,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'created_by' => $_SESSION['employee']['id'] ?? 1,
            ]);

            $orderId = (int) $this->pdo->lastInsertId();
            $itemInsert = $this->pdo->prepare(
                'INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal)
                 VALUES (:order_id, :menu_item_id, :quantity, :unit_price, :subtotal)'
            );

            foreach ($items as $menuId => $quantity) {
                $price = $prices[(int) $menuId] ?? 0;
                if ($price <= 0) {
                    continue;
                }
                $itemInsert->execute([
                    'order_id' => $orderId,
                    'menu_item_id' => $menuId,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'subtotal' => $price * $quantity,
                ]);
            }

            $this->logActivity($orderId, 'Pesanan dibuat oleh ' . ($_SESSION['employee']['name'] ?? 'Pegawai') . '.');
            $this->pdo->commit();

            return $orderId;
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * Updates an order status.
     */
    public function updateStatus(int $orderId, string $status): void
    {
        if (!in_array($status, ['waiting', 'cooking', 'done', 'closed'], true)) {
            throw new InvalidArgumentException('Status tidak valid.');
        }

        $statement = $this->pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $orderId]);
        $label = ['waiting' => 'Menunggu', 'cooking' => 'Sedang dibuat', 'done' => 'Selesai', 'closed' => 'Kosong'][$status];
        $this->logActivity($orderId, 'Ayu mengubah status menjadi ' . $label . '.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activities(int $orderId): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM order_activities WHERE order_id = :order_id ORDER BY created_at DESC LIMIT 4');
        $statement->execute(['order_id' => $orderId]);

        return $statement->fetchAll();
    }

    /**
     * @param array<int, int|string> $menuIds
     * @return array<int, int>
     */
    private function menuPrices(array $menuIds): array
    {
        $placeholders = implode(',', array_fill(0, count($menuIds), '?'));
        $statement = $this->pdo->prepare("SELECT id, price FROM menu_items WHERE id IN ({$placeholders}) AND is_available = 1");
        $statement->execute(array_values($menuIds));
        $prices = [];
        foreach ($statement->fetchAll() as $row) {
            $prices[(int) $row['id']] = (int) $row['price'];
        }

        return $prices;
    }

    private function logActivity(int $orderId, string $message): void
    {
        $statement = $this->pdo->prepare('INSERT INTO order_activities (order_id, message) VALUES (:order_id, :message)');
        $statement->execute(['order_id' => $orderId, 'message' => $message]);
    }
}
