<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ProblemDetails;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use PHPUnit\Framework\TestCase;

class MethodNotAllowedProblemDetailsTest extends TestCase
{
    /**
     * Test that getters will return correct values.
     *
     * @covers \ExtendsFramework\Router\Framework\ProblemDetails\MethodNotAllowedProblemDetails::__construct()
     */
    public function testGetters(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('toRelative')
            ->willReturn('/foo/bar');

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $exception = $this->createMock(MethodNotAllowed::class);
        $exception
            ->expects($this->exactly(2))
            ->method('getMethod')
            ->willReturn('GET');

        $exception
            ->expects($this->exactly(1))
            ->method('getAllowedMethods')
            ->willReturn(['PUT', 'POST']);

        /**
         * @var RequestInterface $request
         * @var MethodNotAllowed $exception
         */
        $problemDetails = new MethodNotAllowedProblemDetails($request, $exception);

        $this->assertSame('/problems/router/method-not-allowed', $problemDetails->getType());
        $this->assertSame('Method not allowed', $problemDetails->getTitle());
        $this->assertSame('Method "GET" is not allowed.', $problemDetails->getDetail());
        $this->assertSame(405, $problemDetails->getStatus());
        $this->assertSame('/foo/bar', $problemDetails->getInstance());
        $this->assertSame(['method' => 'GET', 'allowed_methods' => ['PUT', 'POST']], $problemDetails->getAdditional());
    }
}
