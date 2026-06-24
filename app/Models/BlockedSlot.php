<?php

namespace App\Models;

class BlockedSlot extends Model
{
    protected string $table = 'blocked_slots';

    public function all(): array
    {
        return $this->fetchAll(
            "SELECT bs.*, u.name as created_by_name
             FROM blocked_slots bs
             LEFT JOIN users u ON u.id = bs.created_by
             ORDER BY bs.created_at DESC"
        );
    }

    public function create(array $data): int
    {
        $this->execute(
            "INSERT INTO blocked_slots (block_type, block_date, block_time, weekday, recurring_time, reason, valid_from, valid_until, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['block_type'],
                $data['block_date']     ?? null,
                $data['block_time']     ?? null,
                $data['weekday']        ?? null,
                $data['recurring_time'] ?? null,
                $data['reason']         ?? null,
                $data['valid_from']     ?? null,
                $data['valid_until']    ?? null,
                $data['created_by'],
            ]
        );
        return (int)$this->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->execute("DELETE FROM blocked_slots WHERE id = ?", [$id]);
    }

    /** Visszaadja az összes letiltást egy adott dátumra vonatkozóan */
    public function getForDate(string $date): array
    {
        $weekday = (int)date('w', strtotime($date)); // 0=vasárnap
        return $this->fetchAll(
            "SELECT * FROM blocked_slots WHERE
               (block_type = 'day' AND block_date = ?)
               OR (block_type = 'slot' AND block_date = ?)
               OR (block_type = 'recurring_day' AND weekday = ?
                   AND (valid_from IS NULL OR valid_from <= ?)
                   AND (valid_until IS NULL OR valid_until >= ?))
               OR (block_type = 'recurring_slot' AND weekday = ?
                   AND (valid_from IS NULL OR valid_from <= ?)
                   AND (valid_until IS NULL OR valid_until >= ?))",
            [$date, $date, $weekday, $date, $date, $weekday, $date, $date]
        );
    }

    /** Teljesen le van-e tiltva az adott nap */
    public function isDayBlocked(string $date): bool
    {
        $blocks = $this->getForDate($date);
        foreach ($blocks as $b) {
            if (in_array($b['block_type'], ['day', 'recurring_day'])) return true;
        }
        return false;
    }

    /** Le van-e tiltva egy konkrét időpont */
    public function isTimeBlocked(string $date, string $time): bool
    {
        $blocks = $this->getForDate($date);
        foreach ($blocks as $b) {
            if ($b['block_type'] === 'day' || $b['block_type'] === 'recurring_day') return true;
            if ($b['block_type'] === 'slot' && $b['block_time'] === $time) return true;
            if ($b['block_type'] === 'recurring_slot' && $b['recurring_time'] === $time) return true;
        }
        return false;
    }
}
