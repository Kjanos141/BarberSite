<?php
// ============================================================
// stats.php — Dashboard statistics (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

requireAdmin();

try {
    $db = getDB();

    $total   = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $active  = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
    $admins  = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $pending = $db->query("SELECT COUNT(*) FROM invites WHERE status = 'pending' AND expires_at > NOW()")->fetchColumn();

    jsonResponse([
        'success'         => true,
        'total'           => (int)$total,
        'active'          => (int)$active,
        'admins'          => (int)$admins,
        'pending_invites' => (int)$pending,
    ]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
