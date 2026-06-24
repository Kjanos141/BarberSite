<?php
// ============================================================
// auth.php — Session + role guard
// ============================================================
require_once __DIR__ . '/db.php';

session_start();

function requireLogin(): array {
    if (empty($_SESSION['user_id'])) {
        if (isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Bejelentkezés szükséges.'], 401);
        }
        header('Location: ../login.html');
        exit;
    }
    return $_SESSION['user'] ?? [];
}

function requireAdmin(): array {
    $user = requireLogin();
    if (($user['role'] ?? '') !== 'admin') {
        if (isAjax()) {
            jsonResponse(['success' => false, 'message' => 'Nincs jogosultságod.'], 403);
        }
        header('Location: ../login.html');
        exit;
    }
    return $user;
}

function isAjax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
