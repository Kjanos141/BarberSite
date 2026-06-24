<?php

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->fetch(
            'SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetch(
            'SELECT id, name, email, role, status, created_at, last_login FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    public function all(int $limit = 1000): array
    {
        return $this->fetchAll(
            'SELECT id, name, email, role, status, created_at, last_login FROM users ORDER BY created_at DESC LIMIT ?',
            [$limit]
        );
    }

    public function create(string $name, string $email, string $passwordHash, string $role = 'user'): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, ?, \'active\', NOW())',
            [$name, $email, $passwordHash, $role]
        );
        return (int)$this->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->execute('UPDATE users SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function updateRole(int $id, string $role): void
    {
        $this->execute('UPDATE users SET role = ? WHERE id = ?', [$role, $id]);
    }

    public function updateLastLogin(int $id): void
    {
        $this->execute('UPDATE users SET last_login = NOW() WHERE id = ?', [$id]);
    }

    public function countAll(): int
    {
        return (int)$this->fetch('SELECT COUNT(*) as c FROM users')['c'];
    }

    public function countActive(): int
    {
        return (int)$this->fetch("SELECT COUNT(*) as c FROM users WHERE status = 'active'")['c'];
    }

    public function countAdmins(): int
    {
        return (int)$this->fetch("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")['c'];
    }

    public function emailExists(string $email): bool
    {
        return (bool)$this->fetch('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]);
    }
}
