<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Http\Router\Exception\NotFound;
use ExtendsFramework\Http\Router\Route\Group\GroupRoute;
use ExtendsFramework\Http\Router\Route\RouteInterface;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * Match.
     *
     * Test that router can match route and return RouteMatchInterface.
     *
     * @covers \ExtendsFramework\Http\Router\Router::addRoute()
     * @covers \ExtendsFramework\Http\Router\Router::route()
     * @covers \ExtendsFramework\Http\Router\Routes::addRoute()
     * @covers \ExtendsFramework\Http\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Http\Router\Routes::getRoutes()
     */
    public function testMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo');

        $uri
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn([
                'foo' => 'bar',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getPathOffset')
            ->willReturn(4);

        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'foo' => 'bar',
            ]);

        $route = $this->createMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('match')
            ->with($request)
            ->willReturn($match);

        /**
         * @var RouteInterface   $route
         * @var RequestInterface $request
         */
        $router = new Router();
        $matched = $router
            ->addRoute($route, 'route')
            ->route($request);

        $this->assertSame($match, $matched);
    }

    /**
     * No match.
     *
     * Test that router can not match route and will return null.
     *
     * @covers \ExtendsFramework\Http\Router\Router::route()
     * @covers \ExtendsFramework\Http\Router\Routes::matchRoutes()
     * @covers \ExtendsFramework\Http\Router\Routes::getRoutes()
     * @covers \ExtendsFramework\Http\Router\Exception\NotFound::getRequest()
     */
    public function testNoMatch(): void
    {
        $request = $this->createMock(RequestInterface::class);

        /**
         * @var RequestInterface $request
         */
        $router = new Router();

        try {
            $router->route($request);
        } catch (NotFound $exception) {
            $this->assertSame($request, $exception->getRequest());
        }
    }

    /**
     * Path offset mismatch.
     *
     * Test that a partial URI path can not be matched.
     *
     * @covers                   \ExtendsFramework\Http\Router\Router::route()
     * @covers                   \ExtendsFramework\Http\Router\Routes::matchRoutes()
     * @covers                   \ExtendsFramework\Http\Router\Routes::getRoutes()
     * @covers                   \ExtendsFramework\Http\Router\Exception\NotFound::__construct()
     * @expectedException        \ExtendsFramework\Http\Router\Exception\NotFound
     * @expectedExceptionMessage Request could not be matched by a route.
     */
    public function testPathOffsetMismatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getPathOffset')
            ->willReturn(4);

        $route = $this->createMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('match')
            ->with($request)
            ->willReturn($match);

        /**
         * @var RouteInterface   $route
         * @var RequestInterface $request
         */
        $router = new Router();
        $matched = $router
            ->addRoute($route, 'route')
            ->route($request);

        $this->assertSame($match, $matched);
    }

    /**
     * Too much query parameters.
     *
     * Test that more then the allowed query string parameters will return in an exception.
     *
     * @covers                   \ExtendsFramework\Http\Router\Router::route()
     * @covers                   \ExtendsFramework\Http\Router\Routes::matchRoutes()
     * @covers                   \ExtendsFramework\Http\Router\Routes::getRoutes()
     * @covers                   \ExtendsFramework\Http\Router\Exception\NotFound::__construct()
     * @expectedException        \ExtendsFramework\Http\Router\Exception\NotFound
     * @expectedExceptionMessage Request could not be matched by a route.
     */
    public function testTooMuchQueryParameters(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo');

        $uri
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn([
                'foo' => 'bar',
                'qux' => 'quux',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->exactly(2))
            ->method('getUri')
            ->willReturn($uri);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getPathOffset')
            ->willReturn(4);

        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'foo' => 'bar',
            ]);

        $route = $this->createMock(RouteInterface::class);
        $route
            ->expects($this->once())
            ->method('match')
            ->with($request)
            ->willReturn($match);

        /**
         * @var RouteInterface   $route
         * @var RequestInterface $request
         */
        $router = new Router();
        $router
            ->addRoute($route, 'route')
            ->route($request);
    }

    /**
     * Assemble.
     *
     * Test that route will be assembled and request will be returned.
     *
     * @covers \ExtendsFramework\Http\Router\Router::assemble()
     */
    public function testAssemble(): void
    {
        $route = $this->createMock(GroupRoute::class);
        $route
            ->expects($this->once())
            ->method('assemble')
            ->with(
                $this->isInstanceOf(RequestInterface::class),
                ['bar', 'baz'],
                ['foo' => 'bar']
            )
            ->willReturn($this->createMock(RequestInterface::class));

        /**
         * @var RouteInterface   $route
         * @var RequestInterface $request
         */
        $router = new Router();
        $request = $router
            ->addRoute($route, 'foo')
            ->assemble('foo/bar/baz', ['foo' => 'bar']);

        $this->assertInstanceOf(RequestInterface::class, $request);
    }

    /**
     * Invalid route path.
     *
     * Test that exception will be thrown when route path is invalid.
     *
     * @covers                   \ExtendsFramework\Http\Router\Router::assemble()
     * @covers                   \ExtendsFramework\Http\Router\Exception\InvalidRoutePath::__construct()
     * @expectedException        \ExtendsFramework\Http\Router\Exception\InvalidRoutePath
     * @expectedExceptionMessage Invalid router assemble path, got "/foo/".
     */
    public function testInvalidRoutePath(): void
    {
        $router = new Router();
        $router->assemble('/foo/');
    }
}
