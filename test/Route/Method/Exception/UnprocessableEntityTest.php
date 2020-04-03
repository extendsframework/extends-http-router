<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Method\Exception;

use ExtendsFramework\Validator\Result\ResultInterface;
use PHPUnit\Framework\TestCase;

class UnprocessableEntityTest extends TestCase
{
    /**
     * Get result.
     *
     * Test that correct result will be returned.
     *
     * @covers \ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity::__construct()
     * @covers \ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity::getResult()
     */
    public function testGetResult(): void
    {
        $result = $this->createMock(ResultInterface::class);

        /**
         * @var ResultInterface $result
         */
        $exception = new UnprocessableEntity($result);

        $this->assertSame($result, $exception->getResult());
    }
}
