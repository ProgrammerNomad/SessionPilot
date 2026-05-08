<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Support;

class IpHelper
{
    public static function getClientIp(): string
    {
        // Respect reverse proxy headers only if explicitly configured
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];

        foreach ($headers as $header) {
            if ( ! empty($_SERVER[$header]) ) {
                $ip = sanitize_text_field(wp_unslash($_SERVER[$header]));
                // X-Forwarded-For can be a comma-separated list — take the first
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '';
    }

    public static function maybeAnonymize(string $ip): string
    {
        if ( self::shouldAnonymize() ) {
            return self::anonymize($ip);
        }

        return $ip;
    }

    public static function anonymize(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Zero out last 80 bits of IPv6
            $packed  = inet_pton($ip);
            $masked  = $packed & "\xff\xff\xff\xff\xff\xff" . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
            return inet_ntop($masked);
        }

        // IPv4: zero out last octet
        return preg_replace('/\.\d+$/', '.0', $ip) ?? $ip;
    }

    private static function shouldAnonymize(): bool
    {
        static $cache = null;

        if ($cache === null) {
            global $wpdb;
            $val    = $wpdb->get_var("SELECT setting_value FROM {$wpdb->prefix}sp_settings WHERE setting_key = 'anonymize_ip'");
            $cache  = (bool) ($val ?? false);
        }

        return $cache;
    }
}
