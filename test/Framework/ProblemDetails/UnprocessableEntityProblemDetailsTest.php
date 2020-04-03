<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ProblemDetails;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Request\Uri\UriInterface;
use ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity;
use ExtendsFramework\Validator\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class UnprocessableEntityProblemDetailsTest extends TestCase
{
    /**
     * Test that getters will return correct values.
     *
     * @covers \ExtendsFramework\Router\Framework\ProblemDetails\UnprocessableEntityProblemDetails::__construct()
     */
    public function testGetters(): void
    {
        $result = $this->createMock(ResultInterface::class);

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

        $exception = $this->createMock(UnprocessableEntity::class);
        $exception
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($result);

        /**
         * @var RequestInterface $request
         * @var UnprocessableEntity $exception
         */
        $problemDetails = new UnprocessableEntityProblemDetails($request, $exception);

        $this->assertSame('/problems/router/unprocessable-entity', $problemDetails->getType());
        $this->assertSame('Unprocessable Entity', $problemDetails->getTitle());
        $this->assertSame('Request body is invalid.', $problemDetails->getDetail());
        $this->assertSame(422, $problemDetails->getStatus());
        $this->assertSame('/foo/bar', $problemDetails->getInstance());
        $this->assertSame(['result' => $result], $problemDetails->getAdditional());
    }
}
