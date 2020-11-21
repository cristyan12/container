<?php declare(strict_types=1);

namespace Beleriand\Container;

abstract class Provider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    abstract public function register(): void;
}