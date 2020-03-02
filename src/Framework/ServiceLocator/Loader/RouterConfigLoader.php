<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ServiceLocator\Loader;

use ExtendsFramework\Router\Controller\Executor\Executor;
use ExtendsFramework\Router\Controller\Executor\ExecutorInterface;
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
use ExtendsFramework\ServiceLocator\Config\Loader\LoaderInterface;
use ExtendsFramework\ServiceLocator\Resolver\Factory\FactoryResolver;
use ExtendsFramework\ServiceLocator\Resolver\Reflection\ReflectionResolver;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryResolver;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class RouterConfigLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    public function load(): array
    {
        return [
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
                    ExecutorInterface::class => Executor::class,
                ],
            ],
            RouterInterface::class => [
                'routes' => [],
            ],
        ];
    }
}
