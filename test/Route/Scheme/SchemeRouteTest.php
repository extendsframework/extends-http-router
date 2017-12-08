<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route\Scheme;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Http\Router\Route\RouteInterface;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class SchemeRouteTest extends TestCase
{
    /**
     * Match.
     *
     * Test that route will match scheme HTTPS and return instance of RouteMatchInterface.
     *
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::factory()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::__construct()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::match()
     */
    public function testMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getScheme')
            ->willReturn('https');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        /**
         * @var RequestInterface $request
         */
        $scheme = new SchemeRoute('https', [
            'foo' => 'bar',
        ]);
        $match = $scheme->match($request, 5);

        $this->assertInstanceOf(RouteMatchInterface::class, $match);
        if ($match instanceof RouteMatchInterface) {
            $this->assertSame(5, $match->getPathOffset());
            $this->assertSame([
                'foo' => 'bar',
            ], $match->getParameters());
        }
    }

    /**
     * No match.
     *
     * Test that route will not match scheme HTTP and will return null.
     *
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::factory()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::__construct()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::match()
     */
    public function testNoMatch(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('getScheme')
            ->willReturn('http');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        /**
         * @var RequestInterface $request
         */
        $scheme = new SchemeRoute('https');
        $match = $scheme->match($request, 5);

        $this->assertNull($match);
    }

    /**
     * Assemble.
     *
     * Test that scheme will be set on request URI.
     *
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::__construct()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::assemble()
     */
    public function testAssemble(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withScheme')
            ->with('HTTPS')
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
        $host = new SchemeRoute('https');
        $host->assemble($request, [], []);
    }

    /**
     * Factory.
     *
     * Test that factory will return an instance of RouteInterface.
     *
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::factory()
     * @covers \ExtendsFramework\Http\Router\Route\Scheme\SchemeRoute::__construct()
     */
    public function testFactory(): void
    {
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         */
        $route = SchemeRoute::factory(SchemeRoute::class, $serviceLocator, [
            'scheme' => 'https',
            'parameters' => [
                'foo' => 'bar',
            ],
        ]);

        $this->assertInstanceOf(RouteInterface::class, $route);
    }
}
