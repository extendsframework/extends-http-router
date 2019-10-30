<?php
declare(strict_types=1);

namespace ExtendsFramework\Router;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Exception\GroupRouteExpected;
use ExtendsFramework\Router\Exception\RouteNotFound;
use ExtendsFramework\Router\Route\Group\GroupRoute;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\Method\MethodRoute;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use PHPUnit\Framework\TestCase;

class RoutesTest extends TestCase
{
    /**
     * Match.
     *
     * Test that route will be matched and returned.
     *
     * @covers \ExtendsFramework\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Router\Routes::getRoutes()
     */
    public function testMatch(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);

        $route1 = $this->createMock(RouteInterface::class);
        $route1
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willReturn(null);

        $route2 = $this->createMock(RouteInterface::class);
        $route2
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willReturn($match);

        /**
         * @var RequestInterface $request
         * @var RouteInterface   $route1
         * @var RouteInterface   $route2
         */
        $routes = new RoutesStub();
        $matched = $routes
            ->addRoute($route1, 'route1')
            ->addRoute($route2, 'route2')
            ->match($request);

        $this->assertSame($match, $matched);
    }

    /**
     * Not match.
     *
     * Test that no route will be matched and null will be returned.
     *
     * @covers \ExtendsFramework\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Router\Routes::getRoutes()
     */
    public function testNoMatch(): void
    {
        $request = $this->createMock(RequestInterface::class);

        /**
         * @var RequestInterface $request
         */
        $routes = new RoutesStub();

        $this->assertNull($routes->match($request));
    }

    /**
     * Method not allowed.
     *
     * Test that none of the method routes is allowed and exception will be thrown with allowed methods.
     *
     * @covers \ExtendsFramework\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Router\Routes::getRoutes()
     */
    public function testMethodNotAllowed(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $route1 = $this->createMock(MethodRoute::class);
        $route1
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willThrowException(new MethodNotAllowed('GET', ['POST', 'PUT']));

        $route2 = $this->createMock(MethodRoute::class);
        $route2
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willThrowException(new MethodNotAllowed('GET', ['DELETE']));

        /**
         * @var RouteInterface   $route1
         * @var RouteInterface   $route2
         * @var RequestInterface $request
         */
        $routes = new RoutesStub();
        $routes
            ->addRoute($route1, 'route1')
            ->addRoute($route2, 'route2');

        try {
            $routes->match($request);
        } catch (MethodNotAllowed $exception) {
            $this->assertSame(['POST', 'PUT', 'DELETE'], $exception->getAllowedMethods());
        }
    }

    /**
     * Method allowed.
     *
     * Test that second method route is allowed and first exception not will be thrown.
     *
     * @covers \ExtendsFramework\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Router\Routes::getRoutes()
     */
    public function testMethodAllowed(): void
    {
        $match = $this->createMock(RouteMatchInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $route1 = $this->createMock(MethodRoute::class);
        $route1
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willThrowException(new MethodNotAllowed('GET', ['POST', 'PUT']));

        $route2 = $this->createMock(MethodRoute::class);
        $route2
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willReturn($match);

        /**
         * @var RouteInterface   $route1
         * @var RouteInterface   $route2
         * @var RequestInterface $request
         */
        $routes = new RoutesStub();
        $matched = $routes
            ->addRoute($route1, 'route1')
            ->addRoute($route2, 'route2')
            ->match($request);

        $this->assertSame($match, $matched);
    }

    /**
     * Route order.
     *
     * Test that group route will be matched first.
     *
     * @covers \ExtendsFramework\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Router\Routes::getRoutes()
     */
    public function testRouteOrder(): void
    {
        $match = $this->createMock(RouteMatchInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $route1 = $this->createMock(MethodRoute::class);
        $route1
            ->expects($this->never())
            ->method('match');

        $route2 = $this->createMock(GroupRoute::class);
        $route2
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willReturn(null);

        $route3 = $this->createMock(GroupRoute::class);
        $route3
            ->expects($this->once())
            ->method('match')
            ->with($request, 0)
            ->willReturn($match);

        /**
         * @var RouteInterface   $route1
         * @var RouteInterface   $route2
         * @var RouteInterface   $route3
         * @var RequestInterface $request
         */
        $routes = new RoutesStub();
        $matched = $routes
            ->addRoute($route1, 'route1')
            ->addRoute($route2, 'route2')
            ->addRoute($route3, 'route3')
            ->match($request);

        $this->assertSame($match, $matched);
    }

    /**
     * Get route.
     *
     * Test that route with name will be returned.
     *
     * @covers \ExtendsFramework\Router\Routes::getRoute()
     */
    public function testGetRoute(): void
    {
        $route = $this->createMock(RouteInterface::class);

        /**
         * @var RouteInterface $route
         */
        $router = new RoutesStub();
        $found = $router
            ->addRoute($route, 'foo')
            ->route('foo', false);

        $this->assertSame($route, $found);
    }

    /**
     * Route not found.
     *
     * Test that route can not be found and an exception will be thrown.
     *
     * @covers \ExtendsFramework\Router\Routes::getRoute()
     * @covers \ExtendsFramework\Router\Exception\RouteNotFound::__construct()
     *
     */
    public function testRouteNotFound(): void
    {
        $this->expectException(RouteNotFound::class);
        $this->expectExceptionMessage('Route for name "foo" can not be found.');

        $router = new RoutesStub();
        $router->route('foo');
    }

    /**
     * Group route expected.
     *
     * Test that and exception will be thrown when group route is expected but not returned.
     *
     * @covers \ExtendsFramework\Router\Routes::getRoute()
     * @covers \ExtendsFramework\Router\Exception\GroupRouteExpected::__construct()
     */
    public function testGroupRouteExpected(): void
    {
        $this->expectException(GroupRouteExpected::class);
        $this->expectExceptionMessageMatches(
            '/^A group route was expected, but an instance of "([^"]+)" was returned.$/'
        );

        $route = $this->createMock(RouteInterface::class);

        /**
         * @var RouteInterface $route
         */
        $router = new RoutesStub();
        $router
            ->addRoute($route, 'foo')
            ->route('foo', true);
    }
}
