<?php

use App\Core\Router;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\BookingController;

$router = new Router();

// ---- Public ----
$router->get('/', [PublicController::class, 'home']);
$router->get('/dashboard', [PublicController::class, 'dashboard']);

// ---- Auth ----
$router->get('/login',              [AuthController::class, 'showLogin']);
$router->post('/login',             [AuthController::class, 'login']);
$router->get('/kijelentkezes',      [AuthController::class, 'logout']);
$router->get('/regisztracio',       [AuthController::class, 'showRegister']);
$router->post('/regisztracio',      [AuthController::class, 'register']);
$router->get('/token-ellenorzes',   [AuthController::class, 'validateToken']);

// ---- Booking (User) ----
$router->get('/foglalas',           [BookingController::class, 'index']);
$router->get('/api/slots',          [BookingController::class, 'availableSlots']);
$router->post('/api/foglalas',      [BookingController::class, 'store']);
$router->post('/api/foglalas/lemondas',       [BookingController::class, 'cancel']);
$router->post('/api/foglalas/csoport-lemondas', [BookingController::class, 'cancelGroup']);

// ---- Admin pages ----
$router->get('/admin',                  [AdminController::class, 'dashboard']);
$router->get('/admin/felhasznalok',     [AdminController::class, 'users']);
$router->get('/admin/meghivo',          [AdminController::class, 'invitePage']);
$router->get('/admin/foglalasok',       [AdminController::class, 'bookingsPage']);
$router->get('/admin/szolgaltatasok',   [AdminController::class, 'servicesPage']);
$router->get('/admin/letiltasok',       [AdminController::class, 'blockedPage']);
$router->get('/admin/naplo',            [AdminController::class, 'logPage']);

// ---- Admin API ----
$router->get('/api/admin/stats',                [AdminController::class, 'stats']);
$router->get('/api/admin/felhasznalok',         [AdminController::class, 'listUsers']);
$router->post('/api/admin/felhasznalo',         [AdminController::class, 'updateUser']);
$router->post('/api/admin/meghivo',             [AdminController::class, 'sendInvite']);
$router->get('/api/admin/meghivok',             [AdminController::class, 'listInvites']);
$router->post('/api/admin/meghivo/kezeles',     [AdminController::class, 'manageInvite']);

// Foglalások
$router->get('/api/admin/foglalasok',           [AdminController::class, 'listBookings']);
$router->post('/api/admin/foglalas/jovahagyas', [AdminController::class, 'confirmBooking']);
$router->post('/api/admin/foglalas/elutasit',   [AdminController::class, 'rejectBooking']);

// Szolgáltatások
$router->get('/api/admin/szolgaltatasok',       [AdminController::class, 'listServices']);
$router->post('/api/admin/szolgaltatas',        [AdminController::class, 'saveService']);
$router->post('/api/admin/szolgaltatas/torles', [AdminController::class, 'deleteService']);

// Letiltások
$router->get('/api/admin/letiltasok',           [AdminController::class, 'listBlocked']);
$router->post('/api/admin/letiltasok',          [AdminController::class, 'addBlocked']);
$router->post('/api/admin/letiltas/torles',     [AdminController::class, 'deleteBlocked']);

// Napló
$router->get('/api/admin/naplo',                [AdminController::class, 'listLog']);

return $router;
