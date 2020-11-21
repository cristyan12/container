<?php

namespace Beleriand\Container;

class Application
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $this->registerSessionManager();
        $this->registerAuthenticator();
        $this->registerAccessHandler();
    }

    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $provider = new $provider($this->container);
            $provider->register();
        }
    }
}
