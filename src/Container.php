<?php declare(strict_types=1);

namespace Beleriand\Container;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class Container
{
    protected static ?Container $instance = null;

    protected array $bindings = [];
    protected array $shared = [];

    public static function setInstance(?Container $container): void
    {
        static::$instance = $container;
    }

    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * @param   mixed   $resolver
     */
    public function bind(string $name, $resolver, bool $shared = false): void
    {
        $this->bindings[$name] = [
            'resolver' => $resolver,
            'shared' => $shared,
        ];
    }

    /**
     * @param   mixed   $class
     */
    public function instance(string $name, $class): void
    {
        $this->shared[$name] = $class;
    }

    /**
     * @param   mixed   $resolver
     */
    public function singleton(string $name, $resolver): void
    {
        $this->bind($name, $resolver, true);
    }

    public function make(string $name, ?array $arguments = [])
    {
        if (isset($this->shared[$name])) {
            return $this->shared[$name];
        }

        if (isset($this->bindings[$name])) {
            $resolver = $this->bindings[$name]['resolver'];
            $shared = $this->bindings[$name]['shared'];
        } else {
            $resolver = $name;
            $shared = false;
        }

        try {
            $object = ($resolver instanceof Closure)
                ? $resolver($this)
                : $this->build($resolver, $arguments);
        } catch (ReflectionException $e) {
            throw new ContainerException("Unable to build [$resolver]: " . $e->getMessage(), 0, $e);
        }

        if ($shared) {
            $this->shared[$name] = $object;
        }

        return $object;
    }

    public function build(string $name, array $arguments = []): object
    {
        $reflection = new ReflectionClass($name);

        if (! $reflection->isInstantiable()) {
            throw new InvalidArgumentException(
                "$name is not instantiable. Is possible $name are Inteface or Abstract Class."
            );
        }

        $constructor = $reflection->getConstructor();  //: ReflectionMethod

        if (is_null($constructor)) {
            return new $name;
        }

        $constructorParameters = $constructor->getParameters();  //: ReflectionParameter[]

        $dependencies = [];

        foreach ($constructorParameters as $constructorParameter) {
            $parameterName = $constructorParameter->getName();

            if (isset($arguments[$parameterName])) {
                $dependencies[] = $arguments[$parameterName];
                continue;
            }

            try {
                $parameterClass = $constructorParameter->getClass();
            } catch (ReflectionException $e) {
                throw new ContainerException("Unable to build [$parameterName]: " . $e->getMessage(), 0, $e);
            }

            if ($parameterClass != null) {
                $parameterClassName = $parameterClass->getName();

                $dependencies[] = $this->build($parameterClassName);
            }

            if ($constructorParameter->isDefaultValueAvailable()) {
                $defaultValue = $constructorParameter->getDefaultValue();

                $dependencies[] = $defaultValue;
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
