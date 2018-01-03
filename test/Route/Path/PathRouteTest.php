<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Path;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\Result\ResultInterface;
use ExtendsFramework\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class PathRouteTest extends TestCase
{
    /**
     * Match.
     *
     * Test that path '/foo/:first_name/bar' will match '/:first_name/bar' and return an instance of
     * RouteMatchInterface.
     *
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::match()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getPattern()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getMatchedParameters()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getParameters()
     */
    public function testMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo/John/bar');

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
            ->with('John')
            ->willReturn($result);

        /**
         * @var RequestInterface $request
         */
        $path = new PathRoute('/:first_name/bar', [
            'first_name' => $validator,
        ], [
            'foo' => 'bar',
        ]);
        $match = $path->match($request, 4);

        $this->assertInstanceOf(RouteMatchInterface::class, $match);
        if ($match instanceof RouteMatchInterface) {
            $this->assertSame(13, $match->getPathOffset());
            $this->assertSame([
                'foo' => 'bar',
                'first_name' => 'John',
            ], $match->getParameters());
        }
    }

    /**
     * No match.
     *
     * Test that path will not match.
     *
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::match()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getValidators()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getPattern()
     */
    public function testNotMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo/bar/baz');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->never())
            ->method('validate');

        /**
         * @var RequestInterface $request
         */
        $path = new PathRoute('/bar/:id', [
            'id' => $validator,
        ]);
        $match = $path->match($request, 0);

        $this->assertNull($match);
    }

    /**
     * Assemble.
     *
     * Test that path will be set on request URI.
     *
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::assemble()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getPath()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getParameters()
     */
    public function testAssemble(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo');

        $uri
            ->expects($this->once())
            ->method('withPath')
            ->with('/foo/bar/33')
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
        $host = new PathRoute('/bar/:id');
        $host->assemble($request, [], [
            'id' => '33',
        ]);
    }

    /**
     * Parameter missing.
     *
     * Test that exception will be thrown when parameter is missing.
     *
     * @covers                   \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     * @covers                   \ExtendsFramework\Router\Route\Path\PathRoute::assemble()
     * @covers                   \ExtendsFramework\Router\Route\Path\PathRoute::getPath()
     * @covers                   \ExtendsFramework\Router\Route\Path\PathRoute::getParameters()
     * @covers                   \ExtendsFramework\Router\Route\Path\Exception\PathParameterMissing::__construct()
     * @expectedException        \ExtendsFramework\Router\Route\Path\Exception\PathParameterMissing
     * @expectedExceptionMessage Failed to assemble route, path parameter "id" is missing.
     */
    public function testParameterMissing(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        /**
         * @var RequestInterface $request
         */
        $host = new PathRoute('/bar/:id');
        $host->assemble($request, [], []);
    }

    /**
     * Invalid validator.
     *
     * Test that validator for 'id' is invalid and route won't match.
     *
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::match()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::getPattern()
     */
    public function testInvalidValidator(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('/foo/bar/baz');

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
        $path = new PathRoute('/:id/bar', [
            'id' => $validator,
        ]);
        $match = $path->match($request, 0);

        $this->assertNull($match);
    }

    /**
     * Factory.
     *
     * Test that factory will return an instance of RouteInterface.
     *
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::factory()
     * @covers \ExtendsFramework\Router\Route\Path\PathRoute::__construct()
     */
    public function testFactory(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->exactly(2))
            ->method('getService')
            ->withConsecutive(
                [
                    ValidatorInterface::class,
                    ['foo' => 'bar'],
                ],
                [
                    ValidatorInterface::class,
                    [],
                ]
            )
            ->willReturn($this->createMock(ValidatorInterface::class));

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $route = PathRoute::factory(PathRoute::class, $serviceLocator, [
            'path' => '/:id/bar/:baz',
            'validators' => [
                'id' => [
                    'name' => ValidatorInterface::class,
                    'options' => [
                        'foo' => 'bar',
                    ],
                ],
                'baz' => ValidatorInterface::class, // Short syntax will be converted to array with 'name' property.
            ],
            'parameters' => [
                'foo' => 'bar',
            ],
        ]);

        $this->assertInstanceOf(RouteInterface::class, $route);
    }
}
