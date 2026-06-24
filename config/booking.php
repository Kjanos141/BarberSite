<?php

// ============================================================
// Időpontfoglalás konfiguráció
// ============================================================
return [
    // Foglalható időszak
    'booking_start_hour' => 9,    // 09:00-tól
    'booking_end_hour'   => 18,   // 18:00-ig (utolsó slot kezdete)

    // Max előre foglalható idő
    'max_advance_days'   => 90,   // 3 hónap

    // Alapértelmezett időablak (perc) — 30 vagy 60
    'default_slot_size'  => 60,

    // Hány napra előre látható a naptár
    'calendar_visible_months' => 3,
];
