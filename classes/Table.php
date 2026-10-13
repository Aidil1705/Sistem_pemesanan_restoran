<?php

declare(strict_types=1);

namespace App;

/**
 * Dining table model.
 */
class Table extends BaseModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function allWithLatestOrder(): array
    {
        $sql = "SELECT restaurant_tables.*, orders.id order_id, orders.order_number, orders.customer_name,
                       orders.status order_status, orders.total, orders.created_at,
                       (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE order_id = orders.id) item_count
                FROM restaurant_tables
                LEFT JOIN orders ON orders.id = (
                    SELECT id FROM orders o
                    WHERE o.table_id = restaurant_tables.id AND o.status IN ('waiting', 'cooking', 'done')
                    ORDER BY o.created_at DESC LIMIT 1
                )
                ORDER BY restaurant_tables.number ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function emptyTables(): array
    {
        return $this->pdo->query(
            "SELECT * FROM restaurant_tables
             WHERE id NOT IN (SELECT table_id FROM orders WHERE status IN ('waiting', 'cooking', 'done'))
             ORDER BY number ASC"
        )->fetchAll();
    }
}
