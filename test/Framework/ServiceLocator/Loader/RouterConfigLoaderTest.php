<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ServiceLocator\Loader;

use ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware;
use ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware;
use ExtendsFramework\Router\Framework\ServiceLocator\Factory\RouterFactory;
use ExtendsFramework\Router\Route\Group\GroupRoute;
use ExtendsFramework\Router\Route\Host\HostRoute;
use ExtendsFramework\Router\Route\Method\MethodRoute;
use ExtendsFramework\Router\Route\Path\PathRoute;
use ExtendsFramework\Router\Route\Query\QueryRoute;
use ExtendsFramework\Router\Route\Scheme\SchemeRoute;
use ExtendsFramework\Router\RouterInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\FactoryResolver;
use ExtendsFramework\ServiceLocator\Resolver\Reflection\ReflectionResolver;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryResolver;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class RouterConfigLoaderTest extends TestCase
{
    /**
     * Load.
     *
     * Test that loader returns correct array.
     *
     * @covers \ExtendsFramework\Router\Framework\ServiceLocator\Loader\RouterConfigLoader::load()
     */
    public function testLoad(): void
    {
        $loader = new RouterConfigLoader();

        $this->assertSame([
            ServiceLocatorInterface::class => [
                FactoryResolver::class => [
                    RouterInterface::class => RouterFactory::class,
                ],
                StaticFactoryResolver::class => [
                    GroupRoute::class => GroupRoute::class,
                    HostRoute::class => HostRoute::class,
                    MethodRoute::class => MethodRoute::class,
                    PathRoute::class => PathRoute::class,
                    QueryRoute::class => QueryRoute::class,
                    SchemeRoute::class => SchemeRoute::class,
                ],
                ReflectionResolver::class => [
                    RouterMiddleware::class => RouterMiddleware::class,
                    ControllerMiddleware::class => ControllerMiddleware::class,
                ],
            ],
            RouterInterface::class => [
                'routes' => [],
            ],
        ], $loader->load());
    }
}