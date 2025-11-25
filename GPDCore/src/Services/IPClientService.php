<?php

declare(strict_types=1);

namespace GPDCore\Services;

class IPClientService
{
    /**
     * Recupera la ip del cliente.
     */
    public static function get(): string
    {
        // whether ip is from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        // whether ip is from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // whether ip is from remote address
        else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        return $ip_address;
    }
}
