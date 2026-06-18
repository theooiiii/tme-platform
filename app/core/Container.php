<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => false,
        ];
    }

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => true,
        ];
    }

    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? [
            'concrete' => $abstract,
            'shared' => false,
        ];

        $object = $this->build($binding['concrete']);

        if ($binding['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(callable|string $concrete): object
    {
        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (! class_exists($concrete)) {
            throw new RuntimeException('Classe nao encontrada no container: ' . $concrete);
        }

        $reflection = new ReflectionClass($concrete);
        $constructor = $reflection->getConstructor();

        if (! $constructor || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException('Dependencia nao resolvida: ' . $parameter->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
