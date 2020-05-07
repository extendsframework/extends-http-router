<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Router;

use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Exception\NotFound;
use ExtendsFramework\Router\Framework\ProblemDetails\InvalidQueryStringProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\MethodNotAllowedProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\NotFoundProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\QueryParameterMissingProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\InvalidRequestBodyProblemDetails;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\Method\Exception\InvalidRequestBody;
use ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString;
use ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\Router\RouterInterface;
use ExtendsFramework\Validator\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class RouterMiddlewareTest extends TestCase
{
    /**
     * Process.
     *
     * Test that route can be matched, controller will be executed and a response will be returned.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     */
    public function testMatch(): void
    {
        $match = $this->createMock(RouteMatchInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('andAttribute')
            ->with('routeMatch', $match)
            ->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);

        $chain = $this->createMock(MiddlewareChainInterface::class);
        $chain
            ->expects($this->once())
            ->method('proceed')
            ->with($request)
            ->willReturn($response);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willReturn($match);

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);

        $this->assertSame($response, $middleware->process($request, $chain));
    }

    /**
     * Not found.
     *
     * Test that when method is not allowed a 404 Not Found response will be returned.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     * @covers \ExtendsFramework\Router\Exception\NotFound::getRequest()
     */
    public function testNotFound(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('toRelative')
            ->willReturn('/foo/bar');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willThrowException(new NotFound($request));

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);
        $response = $middleware->process($request, $chain);

        $this->assertInstanceOf(NotFoundProblemDetails::class, $response->getBody());
    }

    /**
     * Method not allowed.
     *
     * Test that when method is not allowed a 405 Method Not Allowed response will be returned.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     */
    public function testMethodNotAllowed(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $exception = $this->createMock(MethodNotAllowed::class);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willThrowException($exception);

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);
        $response = $middleware->process($request, $chain);

        $this->assertInstanceOf(MethodNotAllowedProblemDetails::class, $response->getBody());
    }

    /**
     * Invalid query string.
     *
     * Test that invalid query string exception will be caught and returned as a 400 response.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     */
    public function testInvalidQueryString(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $result = $this->createMock(ResultInterface::class);

        $exception = $this->createMock(InvalidQueryString::class);
        $exception
            ->method('getParameter')
            ->willReturn('foo');

        $exception
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willThrowException($exception);

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);
        $response = $middleware->process($request, $chain);

        $this->assertInstanceOf(InvalidQueryStringProblemDetails::class, $response->getBody());
    }

    /**
     * Query parameter missing.
     *
     * Test that query parameter missing exception will be caught and returned as a 400 response.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     */
    public function testQueryParameterMissing(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $exception = $this->createMock(QueryParameterMissing::class);
        $exception
            ->method('getParameter')
            ->willReturn('phrase');

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willThrowException($exception);

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);
        $response = $middleware->process($request, $chain);

        $this->assertInstanceOf(QueryParameterMissingProblemDetails::class, $response->getBody());
    }

    /**
     * Invalid request body.
     *
     * Test that when request body is invalid the correct problem details will be returned.
     *
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::__construct()
     * @covers \ExtendsFramework\Router\Framework\Http\Middleware\Router\RouterMiddleware::process()
     */
    public function testInvalidRequestBody(): void
    {
        $chain = $this->createMock(MiddlewareChainInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $exception = $this->createMock(InvalidRequestBody::class);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects($this->once())
            ->method('route')
            ->with($request)
            ->willThrowException($exception);

        /**
         * @var RouterInterface $router
         * @var RequestInterface $request
         * @var MiddlewareChainInterface $chain
         */
        $middleware = new RouterMiddleware($router);
        $response = $middleware->process($request, $chain);

        $this->assertInstanceOf(InvalidRequestBodyProblemDetails::class, $response->getBody());
    }
}
