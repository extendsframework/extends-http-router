<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route;

use PHPUnit\Framework\TestCase;

class RouteMatchTest extends TestCase
{
    /**
     * Get methods.
     *
     * Test that get methods will return correct values.s
     *
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::__construct()
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::getParameters()
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::getPathOffset()
     */
    public function testGetMethods(): void
    {
        $match = new RouteMatch(['foo' => 'bar'], 15);

        $this->assertSame(['foo' => 'bar'], $match->getParameters());
        $this->assertSame(15, $match->getPathOffset());
    }

    /**
     * Merge.
     *
     * Test that two RouteMatchInterface instances can merge in a third.
     *
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::__construct()
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::merge()
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::getParameters()
     * @covers \ExtendsFramework\Http\Router\Route\RouteMatch::getPathOffset()
     */
    public function testMerge(): void
    {
        $match1 = new RouteMatch([
            'foo' => 'bar',
        ], 10);
        $match2 = new RouteMatch([
            'baz' => 'qux',
        ], 15);
        $match3 = $match1->merge($match2);

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'qux',
        ], $match3->getParameters());
        $this->assertSame(15, $match3->getPathOffset());
    }
}
