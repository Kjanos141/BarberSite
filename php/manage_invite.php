<?php
// ============================================================
// manage_invite.php — Revoke or resend invite (admin only)
// ============================================================
require_once __DIR__ . '/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Érvénytelen kérés.'], 405);
}

$id     = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['revoke', 'resend'])) {
    jsonResponse(['success' => false, 'message' => 'Hiányzó vagy érvénytelen paraméter.']);
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM invites WHERE id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$id]);
    $invite = $stmt->fetch();

    if (!$invite) {
        jsonResponse(['success' => false, 'message' => 'A meghívó nem található.']);
    }

    if ($action === 'revoke') {
        $db->prepare("UPDATE invites SET status = 'revoked' WHERE id = ?")->execute([$id]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'resend') {
        // Extend expiry
        $newExpiry = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $db->prepare("UPDATE invites SET expires_at = ? WHERE id = ?")->execute([$newExpiry, $id]);

        $baseUrl   = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $inviteUrl = $baseUrl . '/register.html?token=' . $invite['token'];
        $name      = $invite['name'] ?: 'Felhasználó';

        $subject = 'Meghívó (újraküldve) – Blackstone Barber';
        $message = "Kedves {$name}!\n\nItt az újraküldött meghívód:\n{$inviteUrl}\n\n(48 óráig érvényes)";
        $headers = "From: noreply@blackstonebarber.hu\r\nContent-Type: text/plain; charset=UTF-8";
        @mail($invite['email'], $subject, $message, $headers);

        jsonResponse(['success' => true]);
    }

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Szerver hiba.'], 500);
}
