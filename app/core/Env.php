<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Env
{
    public static function load(string $file): void
    {
        if (! is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}
