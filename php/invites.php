<?php
// ============================================================
// invites.php — List pending invites (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

requireAdmin();

try {
    $db   = getDB();
    $stmt = $db->prepare(
        "SELECT i.id, i.email, i.name, i.role, i.created_at, i.expires_at,
                u.name AS invited_by_name
         FROM invites i
         LEFT JOIN users u ON u.id = i.invited_by
         WHERE i.status = 'pending' AND i.expires_at > NOW()
         ORDER BY i.created_at DESC"
    );
    $stmt->execute();
    $invites = $stmt->fetchAll();

    jsonResponse(['success' => true, 'invites' => $invites]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
