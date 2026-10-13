<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Handles employee authentication and session identity.
 */
class Auth
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Attempts login using email or username.
     */
    public function attempt(string $identity, string $password): bool
    {
        $statement = $this->pdo->prepare('SELECT * FROM employees WHERE email = :email OR username = :username LIMIT 1');
        $statement->execute(['email' => $identity, 'username' => $identity]);
        $employee = $statement->fetch();

        if (!$employee || !password_verify($password, $employee['password_hash'])) {
            return false;
        }

        $_SESSION['employee'] = [
            'id' => (int) $employee['id'],
            'name' => $employee['name'],
            'role' => $employee['role'],
            'email' => $employee['email'],
        ];

        return true;
    }

    /**
     * Creates a new employee account.
     *
     * @param array<string, string> $employee
     */
    public function createEmployee(array $employee): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO employees (name, username, email, password_hash, role)
             VALUES (:name, :username, :email, :password_hash, :role)'
        );
        $statement->execute([
            'name' => $employee['name'],
            'username' => $employee['username'],
            'email' => $employee['email'],
            'password_hash' => password_hash($employee['password'], PASSWORD_DEFAULT),
            'role' => $employee['role'],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        return $_SESSION['employee'] ?? null;
    }

    /**
     * Redirects guests to login.
     */
    public function requireLogin(): void
    {
        if ($this->user() === null) {
            header('Location: index.php?page=login');
            exit;
        }
    }

    /**
     * Ends the current session.
     */
    public function logout(): void
    {
        unset($_SESSION['employee']);
    }
}
