<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Controller;

use ExtendsFramework\Router\Controller\ControllerInterface;
use ExtendsFramework\Router\Controller\Exception\ActionNotFound;
use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Exception\ServiceNotFound;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class ControllerMiddlewareTest extends TestCase
{
    /**
     * Process.
     *
     * Test that controller will be executed with request and route match.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::process()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::getController()
     */
    public function testProcess(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'foo',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with('routeMatch')
            ->willReturn($match);

        $response = $this->createMock(ResponseInterface::class);

        $controller = $this->createMock(ControllerInterface::class);
        $controller
            ->expects($this->once())
            ->method('execute')
            ->with($request, $match)
            ->willReturn($response);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getService')
            ->with('foo')
            ->willReturn($controller);

        /**
         * @var ServiceLocatorInterface  $serviceLocator
         * @var RequestInterface         $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new ControllerMiddleware($serviceLocator);

        $this->assertSame($response, $middleware->process($request, $chain));
    }

    /**
     * Controller not found.
     *
     * Test that when route match parameter 'controller' is not set the exception ControllerNotFound is thrown.
     *
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::__construct()
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::process()
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\Exception\ControllerNotFound::__construct()
     * @expectedException        \ExtendsFramework\Router\Framework\Http\Middleware\Controller\Exception\ControllerNotFound
     * @expectedExceptionMessage Controller for key "foo" can not be retrieved from service locator.
     */
    public function testControllerNotFound(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'foo',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with('routeMatch')
            ->willReturn($match);

        $exception = $this->createMock(ServiceNotFound::class);

        /**
         * @var ServiceNotFound $exception
         */
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getService')
            ->with('foo')
            ->willThrowException($exception);

        /**
         * @var ServiceLocatorInterface  $serviceLocator
         * @var RequestInterface         $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new ControllerMiddleware($serviceLocator);
        $middleware->process($request, $chain);
    }

    /**
     * Controller execute failed.
     *
     * Test that a ControllerException can be caught and the exception ControllerExecutionFailed will be thrown.
     *
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::__construct()
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::process()
     * @covers                   \ExtendsFramework\Router\Framework\Http\Middleware\Controller\Exception\ControllerExecutionFailed::__construct()
     * @expectedException        \ExtendsFramework\Router\Framework\Http\Middleware\Controller\Exception\ControllerExecutionFailed
     * @expectedExceptionMessage Failed to execute request to controller. See previous exception for more details.
     */
    public function testControllerExecuteFailed(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'foo',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with('routeMatch')
            ->willReturn($match);

        $exception = $this->createMock(ActionNotFound::class);

        /**
         * @var ActionNotFound $exception
         */
        $controller = $this->createMock(ControllerInterface::class);
        $controller
            ->expects($this->once())
            ->method('execute')
            ->with($request, $match)
            ->willThrowException($exception);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getService')
            ->with('foo')
            ->willReturn($controller);

        /**
         * @var ServiceLocatorInterface  $serviceLocator
         * @var RequestInterface         $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new ControllerMiddleware($serviceLocator);
        $middleware->process($request, $chain);
    }

    /**
     * No route match.
     *
     * Test that when route match is not available the middleware chain is called.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::process()
     */
    public function testNoRouteMatch(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with('routeMatch')
            ->willReturn(null);

        $response = $this->createMock(ResponseInterface::class);

        $chain = $this->createMock(MiddlewareChainInterface::class);
        $chain
            ->expects($this->once())
            ->method('proceed')
            ->with($request)
            ->willReturn($response);

        /**
         * @var ServiceLocatorInterface  $serviceLocator
         * @var RequestInterface         $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new ControllerMiddleware($serviceLocator);

        $this->assertSame($response, $middleware->process($request, $chain));
    }

    /**
     * No controller parameters.
     *
     * Test that when controller parameter is not available on route match the middleware chain is called.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddleware::process()
     */
    public function testNoControllerParameter(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with('routeMatch')
            ->willReturn($match);

        $response = $this->createMock(ResponseInterface::class);

        $chain = $this->createMock(MiddlewareChainInterface::class);
        $chain
            ->expects($this->once())
            ->method('proceed')
            ->with($request)
            ->willReturn($response);

        /**
         * @var ServiceLocatorInterface  $serviceLocator
         * @var RequestInterface         $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new ControllerMiddleware($serviceLocator);

        $this->assertSame($response, $middleware->process($request, $chain));
    }
}
