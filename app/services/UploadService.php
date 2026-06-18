<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class UploadService
{
    public function storePublic(array $file, string $directory, array $allowedMimes, int $maxBytes): string
    {
        $this->assertValid($file, $allowedMimes, $maxBytes);

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $safeDirectory = trim(str_replace(['..', '\\'], ['', '/'], $directory), '/');
        $targetDirectory = BASE_PATH . '/public/uploads/' . $safeDirectory;

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . ($extension ? '.' . $extension : '');
        $target = $targetDirectory . '/' . $filename;

        if (! move_uploaded_file((string) $file['tmp_name'], $target)) {
            throw new RuntimeException('Nao foi possivel salvar o arquivo enviado.');
        }

        return 'uploads/' . $safeDirectory . '/' . $filename;
    }

    private function assertValid(array $file, array $allowedMimes, int $maxBytes): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Arquivo invalido ou ausente.');
        }

        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new InvalidArgumentException('Arquivo maior que o limite permitido.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');

        if (! is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('Upload nao reconhecido pelo servidor.');
        }

        $mime = mime_content_type($tmpName) ?: 'application/octet-stream';

        if (! in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException('Tipo de arquivo nao permitido.');
        }
    }
}
