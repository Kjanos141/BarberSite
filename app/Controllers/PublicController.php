<?php

namespace App\Controllers;

use App\Core\Response;

class PublicController
{
    public function home(): void
    {
        Response::view('public/home');
    }

    public function dashboard(): void
    {
        \App\Core\Auth::requireLogin();
        Response::view('public/dashboard', ['user' => \App\Core\Auth::user()]);
    }
}
