<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Query;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString;
use ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\Result\ResultInterface;
use ExtendsFramework\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class QueryRouteTest extends TestCase
{
    /**
     * Match.
     *
     * Test that route will match '?limit=20&offset=0' and return an instance of RouteMatchInterface
     *
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::match()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     */
    public function testMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn([
                'offset' => '0',
                'limit' => '20',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $result = $this->createMock(ResultInterface::class);
        $result
            ->expects($this->exactly(2))
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->exactly(2))
            ->method('validate')
            ->withConsecutive(
                ['20'],
                ['0']
            )
            ->willReturn($result);

        /**
         * @var RequestInterface $request
         */
        $path = new QueryRoute([
            'limit' => $validator,
            'offset' => $validator,
        ], [
            'offset' => '0',
        ]);
        $match = $path->match($request, 4);

        $this->assertInstanceOf(RouteMatchInterface::class, $match);
        if ($match instanceof RouteMatchInterface) {
            $this->assertSame(4, $match->getPathOffset());
            $this->assertSame([
                'offset' => '0',
                'limit' => '20',
            ], $match->getParameters());
        }
    }

    /**
     * No match.
     *
     * Test that route will not match empty query and return null.
     *
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::match()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     * @covers \ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString::getParameter()
     * @covers \ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString::getResult()
     */
    public function testNoMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn([
                'limit' => 'foo',
                'offset' => 'bar',
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $result = $this->createMock(ResultInterface::class);
        $result
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('foo')
            ->willReturn($result);

        /**
         * @var RequestInterface $request
         */
        $path = new QueryRoute([
            'limit' => $validator,
            'offset' => $validator,
        ]);

        try {
            $path->match($request, 4);
        } catch (InvalidQueryString $exception) {
            $this->assertSame(
                'Query string parameter "limit" failed to validate.',
                $exception->getMessage()
            );
            $this->assertSame('limit', $exception->getParameter());
            $this->assertSame($result, $exception->getResult());
        }
    }

    /**
     * Assemble.
     *
     * Test that query string will be set on request URI.
     *
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::assemble()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     */
    public function testAssemble(): void
    {
        $result = $this->createMock(ResultInterface::class);
        $result
            ->expects($this->exactly(3))
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->exactly(3))
            ->method('validate')
            ->withConsecutive(
                ['foo'],
                [20],
                [10]
            )
            ->willReturn($result);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn([
                'offset' => 5,
            ]);

        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with([
                'phrase' => 'foo',
                'offset' => 10,
            ])
            ->willReturnSelf();

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->expects($this->once())
            ->method('withUri')
            ->with($uri)
            ->willReturnSelf();

        /**
         * @var RequestInterface $request
         */
        $host = new QueryRoute([
            'phrase' => $validator,
            'limit' => $validator,
            'offset' => $validator,
        ], [
            'limit' => 20,
            'offset' => 0,
        ]);
        $host->assemble($request, [], [
            'phrase' => 'foo',
            'limit' => 20,
            'offset' => 10,
        ]);
    }

    /**
     * Query parameter missing.
     *
     * Test that exception will be thrown when required query parameter is missing.
     *
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::assemble()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     * @expectedException \ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing
     */
    public function testQueryParameterMissing(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);

        $request = $this->createMock(RequestInterface::class);

        /**
         * @var RequestInterface $request
         */
        $host = new QueryRoute([
            'phrase' => $validator,
        ]);
        $host->assemble($request, [], []);
    }

    /**
     * Invalid query string.
     *
     * Test that exception will be thrown when query string parameter is invalid.
     *
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::assemble()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers            \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     * @expectedException \ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString
     */
    public function testInvalidQueryString(): void
    {
        $result = $this->createMock(ResultInterface::class);
        $result
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('foo')
            ->willReturn($result);

        $request = $this->createMock(RequestInterface::class);

        /**
         * @var RequestInterface $request
         */
        $host = new QueryRoute([
            'phrase' => $validator,
        ]);
        $host->assemble($request, [], [
            'phrase' => 'foo',
        ]);
    }

    /**
     * Validator without default.
     *
     * Test that a missing query parameter, without default value, will thrown an exception.
     *
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::match()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::getParameters()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::replaceParameters()
     * @covers \ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing::__construct()
     * @covers \ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing::getParameter()
     */
    public function testValidatorWithoutDefault(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn([
                'limit' => 20,
            ]);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $result = $this->createMock(ResultInterface::class);
        $result
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with(20)
            ->willReturn($result);

        /**
         * @var RequestInterface $request
         */
        $path = new QueryRoute([
            'limit' => $validator,
            'offset' => $validator,
        ]);

        try {
            $path->match($request, 4);
        } catch (QueryParameterMissing $exception) {
            $this->assertSame('offset', $exception->getParameter());
        }
    }

    /**
     * Factory.
     *
     * Test that factory will return an instance of RouteInterface.
     *
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::factory()
     * @covers \ExtendsFramework\Router\Route\Query\QueryRoute::__construct()
     */
    public function testFactory(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->exactly(2))
            ->method('getService')
            ->withConsecutive(
                [ValidatorInterface::class, ['foo' => 'bar',]],
                [ValidatorInterface::class, []]
            )
            ->willReturn($validator);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $route = QueryRoute::factory(QueryRoute::class, $serviceLocator, [
            'path' => '/:id/bar',
            'validators' => [
                'limit' => [
                    'name' => ValidatorInterface::class,
                    'options' => [
                        'foo' => 'bar',
                    ],
                ],
                'offset' => ValidatorInterface::class, // Short syntax will be converted to array with 'name' property.
            ],
            'parameters' => [
                'offset' => '0',
            ],
        ]);

        $this->assertInstanceOf(RouteInterface::class, $route);
    }
}
