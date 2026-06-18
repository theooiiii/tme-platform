<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field][] = $message ?? 'Este campo e obrigatorio.';
        }

        return $this;
    }

    public function email(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && trim((string) $value) !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? 'Informe um e-mail valido.';
        }

        return $this;
    }

    public function min(string $field, int $length, ?string $message = null): self
    {
        $value = (string) ($this->data[$field] ?? '');

        if ($value !== '' && mb_strlen($value) < $length) {
            $this->errors[$field][] = $message ?? 'Informe pelo menos ' . $length . ' caracteres.';
        }

        return $this;
    }

    public function max(string $field, int $length, ?string $message = null): self
    {
        $value = (string) ($this->data[$field] ?? '');

        if (mb_strlen($value) > $length) {
            $this->errors[$field][] = $message ?? 'Informe no maximo ' . $length . ' caracteres.';
        }

        return $this;
    }

    public function in(string $field, array $allowed, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && ! in_array($value, $allowed, true)) {
            $this->errors[$field][] = $message ?? 'Valor invalido.';
        }

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function flatErrors(): array
    {
        return array_merge(...array_values($this->errors ?: [[]]));
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }
}
