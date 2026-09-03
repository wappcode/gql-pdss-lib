<?php

declare(strict_types=1);

namespace GPDCore\Shared;

class ClientIPUtilities
{
    public static function getClientIP(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::isValidIP($_SERVER['HTTP_CLIENT_IP'])) {
            return self::sanitizeIP($_SERVER['HTTP_CLIENT_IP']);
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (self::isValidIP($ip) && !self::isPrivateIP($ip)) {
                    return $ip;
                }
            }
        }

        if (!empty($_SERVER['REMOTE_ADDR']) && self::isValidIP($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '0.0.0.0';
    }

    public static function get(): string
    {
        return self::getClientIP();
    }

    public static function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    public static function isPrivateIP(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    private static function sanitizeIP(string $ip): string
    {
        return trim($ip);
    }

    public static function getAllIPs(): array
    {
        return [
            'client_ip' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
            'x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
            'resolved_ip' => self::getClientIP(),
        ];
    }

    public static function isLocalhost(): bool
    {
        $ip = self::getClientIP();

        return in_array($ip, ['127.0.0.1', '::1', 'localhost', '0.0.0.0']);
    }
}
