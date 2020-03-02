<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Executor;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Controller\ControllerInterface;
use ExtendsFramework\Router\Controller\Exception\ActionNotFound;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerExecutionFailed;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerNotFound;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerParameterMissing;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Exception\ServiceNotFound;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class ExecutorTest extends TestCase
{
    /**
     * Execute.
     *
     * Test that controller will be executed and response will be returned.
     *
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::__construct()
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::execute()
     */
    public function testExecute(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'Foo',
            ]);

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
            ->with('Foo')
            ->willReturn($controller);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         * @var RequestInterface $request
         * @var RouteMatchInterface $match
         */
        $executor = new Executor($serviceLocator);
        $this->assertSame($response, $executor->execute($request, $match));
    }

    /**
     * Controller parameter missing.
     *
     * Test that exception will be thrown when route match parameter for controller is not set.
     *
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::__construct()
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::execute()
     * @covers \ExtendsFramework\Router\Controller\Executor\Exception\ControllerParameterMissing::__construct()
     */
    public function testControllerParameterMissing(): void
    {
        $this->expectException(ControllerParameterMissing::class);
        $this->expectExceptionMessage('Controller parameter is missing in route match.');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([]);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         * @var RequestInterface $request
         * @var RouteMatchInterface $match
         */
        $executor = new Executor($serviceLocator);
        $executor->execute($request, $match);
    }

    /**
     * Controller not found.
     *
     * Test that exception will be thrown when controller can not be received from service locator.
     *
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::__construct()
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::execute()
     * @covers \ExtendsFramework\Router\Controller\Executor\Exception\ControllerNotFound::__construct()
     */
    public function testControllerNotFound(): void
    {
        $this->expectException(ControllerNotFound::class);
        $this->expectExceptionMessage('Controller for key "Foo" can not be retrieved from service locator.');

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getService')
            ->with('Foo')
            ->willThrowException(new ServiceNotFound('Foo'));

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'Foo',
            ]);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         * @var RequestInterface $request
         * @var RouteMatchInterface $match
         */
        $executor = new Executor($serviceLocator);
        $executor->execute($request, $match);
    }

    /**
     * Controller execution failed.
     *
     * Test that exception will be thrown when controller execution fails.
     *
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::__construct()
     * @covers \ExtendsFramework\Router\Controller\Executor\Executor::execute()
     * @covers \ExtendsFramework\Router\Controller\Executor\Exception\ControllerExecutionFailed::__construct()
     */
    public function testControllerExecutionFailed(): void
    {
        $this->expectException(ControllerExecutionFailed::class);
        $this->expectExceptionMessage(
            'Failed to execute request to controller. See previous exception for more details.'
        );

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->expects($this->once())
            ->method('getParameters')
            ->willReturn([
                'controller' => 'Foo',
            ]);

        $controller = $this->createMock(ControllerInterface::class);
        $controller
            ->expects($this->once())
            ->method('execute')
            ->with($request, $match)
            ->willThrowException(new ActionNotFound());

        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $serviceLocator
            ->expects($this->once())
            ->method('getService')
            ->with('Foo')
            ->willReturn($controller);

        /**
         * @var ServiceLocatorInterface $serviceLocator
         * @var RequestInterface $request
         * @var RouteMatchInterface $match
         */
        $executor = new Executor($serviceLocator);
        $executor->execute($request, $match);
    }
}
