<?php

namespace App\Core;

class Logger
{
    public static function log(
        string $action,
        ?int   $userId,
        string $entityType = '',
        ?int   $entityId   = null,
        string $description = ''
    ): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $userId,
                $action,
                $entityType ?: null,
                $entityId,
                $description ?: null,
                self::getIp(),
                isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            ]);
        } catch (\Throwable $e) {
            // Logolási hiba nem állíthatja meg az appot
            error_log('Logger error: ' . $e->getMessage());
        }
    }

    private static function getIp(): string
    {
        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}
