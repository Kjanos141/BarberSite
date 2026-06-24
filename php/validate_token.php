<?php
// ============================================================
// validate_token.php — Check if invite token is valid
// ============================================================
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$token = trim($_GET['token'] ?? '');
if (!$token) {
    jsonResponse(['valid' => false]);
}

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT email, role FROM invites WHERE token = ? AND status = 'pending' AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $invite = $stmt->fetch();

    if (!$invite) {
        jsonResponse(['valid' => false]);
    }
    jsonResponse(['valid' => true, 'email' => $invite['email'], 'role' => $invite['role']]);
} catch (Exception $e) {
    jsonResponse(['valid' => false]);
}
