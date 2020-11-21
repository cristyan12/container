<?php

use Beleriand\Container\Container;
use Beleriand\Container\ContainerException;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /** @test */
    function it_can_bind_from_closure(): void
    {
        $container = new Container;

        $container->bind('key', fn() => 'Object');

        $this->assertSame('Object', $container->make('key'));
    }

    /** @test */
    function it_can_bind_instance(): void
    {
        $container = new Container;

        $class = new class {};

        $container->instance('key', $class);

        $this->assertSame($class, $container->make('key'));
    }

    /** @test */
    function it_can_bind_from_class_name(): void
    {
        $container = new Container;

        $container->bind('key', 'StdClass');

        $this->assertInstanceOf('StdClass', $container->make('key'));
    }

    /** @test */
    function it_can_bind_with_automatic_class_resolution(): void
    {
        $container = new Container;

        $container->bind('foo', 'Foo');

        $this->assertInstanceOf('Foo', $container->make('foo'));
    }

    /** @test */
    function singleton_instance(): void
    {
        $container = new Container;

        $container->singleton('foo', 'Foo');

        $this->assertSame($container->make('foo'), $container->make('foo'));
    }

    /** @test */
    function expected_container_exception_if_dependency_not_exists(): void
    {
        $this->expectException(
            ContainerException::class,
            'Unable to build [Qux]: Class Narf does not exist'
        );

        $container = new Container;

        $container->bind('qux', 'Qux');

        $container->make('qux');
    }

    /** @test */
    function expected_container_exception_if_class_not_exists(): void
    {
        $this->expectException(
            ContainerException::class,
            'Unable to build [Narf]: Class Narf does not exist'
        );

        $container = new Container;

        $container->bind('narf', 'Narf');

        $container->make('narf');
    }

    /** @test */
    function container_make_with_arguments(): void
    {
        $container = new Container;

        $this->assertInstanceOf(
            MailDummy::class,
            $container->make('MailDummy', ['url' => '::url::', 'key' => '::key::']));
    }

    /** @test */
    function container_make_with_default_arguments(): void
    {
        $container = new Container;

        $this->assertInstanceOf(
            MailDummy::class,
            $container->make('MailDummy', ['url' => '::url::'])
        );
    }
}

/** --TESTS CLASES-- **/

class MailDummy
{
    private string $url;
    private ?string $key;

    public function __construct(string $url, string $key = null)
    {
        $this->url = $url;
        $this->key = $key;
    }
}

class Foo
{
    public function __construct(Bar $bar) {}
}

class Bar
{
    public function __construct(Baz $baz) {}
}

class Baz
{

}

class Qux
{
    public function __construct(Narf $narf) {}
}
