<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class RateLimiter
{
    private string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? RuntimePath::cache('rate-limit');

        if (! is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $record = $this->read($key);

        if (! $record) {
            return false;
        }

        if (($record['expires_at'] ?? 0) < time()) {
            $this->clear($key);
            return false;
        }

        return (int) ($record['attempts'] ?? 0) >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds): void
    {
        $record = $this->read($key);
        $now = time();

        if (! $record || ($record['expires_at'] ?? 0) < $now) {
            $record = [
                'attempts' => 0,
                'expires_at' => $now + $decaySeconds,
            ];
        }

        $record['attempts'] = (int) $record['attempts'] + 1;
        $this->write($key, $record);
    }

    public function clear(string $key): void
    {
        $file = $this->fileFor($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    private function read(string $key): ?array
    {
        $file = $this->fileFor($key);

        if (! is_file($file)) {
            return null;
        }

        $contents = file_get_contents($file);
        $record = json_decode((string) $contents, true);

        return is_array($record) ? $record : null;
    }

    private function write(string $key, array $record): void
    {
        file_put_contents($this->fileFor($key), json_encode($record), LOCK_EX);
    }

    private function fileFor(string $key): string
    {
        return $this->storagePath . '/' . hash('sha256', $key) . '.json';
    }
}
