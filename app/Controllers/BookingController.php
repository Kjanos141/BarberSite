<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Logger;
use App\Core\Mailer;
use App\Core\Response;
use App\Models\Booking;
use App\Models\BlockedSlot;
use App\Models\Service;
use App\Models\User;

class BookingController
{
    private Booking     $bookingModel;
    private BlockedSlot $blockedModel;
    private Service     $serviceModel;
    private array       $config;

    public function __construct()
    {
        Auth::requireLogin();
        $this->bookingModel = new Booking();
        $this->blockedModel = new BlockedSlot();
        $this->serviceModel = new Service();
        $this->config       = require CONFIG_PATH . '/booking.php';
    }

    // ---- Foglaló oldal ----
    public function index(): void
    {
        $services = $this->serviceModel->all(true);
        $myBookings = $this->bookingModel->getByUser(Auth::id());
        Response::view('booking/index', [
            'user'       => Auth::user(),
            'services'   => $services,
            'myBookings' => $myBookings,
            'config'     => $this->config,
        ]);
    }

    // ---- Elérhető időpontok lekérése (AJAX) ----
    public function availableSlots(): void
    {
        $date      = $_GET['date'] ?? '';
        $serviceId = (int)($_GET['service_id'] ?? 0);

        if (!$date || !$serviceId) {
            Response::json(['success' => false, 'slots' => []]);
        }

        $service = $this->serviceModel->findById($serviceId);
        if (!$service) {
            Response::json(['success' => false, 'slots' => []]);
        }

        // Dátum validáció
        $dateTs = strtotime($date);
        $today  = strtotime('today');
        $maxTs  = strtotime('+' . $this->config['max_advance_days'] . ' days');

        if (!$dateTs || $dateTs < $today || $dateTs > $maxTs) {
            Response::json(['success' => true, 'slots' => [], 'reason' => 'Érvénytelen dátum']);
        }

        // Nap teljesen letiltva?
        if ($this->blockedModel->isDayBlocked($date)) {
            Response::json(['success' => true, 'slots' => [], 'reason' => 'Ez a nap le van tiltva']);
        }

        $slots    = $this->generateSlots($date, (int)$service['duration']);
        Response::json(['success' => true, 'slots' => $slots]);
    }

    private function generateSlots(string $date, int $duration): array
    {
        $startH = $this->config['booking_start_hour'];
        $endH   = $this->config['booking_end_hour'];
        $slots  = [];

        $current = mktime($startH, 0, 0, date('n', strtotime($date)), date('j', strtotime($date)), date('Y', strtotime($date)));
        $end     = mktime($endH,   0, 0, date('n', strtotime($date)), date('j', strtotime($date)), date('Y', strtotime($date)));

        while ($current + ($duration * 60) <= $end + 1) {
            $timeStr = date('H:i', $current);
            $timeFull = $timeStr . ':00';

            $blocked = $this->blockedModel->isTimeBlocked($date, $timeFull);
            $taken   = !$blocked && $this->bookingModel->isSlotTaken($date, $timeFull, $duration);

            $slots[] = [
                'time'      => $timeStr,
                'time_full' => $timeFull,
                'available' => !$blocked && !$taken,
                'reason'    => $blocked ? 'blocked' : ($taken ? 'taken' : ''),
            ];

            $current += $duration * 60;
        }
        return $slots;
    }

    // ---- Foglalás létrehozása ----
    public function store(): void
    {
        $user      = Auth::user();
        $serviceId = (int)($_POST['service_id'] ?? 0);
        $date      = trim($_POST['date'] ?? '');
        $time      = trim($_POST['time'] ?? '');
        $note      = trim($_POST['note'] ?? '');
        $recurring = (int)($_POST['recurring'] ?? 0); // 0=nem, hetente hány alkalom

        if (!$serviceId || !$date || !$time) {
            Response::json(['success' => false, 'message' => 'Hiányzó adatok.']);
        }

        $service = $this->serviceModel->findById($serviceId);
        if (!$service || !$service['is_active']) {
            Response::json(['success' => false, 'message' => 'Érvénytelen szolgáltatás.']);
        }

        // Dátum/időpont validáció
        $dateTs = strtotime($date);
        $today  = strtotime('today');
        $maxTs  = strtotime('+' . $this->config['max_advance_days'] . ' days');

        if (!$dateTs || $dateTs < $today || $dateTs > $maxTs) {
            Response::json(['success' => false, 'message' => 'Érvénytelen dátum.']);
        }

        $timeFull = strlen($time) === 5 ? $time . ':00' : $time;

        // Slot ellenőrzés
        if ($this->blockedModel->isTimeBlocked($date, $timeFull)) {
            Response::json(['success' => false, 'message' => 'Ez az időpont le van tiltva.']);
        }
        if ($this->bookingModel->isSlotTaken($date, $timeFull, (int)$service['duration'])) {
            Response::json(['success' => false, 'message' => 'Ez az időpont már foglalt.']);
        }

        $groupId = null;
        $createdCount = 0;

        if ($recurring > 0) {
            // Ismétlődő foglalás — max 3 hónap
            $groupId  = $this->bookingModel->nextGroupId();
            $maxWeeks = min($recurring, (int)ceil($this->config['max_advance_days'] / 7));
            $current  = $dateTs;

            for ($i = 0; $i < $maxWeeks; $i++) {
                $d = date('Y-m-d', $current);
                if (strtotime($d) > $maxTs) break;

                if (!$this->blockedModel->isTimeBlocked($d, $timeFull) &&
                    !$this->bookingModel->isSlotTaken($d, $timeFull, (int)$service['duration'])) {
                    $isFirst = $i === 0;
                    $bookingId = $this->bookingModel->create(
                        Auth::id(), $serviceId, $d, $timeFull, $note, true, $groupId
                    );
                    Logger::log('booking.create', Auth::id(), 'booking', $bookingId,
                        "Ismétlődő foglalás: {$d} {$timeFull} - " . $service['name']);
                    $createdCount++;
                    if ($isFirst) {
                        $this->sendUserNotification((int)$bookingId, $user, $service, $d, $timeFull);
                    }
                }
                $current = strtotime('+1 week', $current);
            }

            $this->notifyAdmin($user, $service, $date, $timeFull, true);
            Response::json([
                'success' => true,
                'message' => "{$createdCount} ismétlődő foglalási igény beadva, admin jóváhagyásra vár.",
            ]);
        } else {
            // Egyedi foglalás
            $bookingId = $this->bookingModel->create(Auth::id(), $serviceId, $date, $timeFull, $note);
            Logger::log('booking.create', Auth::id(), 'booking', $bookingId,
                "Foglalás: {$date} {$timeFull} - " . $service['name']);

            $this->sendUserNotification($bookingId, $user, $service, $date, $timeFull);
            $this->notifyAdmin($user, $service, $date, $timeFull, false);

            Response::json(['success' => true, 'message' => 'Foglalási igény beadva, admin jóváhagyásra vár.']);
        }
    }

    // ---- Saját foglalás lemondása ----
    public function cancel(): void
    {
        $id      = (int)($_POST['id'] ?? 0);
        $booking = $this->bookingModel->findById($id);

        if (!$booking || (int)$booking['user_id'] !== Auth::id()) {
            Response::json(['success' => false, 'message' => 'Nem található a foglalás.']);
        }
        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            Response::json(['success' => false, 'message' => 'Ez a foglalás már nem mondható le.']);
        }

        $this->bookingModel->cancel($id);
        Logger::log('booking.cancel', Auth::id(), 'booking', $id, "Foglalás lemondva: #{$id}");
        Response::json(['success' => true]);
    }

    // ---- Ismétlődő csoport lemondása ----
    public function cancelGroup(): void
    {
        $groupId = (int)($_POST['group_id'] ?? 0);
        if (!$groupId) {
            Response::json(['success' => false, 'message' => 'Hiányzó csoport azonosító.']);
        }
        $fromDate = date('Y-m-d');
        $this->bookingModel->cancelGroup($groupId, $fromDate);
        Logger::log('booking.cancel_group', Auth::id(), 'booking_group', $groupId,
            "Ismétlődő csoport lemondva: #{$groupId} {$fromDate}-tól");
        Response::json(['success' => true]);
    }

    private function sendUserNotification(int $bookingId, array $user, array $service, string $date, string $time): void
    {
        $booking = ['booking_date' => $date, 'booking_time' => $time, 'id' => $bookingId];
        (new Mailer())->bookingPending($booking, $user, $service);
    }

    private function notifyAdmin(array $user, array $service, string $date, string $time, bool $recurring): void
    {
        // Admin e-mail értesítés
        $adminEmail = (require CONFIG_PATH . '/mail.php')['from_email'];
        $booking    = ['booking_date' => $date, 'booking_time' => $time];
        (new Mailer())->newBookingAdminNotify($booking, $user, $service, $adminEmail);
    }
}
