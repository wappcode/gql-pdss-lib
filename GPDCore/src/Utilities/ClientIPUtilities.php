<?php

declare(strict_types=1);

namespace GPDCore\Utilities;

class ClientIPUtilities
{
    /**
     * Obtiene la dirección IP real del cliente.
     * 
     * Verifica múltiples fuentes en orden de prioridad:
     * 1. HTTP_CLIENT_IP (IP compartida/ISP)
     * 2. HTTP_X_FORWARDED_FOR (IP detrás de proxy)
     * 3. REMOTE_ADDR (IP directa)
     * 
     * @return string La dirección IP del cliente o '0.0.0.0' si no se puede determinar
     */
    public static function getClientIP(): string
    {
        // IP desde internet compartido
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::isValidIP($_SERVER['HTTP_CLIENT_IP'])) {
            return self::sanitizeIP($_SERVER['HTTP_CLIENT_IP']);
        }

        // IP desde proxy (puede contener múltiples IPs separadas por coma)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ip) {
                $ip = trim($ip);
                if (self::isValidIP($ip) && !self::isPrivateIP($ip)) {
                    return $ip;
                }
            }
        }

        // IP desde conexión directa
        if (!empty($_SERVER['REMOTE_ADDR']) && self::isValidIP($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '0.0.0.0';
    }

    /**
     * Alias para retrocompatibilidad.
     * 
     * @deprecated Usar getClientIP() en su lugar
     * @return string
     */
    public static function get(): string
    {
        return self::getClientIP();
    }

    /**
     * Valida si una cadena es una dirección IP válida (IPv4 o IPv6).
     * 
     * @param string $ip La dirección IP a validar
     * @return bool True si es válida, false en caso contrario
     */
    public static function isValidIP(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Verifica si una IP es privada o reservada.
     * 
     * @param string $ip La dirección IP a verificar
     * @return bool True si es IP privada/reservada, false si es pública
     */
    public static function isPrivateIP(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Limpia y sanitiza una dirección IP.
     * 
     * @param string $ip La dirección IP a limpiar
     * @return string La IP sanitizada
     */
    private static function sanitizeIP(string $ip): string
    {
        return trim($ip);
    }

    /**
     * Obtiene todas las IPs posibles del cliente (útil para debugging).
     * 
     * @return array<string, string|null> Array con todas las fuentes de IP
     */
    public static function getAllIPs(): array
    {
        return [
            'client_ip' => $_SERVER['HTTP_CLIENT_IP'] ?? null,
            'x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
            'resolved_ip' => self::getClientIP(),
        ];
    }

    /**
     * Verifica si la IP del cliente es localhost.
     * 
     * @return bool True si es localhost, false en caso contrario
     */
    public static function isLocalhost(): bool
    {
        $ip = self::getClientIP();
        return in_array($ip, ['127.0.0.1', '::1', 'localhost', '0.0.0.0']);
    }
}
