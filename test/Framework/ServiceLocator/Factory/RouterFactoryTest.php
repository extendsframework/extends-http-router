<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ServiceLocator\Factory;

use ExtendsFramework\Router\Route\Group\GroupRoute;
use ExtendsFramework\Router\Route\Method\MethodRoute;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\Scheme\SchemeRoute;
use ExtendsFramework\Router\RouterInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class RouterFactoryTest extends TestCase
{
    /**
     * Create service.
     *
     * Test that factory will return an instance of RouterInterface.
     *
     * @covers \ExtendsFramework\Router\Framework\ServiceLocator\Factory\RouterFactory::createService()
     * @covers \ExtendsFramework\Router\Framework\ServiceLocator\Factory\RouterFactory::createRoute()
     * @covers \ExtendsFramework\Router\Framework\ServiceLocator\Factory\RouterFactory::createGroup()
     */
    public function testCreateService(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn([
                RouterInterface::class => [
                    'routes' => [
                        'scheme' => [
                            'name' => SchemeRoute::class,
                            'options' => [
                                'scheme' => 'https',
                                'parameters' => [
                                    'foo' => 'bar',
                                ],
                            ],
                            'abstract' => false,
                            'children' => [
                                'post' => [
                                    'name' => MethodRoute::class,
                                    'options' => [
                                        'method' => MethodRoute::METHOD_POST,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);


        $route1 = $this->createMock(RouteInterface::class);

        $route2 = $this->createMock(RouteInterface::class);

        $group = $this->createMock(GroupRoute::class);
        $group
            ->method('addRoute')
            ->with($route2)
            ->willReturnSelf();

        $serviceLocator
            ->expects($this->at(1))
            ->method('getService')
            ->with(SchemeRoute::class, [
                'scheme' => 'https',
                'parameters' => [
                    'foo' => 'bar',
                ],
            ])
            ->willReturn($route1);

        $serviceLocator
            ->expects($this->at(2))
            ->method('getService')
            ->with(GroupRoute::class, [
                'route' => $route1,
                'abstract' => null,
            ])
            ->willReturn($group);

        $serviceLocator
            ->expects($this->at(3))
            ->method('getService')
            ->with(MethodRoute::class, [
                'method' => MethodRoute::METHOD_POST,
            ])
            ->willReturn($route2);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $factory = new RouterFactory();
        $router = $factory->createService(RouterInterface::class, $serviceLocator, []);

        $this->assertInstanceOf(RouterInterface::class, $router);
    }
}
