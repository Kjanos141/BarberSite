<?php

namespace App\Core;

class Mailer
{
    private array $config;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/mail.php';
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        try {
            if ($this->config['driver'] === 'smtp') {
                return $this->sendSmtp($to, $subject, $htmlBody, $textBody);
            }
            return $this->sendMail($to, $subject, $htmlBody, $textBody);
        } catch (\Throwable $e) {
            if ($this->config['silent_fail'] ?? true) {
                Logger::log('mail.error', null, 'mail', null,
                    "Mail küldési hiba [{$to}]: " . $e->getMessage());
                return false;
            }
            throw $e;
        }
    }

    private function sendMail(string $to, string $subject, string $html, string $text): bool
    {
        $from    = $this->config['from_email'];
        $name    = $this->config['from_name'];
        $headers = implode("\r\n", [
            "From: {$name} <{$from}>",
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: BuktaZoltanEV",
        ]);
        return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
    }

    private function sendSmtp(string $to, string $subject, string $html, string $text): bool
    {
        // PHPMailer integráció — composer require phpmailer/phpmailer
        if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            throw new \RuntimeException('PHPMailer nincs telepítve. Futtasd: composer require phpmailer/phpmailer');
        }
        $smtp = $this->config['smtp'];
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['username'];
        $mail->Password   = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'];
        $mail->Port       = $smtp['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = $text ?: strip_tags($html);
        return $mail->send();
    }

    // ---- E-mail sablonok ----

    public function bookingPending(array $booking, array $user, array $service): bool
    {
        $date = date('Y. F j.', strtotime($booking['booking_date']));
        $time = substr($booking['booking_time'], 0, 5);
        $html = $this->template(
            'Foglalási igény beérkezett',
            "Kedves <strong>" . htmlspecialchars($user['name']) . "</strong>!",
            "<p>Foglalási igényed sikeresen beérkezett. Admin jóváhagyás után válik véglegessé.</p>
             <table style='width:100%;border-collapse:collapse;margin:16px 0'>
               <tr><td style='padding:8px;color:#7A8899;width:120px'>Szolgáltatás:</td><td style='padding:8px'><strong>" . htmlspecialchars($service['name']) . "</strong></td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időpont:</td><td style='padding:8px'><strong>{$date}, {$time}</strong></td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időtartam:</td><td style='padding:8px'>{$service['duration']} perc</td></tr>
             </table>
             <p style='color:#7A8899;font-size:14px'>Értesítünk, amint az admin jóváhagyta a foglalásodat.</p>"
        );
        return $this->send($user['email'], 'Foglalási igény beérkezett – Bukta Zoltán EV', $html);
    }

    public function bookingConfirmed(array $booking, array $user, array $service): bool
    {
        $date = date('Y. F j.', strtotime($booking['booking_date']));
        $time = substr($booking['booking_time'], 0, 5);
        $html = $this->template(
            'Foglalás megerősítve ✓',
            "Kedves <strong>" . htmlspecialchars($user['name']) . "</strong>!",
            "<p>Foglalásod <strong style='color:#27AE60'>jóváhagyásra került</strong>. Várunk!</p>
             <table style='width:100%;border-collapse:collapse;margin:16px 0'>
               <tr><td style='padding:8px;color:#7A8899;width:120px'>Szolgáltatás:</td><td style='padding:8px'><strong>" . htmlspecialchars($service['name']) . "</strong></td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időpont:</td><td style='padding:8px'><strong>{$date}, {$time}</strong></td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időtartam:</td><td style='padding:8px'>{$service['duration']} perc</td></tr>
             </table>"
        );
        return $this->send($user['email'], 'Foglalás visszaigazolva – Bukta Zoltán EV', $html);
    }

    public function bookingCancelled(array $booking, array $user, array $service, string $reason = ''): bool
    {
        $date = date('Y. F j.', strtotime($booking['booking_date']));
        $time = substr($booking['booking_time'], 0, 5);
        $reasonHtml = $reason ? "<p style='color:#7A8899'>Indoklás: {$reason}</p>" : '';
        $html = $this->template(
            'Foglalás elutasítva',
            "Kedves <strong>" . htmlspecialchars($user['name']) . "</strong>!",
            "<p>Sajnos foglalásod <strong style='color:#C0392B'>elutasításra vagy törlésre került</strong>.</p>
             <table style='width:100%;border-collapse:collapse;margin:16px 0'>
               <tr><td style='padding:8px;color:#7A8899;width:120px'>Szolgáltatás:</td><td style='padding:8px'>" . htmlspecialchars($service['name']) . "</td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időpont:</td><td style='padding:8px'>{$date}, {$time}</td></tr>
             </table>
             {$reasonHtml}
             <p style='color:#7A8899;font-size:14px'>Foglalj új időpontot ha szeretnél.</p>"
        );
        return $this->send($user['email'], 'Foglalás elutasítva – Bukta Zoltán EV', $html);
    }

    public function newBookingAdminNotify(array $booking, array $user, array $service, string $adminEmail): bool
    {
        $date = date('Y. F j.', strtotime($booking['booking_date']));
        $time = substr($booking['booking_time'], 0, 5);
        $html = $this->template(
            'Új foglalási igény',
            'Új foglalási igény érkezett!',
            "<table style='width:100%;border-collapse:collapse;margin:16px 0'>
               <tr><td style='padding:8px;color:#7A8899;width:120px'>Ügyfél:</td><td style='padding:8px'><strong>" . htmlspecialchars($user['name']) . "</strong> (" . htmlspecialchars($user['email']) . ")</td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Szolgáltatás:</td><td style='padding:8px'>" . htmlspecialchars($service['name']) . "</td></tr>
               <tr><td style='padding:8px;color:#7A8899'>Időpont:</td><td style='padding:8px'><strong>{$date}, {$time}</strong></td></tr>
             </table>
             <p><a href='/admin/foglalas' style='background:#B87333;color:#0D1117;padding:10px 20px;text-decoration:none;border-radius:4px;font-weight:600'>Foglalások kezelése →</a></p>"
        );
        return $this->send($adminEmail, 'Új foglalási igény – Bukta Zoltán EV', $html);
    }

    private function template(string $title, string $greeting, string $body): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="hu">
<head><meta charset="UTF-8"><style>
  body{font-family:'DM Sans',Arial,sans-serif;background:#0D1117;color:#F8F6F2;margin:0;padding:0}
  .wrap{max-width:560px;margin:40px auto;background:#131920;border:1px solid #253040;border-radius:8px;overflow:hidden}
  .header{background:#131920;border-bottom:1px solid #253040;padding:28px 32px}
  .logo{font-family:Georgia,serif;font-size:22px;color:#F8F6F2}
  .logo span{color:#B87333}
  .body{padding:32px}
  .greeting{font-size:17px;margin-bottom:12px}
  .footer{padding:20px 32px;border-top:1px solid #253040;font-size:12px;color:#7A8899}
  td{vertical-align:top}
</style></head>
<body>
<div class="wrap">
  <div class="header"><div class="logo">Bukta <span>Zoltán</span> EV</div></div>
  <div class="body">
    <div class="greeting">{$greeting}</div>
    {$body}
  </div>
  <div class="footer">© Bukta Zoltán EV — Ez egy automatikus értesítő e-mail.</div>
</div>
</body></html>
HTML;
    }
}
