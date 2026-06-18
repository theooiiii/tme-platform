<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Security
{
    public static function configureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        $sessionName = (string) config('app.security.session_name', 'TMESESSID');
        $sameSite = (string) config('app.security.session_samesite', 'Lax');
        $lifetime = (int) config('app.security.session_lifetime', 7200);
        $secure = (bool) config('app.security.session_secure', false) || self::isHttps();

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Lax',
        ]);
    }

    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }

        if ((bool) config('app.security.csp_enabled', true)) {
            $directives = [
                "default-src 'self'",
                "base-uri 'self'",
                "frame-ancestors 'self'",
                "object-src 'none'",
                "img-src 'self' data: blob: https://api.qrserver.com",
                "font-src 'self' data:",
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net",
                "connect-src 'self'",
                "media-src 'self' blob:",
            ];

            header('Content-Security-Policy: ' . implode('; ', $directives));
        }
    }

    public static function clientIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_REAL_IP'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        return '0.0.0.0';
    }

    private static function isHttps(): bool
    {
        return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }
}
