<?php

namespace App\Models;

class Invite extends Model
{
    protected string $table = 'invites';

    public function findByToken(string $token): ?array
    {
        return $this->fetch(
            "SELECT * FROM invites WHERE token = ? AND status = 'pending' AND expires_at > NOW() LIMIT 1",
            [$token]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetch("SELECT * FROM invites WHERE id = ? AND status = 'pending' LIMIT 1", [$id]);
    }

    public function getPending(): array
    {
        return $this->fetchAll(
            "SELECT i.id, i.email, i.name, i.role, i.created_at, i.expires_at,
                    u.name AS invited_by_name
             FROM invites i
             LEFT JOIN users u ON u.id = i.invited_by
             WHERE i.status = 'pending' AND i.expires_at > NOW()
             ORDER BY i.created_at DESC"
        );
    }

    public function create(string $email, string $name, string $role, string $token, int $invitedBy, string $note, string $expiresAt): int
    {
        $this->execute(
            "INSERT INTO invites (email, name, role, token, invited_by, note, expires_at, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
            [$email, $name, $role, $token, $invitedBy, $note, $expiresAt]
        );
        return (int)$this->lastInsertId();
    }

    public function revoke(int $id): void
    {
        $this->execute("UPDATE invites SET status = 'revoked' WHERE id = ?", [$id]);
    }

    public function markUsed(int $id): void
    {
        $this->execute("UPDATE invites SET status = 'used', used_at = NOW() WHERE id = ?", [$id]);
    }

    public function extendExpiry(int $id, string $newExpiry): void
    {
        $this->execute("UPDATE invites SET expires_at = ? WHERE id = ?", [$newExpiry, $id]);
    }

    public function hasPending(string $email): bool
    {
        return (bool)$this->fetch(
            "SELECT id FROM invites WHERE email = ? AND status = 'pending' AND expires_at > NOW() LIMIT 1",
            [$email]
        );
    }

    public function countPending(): int
    {
        return (int)$this->fetch("SELECT COUNT(*) as c FROM invites WHERE status = 'pending' AND expires_at > NOW()")['c'];
    }
}
