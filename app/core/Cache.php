<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Cache
{
    public static function remember(string $key, int $seconds, callable $callback): mixed
    {
        $file = self::file($key);

        if (is_file($file)) {
            $payload = json_decode((string) file_get_contents($file), true);

            if (is_array($payload) && ($payload['expires_at'] ?? 0) >= time()) {
                return $payload['value'] ?? null;
            }
        }

        $value = $callback();
        file_put_contents($file, json_encode([
            'expires_at' => time() + $seconds,
            'value' => $value,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);

        return $value;
    }

    public static function forget(string $key): void
    {
        $file = self::file($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    private static function file(string $key): string
    {
        return RuntimePath::cache('app') . '/' . hash('sha256', $key) . '.json';
    }
}
