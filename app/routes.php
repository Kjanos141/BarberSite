<?php

use App\Core\Router;
use App\Controllers\PublicController;
use App\Controllers\AuthController;
use App\Controllers\AdminController;

$router = new Router();

// ---- Public ----
$router->get('/', [PublicController::class, 'home']);
$router->get('/dashboard', [PublicController::class, 'dashboard']);

// ---- Auth ----
$router->get('/login',          [AuthController::class, 'showLogin']);
$router->post('/login',         [AuthController::class, 'login']);
$router->get('/kijelentkezes',  [AuthController::class, 'logout']);
$router->get('/regisztracio',   [AuthController::class, 'showRegister']);
$router->post('/regisztracio',  [AuthController::class, 'register']);
$router->get('/token-ellenorzes', [AuthController::class, 'validateToken']);

// ---- Admin pages ----
$router->get('/admin',           [AdminController::class, 'dashboard']);
$router->get('/admin/felhasznalok', [AdminController::class, 'users']);
$router->get('/admin/meghivo',   [AdminController::class, 'invitePage']);

// ---- Admin API ----
$router->get('/api/admin/stats',          [AdminController::class, 'stats']);
$router->get('/api/admin/felhasznalok',   [AdminController::class, 'listUsers']);
$router->post('/api/admin/felhasznalo',   [AdminController::class, 'updateUser']);
$router->post('/api/admin/meghivo',       [AdminController::class, 'sendInvite']);
$router->get('/api/admin/meghivok',       [AdminController::class, 'listInvites']);
$router->post('/api/admin/meghivo/kezeles', [AdminController::class, 'manageInvite']);

return $router;
