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
        print_r($inviteUrl);

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
    public function bookingsPage(): void
    {
        Response::view('admin/bookings', ['user' => Auth::user()]);
    }

    public function listBookings(): void
    {
        $filters = [
            'status'    => $_GET['status']    ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
        ];
        $bookings = (new \App\Models\Booking())->getAll(array_filter($filters));
        Response::json(['success' => true, 'bookings' => $bookings]);
    }

    public function confirmBooking(): void
    {
        $id      = (int)($_POST['id'] ?? 0);
        $model   = new \App\Models\Booking();
        $booking = $model->findById($id);

        if (!$booking) {
            Response::json(['success' => false, 'message' => 'Foglalás nem található.']);
        }

        $model->confirm($id, (int)Auth::id());
        \App\Core\Logger::log('booking.confirm', Auth::id(), 'booking', $id, "Admin jóváhagyta: #{$id}");

        // E-mail az ügyfélnek
        $userModel = new \App\Models\User();
        $user      = $userModel->findById((int)$booking['user_id']);
        $service   = (new \App\Models\Service())->findById((int)$booking['service_id']);
        if ($user && $service) {
            (new \App\Core\Mailer())->bookingConfirmed($booking, $user, $service);
        }

        Response::json(['success' => true]);
    }

    public function rejectBooking(): void
    {
        $id        = (int)($_POST['id'] ?? 0);
        $adminNote = trim($_POST['admin_note'] ?? '');
        $model     = new \App\Models\Booking();
        $booking   = $model->findById($id);

        if (!$booking) {
            Response::json(['success' => false, 'message' => 'Foglalás nem található.']);
        }

        $model->reject($id, $adminNote);
        \App\Core\Logger::log('booking.reject', Auth::id(), 'booking', $id, "Admin elutasította: #{$id} - {$adminNote}");

        $user    = (new \App\Models\User())->findById((int)$booking['user_id']);
        $service = (new \App\Models\Service())->findById((int)$booking['service_id']);
        if ($user && $service) {
            (new \App\Core\Mailer())->bookingCancelled($booking, $user, $service, $adminNote);
        }

        Response::json(['success' => true]);
    }

    // ---- SERVICES (Admin) ----

    public function servicesPage(): void
    {
        Response::view('admin/services', ['user' => Auth::user()]);
    }

    public function listServices(): void
    {
        $services = (new \App\Models\Service())->all();
        Response::json(['success' => true, 'services' => $services]);
    }

    public function saveService(): void
    {
        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $duration = in_array((int)($_POST['duration'] ?? 60), [30, 60]) ? (int)$_POST['duration'] : 60;
        $price    = $_POST['price'] !== '' ? (int)$_POST['price'] : null;
        $color    = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#B87333';
        $sort     = (int)($_POST['sort_order'] ?? 0);
        $active   = isset($_POST['is_active']) ? 1 : 0;

        if (!$name) {
            Response::json(['success' => false, 'message' => 'A szolgáltatás neve kötelező.']);
        }

        $model = new \App\Models\Service();
        if ($id > 0) {
            $model->update($id, $name, $desc, $duration, $price, $color, $sort, $active);
            \App\Core\Logger::log('service.update', Auth::id(), 'service', $id, "Szerkesztve: {$name}");
            Response::json(['success' => true, 'message' => 'Szolgáltatás frissítve.']);
        } else {
            $newId = $model->create($name, $desc, $duration, $price, $color, $sort);
            \App\Core\Logger::log('service.create', Auth::id(), 'service', $newId, "Létrehozva: {$name}");
            Response::json(['success' => true, 'message' => 'Szolgáltatás létrehozva.', 'id' => $newId]);
        }
    }

    public function deleteService(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) Response::json(['success' => false, 'message' => 'Hiányzó azonosító.']);
        (new \App\Models\Service())->delete($id);
        \App\Core\Logger::log('service.delete', Auth::id(), 'service', $id, "Deaktiválva: #{$id}");
        Response::json(['success' => true]);
    }

    // ---- BLOCKED SLOTS (Admin) ----

    public function blockedPage(): void
    {
        Response::view('admin/blocked', ['user' => Auth::user()]);
    }

    public function listBlocked(): void
    {
        $blocked = (new \App\Models\BlockedSlot())->all();
        Response::json(['success' => true, 'blocked' => $blocked]);
    }

    public function addBlocked(): void
    {
        $type   = $_POST['block_type'] ?? '';
        $valid  = ['day', 'slot', 'recurring_day', 'recurring_slot'];
        if (!in_array($type, $valid)) {
            Response::json(['success' => false, 'message' => 'Érvénytelen letiltás típus.']);
        }

        $data = [
            'block_type'     => $type,
            'block_date'     => $_POST['block_date']     ?? null,
            'block_time'     => $_POST['block_time']     ?? null,
            'weekday'        => isset($_POST['weekday'])   ? (int)$_POST['weekday']  : null,
            'recurring_time' => $_POST['recurring_time'] ?? null,
            'reason'         => trim($_POST['reason']    ?? ''),
            'valid_from'     => $_POST['valid_from']     ?? null,
            'valid_until'    => $_POST['valid_until']    ?? null,
            'created_by'     => (int)Auth::id(),
        ];

        $model = new \App\Models\BlockedSlot();
        $id    = $model->create($data);
        \App\Core\Logger::log('blocked.create', Auth::id(), 'blocked_slot', $id,
            "Letiltás létrehozva: {$type}");
        Response::json(['success' => true]);
    }

    public function deleteBlocked(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) Response::json(['success' => false, 'message' => 'Hiányzó azonosító.']);
        (new \App\Models\BlockedSlot())->delete($id);
        \App\Core\Logger::log('blocked.delete', Auth::id(), 'blocked_slot', $id, "Letiltás törölve: #{$id}");
        Response::json(['success' => true]);
    }

    // ---- ACTIVITY LOG ----

    public function logPage(): void
    {
        Response::view('admin/log', ['user' => Auth::user()]);
    }

    public function listLog(): void
    {
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        $db    = \App\Core\Database::getInstance();
        $stmt  = $db->prepare(
            "SELECT al.*, u.name as user_name
             FROM activity_log al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        Response::json(['success' => true, 'logs' => $stmt->fetchAll()]);
    }
}
