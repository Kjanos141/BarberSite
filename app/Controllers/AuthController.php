<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Models\User;
use App\Models\Invite;

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Auth::isLoggedIn()) {
            Response::redirect(Auth::isAdmin() ? '/admin' : '/dashboard');
        }
        Response::view('auth/login');
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            Response::json(['success' => false, 'message' => 'Kötelező mezők hiányoznak.']);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::json(['success' => false, 'message' => 'Hibás e-mail cím vagy jelszó.']);
        }

        if ($user['status'] !== 'active') {
            Response::json(['success' => false, 'message' => 'A fiókod inaktív. Vedd fel a kapcsolatot az adminisztrátorral.']);
        }

        Auth::login($user);
        $this->userModel->updateLastLogin($user['id']);

        $redirect = $user['role'] === 'admin' ? '/admin' : '/dashboard';
        Response::json(['success' => true, 'redirect' => $redirect]);
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }

    public function showRegister(): void
    {
        $token = $_GET['token'] ?? '';
        Response::view('auth/register', ['token' => htmlspecialchars($token)]);
    }

    public function validateToken(): void
    {
        $token = trim($_GET['token'] ?? '');
        if (!$token) {
            Response::json(['valid' => false]);
        }

        $invite = (new Invite())->findByToken($token);
        if (!$invite) {
            Response::json(['valid' => false]);
        }

        Response::json(['valid' => true, 'email' => $invite['email'], 'role' => $invite['role']]);
    }

    public function register(): void
    {
        $token     = trim($_POST['token'] ?? '');
        $name      = trim($_POST['name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (!$token || !$name || !$email || !$password) {
            Response::json(['success' => false, 'message' => 'Kötelező mezők hiányoznak.']);
        }
        if ($password !== $password2) {
            Response::json(['success' => false, 'message' => 'A két jelszó nem egyezik.']);
        }
        if (strlen($password) < 8) {
            Response::json(['success' => false, 'message' => 'A jelszónak legalább 8 karakter hosszúnak kell lennie.']);
        }

        $inviteModel = new Invite();
        $invite = $inviteModel->findByToken($token);

        if (!$invite) {
            Response::json(['success' => false, 'message' => 'Érvénytelen vagy lejárt meghívó.']);
        }
        if ($invite['email'] !== $email) {
            Response::json(['success' => false, 'message' => 'E-mail cím nem egyezik a meghívóban szereplővel.']);
        }
        if ($this->userModel->emailExists($email)) {
            Response::json(['success' => false, 'message' => 'Ez az e-mail cím már regisztrálva van.']);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userModel->create($name, $email, $hash, $invite['role']);
        $inviteModel->markUsed($invite['id']);

        Response::json(['success' => true]);
    }
}
