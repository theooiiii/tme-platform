<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class EventDispatcher
{
    private array $listeners = [];

    public function listen(string $event, callable|string $listener): void
    {
        $this->listeners[$event][] = $listener;
    }

    public function dispatch(string $event, array $payload = []): void
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            if (is_string($listener)) {
                if (! class_exists($listener)) {
                    throw new RuntimeException('Listener nao encontrado: ' . $listener);
                }

                $listener = [new $listener(), 'handle'];
            }

            $listener($payload, $event);
        }
    }
}
