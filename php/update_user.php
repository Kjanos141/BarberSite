<?php
// ============================================================
// update_user.php — Toggle status/role (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

$admin = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen kérés.'], 405);
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) jsonResponse(['success' => false, 'message' => 'Hiányzó felhasználó azonosító.']);

// Prevent self-modification
if ($id === (int)$admin['id']) {
    jsonResponse(['success' => false, 'message' => 'Saját fiókod nem módosíthatod.']);
}

try {
    $db = getDB();
    $allowed = [];

    if (isset($_POST['status']) && in_array($_POST['status'], ['active', 'inactive'])) {
        $allowed[] = 'status = :status';
    }
    if (isset($_POST['role']) && in_array($_POST['role'], ['admin', 'user'])) {
        $allowed[] = 'role = :role';
    }

    if (empty($allowed)) {
        jsonResponse(['success' => false, 'message' => 'Nincs módosítandó mező.']);
    }

    $sql  = 'UPDATE users SET ' . implode(', ', $allowed) . ' WHERE id = :id';
    $stmt = $db->prepare($sql);

    if (isset($_POST['status'])) $stmt->bindValue(':status', $_POST['status']);
    if (isset($_POST['role']))   $stmt->bindValue(':role',   $_POST['role']);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    jsonResponse(['success' => true]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
