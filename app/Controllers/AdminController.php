<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\User;
use App\Models\Invite;

class AdminController
{
    private User $userModel;
    private Invite $inviteModel;

    public function __construct()
    {
        Auth::requireAdmin();
        $this->userModel   = new User();
        $this->inviteModel = new Invite();
    }

    public function dashboard(): void
    {
        Response::view('admin/dashboard', ['user' => Auth::user()]);
    }

    public function users(): void
    {
        Response::view('admin/users', ['user' => Auth::user()]);
    }

    public function invitePage(): void
    {
        Response::view('admin/invite', ['user' => Auth::user()]);
    }

    // ---- API endpoints ----

    public function stats(): void
    {
        Response::json([
            'success'         => true,
            'total'           => $this->userModel->countAll(),
            'active'          => $this->userModel->countActive(),
            'admins'          => $this->userModel->countAdmins(),
            'pending_invites' => $this->inviteModel->countPending(),
        ]);
    }

    public function listUsers(): void
    {
        $limit = min((int)($_GET['limit'] ?? 1000), 1000);
        Response::json(['success' => true, 'users' => $this->userModel->all($limit)]);
    }

    public function updateUser(): void
    {
        $admin = Auth::user();
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            Response::json(['success' => false, 'message' => 'Hiányzó felhasználó azonosító.']);
        }
        if ($id === (int)$admin['id']) {
            Response::json(['success' => false, 'message' => 'Saját fiókod nem módosíthatod.']);
        }

        if (isset($_POST['status']) && in_array($_POST['status'], ['active', 'inactive'])) {
            $this->userModel->updateStatus($id, $_POST['status']);
        } elseif (isset($_POST['role']) && in_array($_POST['role'], ['admin', 'user'])) {
            $this->userModel->updateRole($id, $_POST['role']);
        } else {
            Response::json(['success' => false, 'message' => 'Nincs módosítandó mező.']);
        }

        Response::json(['success' => true]);
    }

    public function sendInvite(): void
    {
        $admin = Auth::user();
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role  = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
        $note  = trim($_POST['note'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'Érvénytelen e-mail cím.']);
        }
        if ($this->userModel->emailExists($email)) {
            Response::json(['success' => false, 'message' => 'Ez az e-mail cím már regisztrálva van.']);
        }
        if ($this->inviteModel->hasPending($email)) {
            Response::json(['success' => false, 'message' => 'Ehhez az e-mail címhez már van aktív meghívó.']);
        }

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $this->inviteModel->create($email, $name, $role, $token, (int)$admin['id'], $note, $expiresAt);

        $baseUrl   = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $inviteUrl = $baseUrl . '/regisztracio?token=' . $token;

        $subject = 'Meghívó – Bukta Zoltán EV rendszer';
        $message = "Kedves {$name}!\n\nMeghívót kaptál a Bukta Zoltán EV rendszerébe.\n\n";
        if ($note) $message .= "Üzenet: {$note}\n\n";
        $message .= "Regisztrálj az alábbi linken (48 óráig érvényes):\n{$inviteUrl}\n\nÜdvözlettel,\nBukta Zoltán EV Admin\n";
        $headers = "From: noreply@buktazoltan.hu\r\nContent-Type: text/plain; charset=UTF-8";

        @mail($email, $subject, $message, $headers);

        Response::json(['success' => true, 'invite_url' => $inviteUrl]);
    }

    public function listInvites(): void
    {
        Response::json(['success' => true, 'invites' => $this->inviteModel->getPending()]);
    }

    public function manageInvite(): void
    {
        $id     = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';

        if (!$id || !in_array($action, ['revoke', 'resend'])) {
            Response::json(['success' => false, 'message' => 'Hiányzó vagy érvénytelen paraméter.']);
        }

        $invite = $this->inviteModel->findById($id);
        if (!$invite) {
            Response::json(['success' => false, 'message' => 'A meghívó nem található.']);
        }

        if ($action === 'revoke') {
            $this->inviteModel->revoke($id);
            Response::json(['success' => true]);
        }

        $newExpiry = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $this->inviteModel->extendExpiry($id, $newExpiry);

        $baseUrl   = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $inviteUrl = $baseUrl . '/regisztracio?token=' . $invite['token'];
        $name      = $invite['name'] ?: 'Felhasználó';

        $subject = 'Meghívó (újraküldve) – Bukta Zoltán EV';
        $message = "Kedves {$name}!\n\nItt az újraküldött meghívód:\n{$inviteUrl}\n\n(48 óráig érvényes)";
        $headers = "From: noreply@buktazoltan.hu\r\nContent-Type: text/plain; charset=UTF-8";
        @mail($invite['email'], $subject, $message, $headers);

        Response::json(['success' => true]);
    }
}
