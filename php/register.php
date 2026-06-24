<?php
// ============================================================
// register.php — Create user from valid invite token
// ============================================================
require_once __DIR__ . '/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen kérés.'], 405);
}

$token     = trim($_POST['token'] ?? '');
$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';

if (!$token || !$name || !$email || !$password) {
    jsonResponse(['success' => false, 'message' => 'Kötelező mezők hiányoznak.']);
}
if ($password !== $password2) {
    jsonResponse(['success' => false, 'message' => 'A két jelszó nem egyezik.']);
}
if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'message' => 'A jelszónak legalább 8 karakter hosszúnak kell lennie.']);
}

try {
    $db = getDB();

    // Validate token
    $stmt = $db->prepare("SELECT * FROM invites WHERE token = ? AND status = 'pending' AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $invite = $stmt->fetch();

    if (!$invite) {
        jsonResponse(['success' => false, 'message' => 'Érvénytelen vagy lejárt meghívó.']);
    }
    if ($invite['email'] !== $email) {
        jsonResponse(['success' => false, 'message' => 'E-mail cím nem egyezik a meghívóban szereplővel.']);
    }

    // Check not already registered
    $exists = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $exists->execute([$email]);
    if ($exists->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ez az e-mail cím már regisztrálva van.']);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Create user
    $insert = $db->prepare(
        'INSERT INTO users (name, email, password_hash, role, status, created_at)
         VALUES (?, ?, ?, ?, \'active\', NOW())'
    );
    $insert->execute([$name, $email, $hash, $invite['role']]);

    // Mark invite used
    $db->prepare("UPDATE invites SET status = 'used', used_at = NOW() WHERE id = ?")
       ->execute([$invite['id']]);

    jsonResponse(['success' => true]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
