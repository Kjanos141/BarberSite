<?php

namespace App\Models;

class Booking extends Model
{
    protected string $table = 'bookings';

    public function findById(int $id): ?array
    {
        return $this->fetch(
            "SELECT b.*, s.name as service_name, s.duration as service_duration, s.color as service_color,
                    u.name as user_name, u.email as user_email
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN users u ON u.id = b.user_id
             WHERE b.id = ? LIMIT 1",
            [$id]
        );
    }

    public function getAll(array $filters = []): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = "b.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = "b.booking_date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = "b.booking_date <= ?";
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['user_id'])) {
            $where[]  = "b.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $whereStr = $where ? "WHERE " . implode(' AND ', $where) : '';
        return $this->fetchAll(
            "SELECT b.*, s.name as service_name, s.duration as service_duration, s.color as service_color,
                    u.name as user_name, u.email as user_email
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN users u ON u.id = b.user_id
             {$whereStr}
             ORDER BY b.booking_date ASC, b.booking_time ASC",
            $params
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->fetchAll(
            "SELECT b.*, s.name as service_name, s.duration as service_duration, s.color as service_color
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             WHERE b.user_id = ?
             ORDER BY b.booking_date DESC, b.booking_time DESC",
            [$userId]
        );
    }

    public function getByDate(string $date): array
    {
        return $this->fetchAll(
            "SELECT b.*, s.duration as service_duration, u.name as user_name
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             JOIN users u ON u.id = b.user_id
             WHERE b.booking_date = ? AND b.status IN ('pending','confirmed')
             ORDER BY b.booking_time ASC",
            [$date]
        );
    }

    /** Slot foglalt-e (pending vagy confirmed foglalás ütközik) */
    public function isSlotTaken(string $date, string $time, int $serviceDuration, ?int $excludeId = null): bool
    {
        // Ellenőrzi, hogy az adott időablak ütközik-e meglévő foglalással
        $params = [$date, $date];
        $excludeSql = $excludeId ? "AND b.id != {$excludeId}" : '';

        $result = $this->fetch(
            "SELECT b.id
             FROM bookings b
             JOIN services s ON s.id = b.service_id
             WHERE b.booking_date = ?
               AND b.status IN ('pending','confirmed')
               {$excludeSql}
               AND (
                 -- Új foglalás kezdete az existing belsejébe esik
                 (? >= b.booking_time AND ? < ADDTIME(b.booking_time, SEC_TO_TIME(s.duration * 60)))
                 OR
                 -- Meglévő kezdete az új belsejébe esik
                 (b.booking_time >= ? AND b.booking_time < ADDTIME(?, SEC_TO_TIME(? * 60)))
               )
             LIMIT 1",
            [$date, $time, $time, $time, $time, $serviceDuration]
        );
        return (bool)$result;
    }

    public function create(
        int $userId, int $serviceId, string $date, string $time,
        string $note = '', bool $isRecurring = false, ?int $groupId = null
    ): int {
        $this->execute(
            "INSERT INTO bookings (user_id, service_id, booking_date, booking_time, status, note, is_recurring, recurring_group_id, created_at)
             VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, NOW())",
            [$userId, $serviceId, $date, $time, $note, $isRecurring ? 1 : 0, $groupId]
        );
        return (int)$this->lastInsertId();
    }

    public function confirm(int $id, int $adminId): void
    {
        $this->execute(
            "UPDATE bookings SET status='confirmed', confirmed_by=?, confirmed_at=NOW() WHERE id=?",
            [$adminId, $id]
        );
    }

    public function reject(int $id, string $adminNote = ''): void
    {
        $this->execute(
            "UPDATE bookings SET status='rejected', admin_note=?, cancelled_at=NOW() WHERE id=?",
            [$adminNote, $id]
        );
    }

    public function cancel(int $id): void
    {
        $this->execute(
            "UPDATE bookings SET status='cancelled', cancelled_at=NOW() WHERE id=?",
            [$id]
        );
    }

    public function cancelGroup(int $groupId, string $fromDate): void
    {
        $this->execute(
            "UPDATE bookings SET status='cancelled', cancelled_at=NOW()
             WHERE recurring_group_id=? AND booking_date >= ? AND status IN ('pending','confirmed')",
            [$groupId, $fromDate]
        );
    }

    public function countPending(): int
    {
        return (int)($this->fetch("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")['c'] ?? 0);
    }

    public function countConfirmedUpcoming(): int
    {
        return (int)($this->fetch("SELECT COUNT(*) as c FROM bookings WHERE status='confirmed' AND booking_date >= CURDATE()")['c'] ?? 0);
    }

    /** Következő szabad sorszám a recurring group-okhoz */
    public function nextGroupId(): int
    {
        $row = $this->fetch("SELECT MAX(recurring_group_id) as m FROM bookings");
        return (int)($row['m'] ?? 0) + 1;
    }
}
