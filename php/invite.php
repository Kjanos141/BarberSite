<?php
// ============================================================
// invite.php — Send invite to new user (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

$admin = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen kérés.'], 405);
}

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role  = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
$note  = trim($_POST['note'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen e-mail cím.']);
}

try {
    $db = getDB();

    // Check if email already registered
    $exists = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $exists->execute([$email]);
    if ($exists->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ez az e-mail cím már regisztrálva van.']);
    }

    // Check pending invite
    $pending = $db->prepare("SELECT id FROM invites WHERE email = ? AND status = 'pending' AND expires_at > NOW() LIMIT 1");
    $pending->execute([$email]);
    if ($pending->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Ehhez az e-mail címhez már van aktív meghívó.']);
    }

    // Generate token
    $token      = bin2hex(random_bytes(32));
    $expiresAt  = date('Y-m-d H:i:s', strtotime('+48 hours'));
    $invitedBy  = $admin['id'];

    $stmt = $db->prepare(
        'INSERT INTO invites (email, name, role, token, invited_by, note, expires_at, status, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, \'pending\', NOW())'
    );
    $stmt->execute([$email, $name, $role, $token, $invitedBy, $note, $expiresAt]);

    // Build invite URL
    $baseUrl    = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $inviteUrl  = $baseUrl . '/register.html?token=' . $token;

    // Send email (configure your mail settings)
    $subject = 'Meghívó – Blackstone Barber rendszer';
    $message = "Kedves {$name}!\n\n";
    $message .= "Meghívót kaptál a Blackstone Barber rendszerébe.\n\n";
    if ($note) $message .= "Üzenet: {$note}\n\n";
    $message .= "Regisztrálj az alábbi linken (48 óráig érvényes):\n{$inviteUrl}\n\n";
    $message .= "Üdvözlettel,\nBlackstone Barber Admin\n";
    $headers = "From: noreply@blackstonebarber.hu\r\nContent-Type: text/plain; charset=UTF-8";

    @mail($email, $subject, $message, $headers);

    jsonResponse(['success' => true, 'invite_url' => $inviteUrl]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba: ' . $e->getMessage()], 500);
}
