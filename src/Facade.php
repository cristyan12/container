<?php declare(strict_types=1);

namespace Beleriand\Container;

abstract class Facade
{
    protected static ?Container $container = null;

    public static function setContainer(Container $container): void
    {
        static::$container = $container;
    }

    public static function getContainer(): Container
    {
        return static::$container;
    }

    public static function getAccessor(): string
    {
        throw new ContainerException('Please implement the getAccessor method in your class.');
    }

    public static function getInstance()
    {
        return static::getContainer()->make(static::getAccessor());
    }

    public static function __callStatic(string $method, array $args)
    {
        $object = static::getInstance();

        return call_user_func_array([$object, $method], $args);
    }
}