<?php

// ============================================================
// E-mail / SMTP konfiguráció
// Töltsd ki az adatokat, ha SMTP szerver elérhető
// ============================================================
return [
    'driver'     => 'mail',        // 'mail' = php mail(), 'smtp' = PHPMailer SMTP
    'from_email' => 'noreply@buktazoltan.hu',
    'from_name'  => 'Bukta Zoltán EV',

    // SMTP beállítások (csak ha driver = 'smtp')
    'smtp' => [
        'host'       => '',        // pl. smtp.gmail.com
        'port'       => 587,
        'encryption' => 'tls',    // 'tls' vagy 'ssl'
        'username'   => '',
        'password'   => '',
    ],

    // Ha true és nincs SMTP, csak logol, nem küld
    'silent_fail' => true,
];
