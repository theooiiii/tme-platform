<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class RuntimePath
{
    public static function cache(string $path = ''): string
    {
        return self::writable('cache', $path);
    }

    public static function temp(string $path = ''): string
    {
        return self::writable('temp', $path);
    }

    public static function logs(string $path = ''): string
    {
        return self::writable('logs', $path);
    }

    private static function writable(string $area, string $path): string
    {
        $base = (env('VERCEL') || env('TME_SERVERLESS'))
            ? rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tme-platform' . DIRECTORY_SEPARATOR . $area
            : BASE_PATH . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $area;

        $target = rtrim($base . DIRECTORY_SEPARATOR . trim($path, '/\\'), DIRECTORY_SEPARATOR);

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }

        return $target;
    }
}
