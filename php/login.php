<?php
// ============================================================
// login.php — Handle POST login
// ============================================================
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen kérés.'], 405);
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Kötelező mezők hiányoznak.']);
}

try {
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Hibás e-mail cím vagy jelszó.']);
    }

    if ($user['status'] !== 'active') {
        jsonResponse(['success' => false, 'message' => 'A fiókod inaktív. Vedd fel a kapcsolatot az adminisztrátorral.']);
    }

    // Store session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user']    = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];

    // Update last login
    $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

    $redirect = $user['role'] === 'admin' ? '../admin.html' : '../dashboard.html';
    jsonResponse(['success' => true, 'redirect' => $redirect]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba. Kérjük próbálja újra.'], 500);
}
