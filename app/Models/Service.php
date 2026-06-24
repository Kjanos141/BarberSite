<?php

namespace App\Models;

class Service extends Model
{
    protected string $table = 'services';

    public function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? "WHERE is_active = 1" : "";
        return $this->fetchAll("SELECT * FROM services {$where} ORDER BY sort_order, id");
    }

    public function findById(int $id): ?array
    {
        return $this->fetch("SELECT * FROM services WHERE id = ? LIMIT 1", [$id]);
    }

    public function create(string $name, string $desc, int $duration, ?int $price, string $color, int $sort): int
    {
        $this->execute(
            "INSERT INTO services (name, description, duration, price, color, sort_order, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW())",
            [$name, $desc, $duration, $price, $color, $sort]
        );
        return (int)$this->lastInsertId();
    }

    public function update(int $id, string $name, string $desc, int $duration, ?int $price, string $color, int $sort, int $active): void
    {
        $this->execute(
            "UPDATE services SET name=?, description=?, duration=?, price=?, color=?, sort_order=?, is_active=? WHERE id=?",
            [$name, $desc, $duration, $price, $color, $sort, $active, $id]
        );
    }

    public function delete(int $id): void
    {
        $this->execute("UPDATE services SET is_active = 0 WHERE id = ?", [$id]);
    }
}
