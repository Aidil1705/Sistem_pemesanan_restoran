<?php

declare(strict_types=1);

namespace App;

/**
 * Menu model for CRUD and catalog visibility.
 */
class MenuItem extends BaseModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $search = null, ?string $visibility = null): array
    {
        $sql = 'SELECT * FROM menu_items WHERE 1 = 1';
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= ' AND name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        if ($visibility === 'available') {
            $sql .= ' AND is_available = 1';
        } elseif ($visibility === 'hidden') {
            $sql .= ' AND is_available = 0';
        }

        $statement = $this->pdo->prepare($sql . ' ORDER BY id ASC');
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function visible(): array
    {
        return $this->all(null, 'available');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO menu_items (name, category, price, description, image_url, is_available)
             VALUES (:name, :category, :price, :description, :image_url, :is_available)'
        );
        $statement->execute([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => (int) $data['price'],
            'description' => $data['description'],
            'image_url' => $data['image_url'],
            'is_available' => isset($data['is_available']) ? 1 : 0,
        ]);
    }

    /**
     * Updates menu visibility.
     */
    public function setAvailability(int $id, bool $available): void
    {
        $statement = $this->pdo->prepare('UPDATE menu_items SET is_available = :available WHERE id = :id');
        $statement->execute(['available' => $available ? 1 : 0, 'id' => $id]);
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        $rows = $this->pdo->query('SELECT is_available, COUNT(*) total FROM menu_items GROUP BY is_available')->fetchAll();
        $counts = ['all' => 0, 'available' => 0, 'hidden' => 0];
        foreach ($rows as $row) {
            $counts['all'] += (int) $row['total'];
            $counts[(int) $row['is_available'] === 1 ? 'available' : 'hidden'] = (int) $row['total'];
        }

        return $counts;
    }
}
