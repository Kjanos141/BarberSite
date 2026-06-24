<?php
// ============================================================
// users.php — List users (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

requireAdmin();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 1000;
$limit = min($limit, 1000);

try {
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT id, name, email, role, status, created_at, last_login
         FROM users
         ORDER BY created_at DESC
         LIMIT ?'
    );
    $stmt->execute([$limit]);
    $users = $stmt->fetchAll();

    jsonResponse(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
