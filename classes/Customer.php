<?php

declare(strict_types=1);

namespace App;

/**
 * Customer persistence helper.
 */
class Customer extends BaseModel
{
    /**
     * Finds or creates a customer by name.
     */
    public function firstOrCreate(string $name): int
    {
        $statement = $this->pdo->prepare('SELECT id FROM customers WHERE name = :name LIMIT 1');
        $statement->execute(['name' => $name]);
        $customer = $statement->fetch();

        if ($customer) {
            return (int) $customer['id'];
        }

        $insert = $this->pdo->prepare('INSERT INTO customers (name) VALUES (:name)');
        $insert->execute(['name' => $name]);

        return (int) $this->pdo->lastInsertId();
    }
}
