<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Controller\Exception\ActionNotFound;
use ExtendsFramework\Router\Controller\Exception\ParameterNotFound;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use PHPUnit\Framework\TestCase;

class AbstractControllerTest extends TestCase
{
    /**
     * Execute.
     *
     * Test that $request can be executed to $controller and $response will be returned.
     *
     * @covers \ExtendsFramework\Router\Controller\AbstractController::execute()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getMethod()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getPostfix()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getAction()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::normalizeAction()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getArguments()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getRequest()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getRouteMatch()
     */
    public function testExecute(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->method('getParameters')
            ->willReturn([
                'action' => 'foo.fancy-action',
                'someId' => 33,
            ]);

        /**
         * @var RequestInterface    $request
         * @var RouteMatchInterface $match
         */
        $controller = new ControllerStub();
        $response = $controller->execute($request, $match);

        $this->assertIsObject($response);
        $this->assertSame([
            'request' => $request,
            'routeMatch' => $match,
            'someId' => 33,
            'allowsNull' => null,
            'defaultValue' => 'string',
        ], $response->getBody());
    }

    /**
     * Action not found.
     *
     * Test that action attribute can not be found in $request and an exception will be thrown.
     *
     * @covers \ExtendsFramework\Router\Controller\AbstractController::execute()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getAction()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getMethod()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getPostfix()
     * @covers \ExtendsFramework\Router\Controller\Exception\ActionNotFound::__construct()
     */
    public function testActionNotFound(): void
    {
        $this->expectException(ActionNotFound::class);
        $this->expectExceptionMessage('No controller action was found in request.');

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->method('getParameters')
            ->willReturn([]);

        /**
         * @var RequestInterface    $request
         * @var RouteMatchInterface $match
         */
        $controller = new ControllerStub();
        $controller->execute($request, $match);
    }

    /**
     * Parameter not found.
     *
     * Test that parameter value can not be determined and an exception will be thrown.
     *
     * @covers \ExtendsFramework\Router\Controller\AbstractController::execute()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getAction()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getMethod()
     * @covers \ExtendsFramework\Router\Controller\AbstractController::getPostfix()
     * @covers \ExtendsFramework\Router\Controller\Exception\ParameterNotFound::__construct()
     */
    public function testParameterNotFound(): void
    {
        $this->expectException(ParameterNotFound::class);
        $this->expectExceptionMessage(
            'Parameter name "someId" can not be found in route match ' .
            'parameters and has no default value or allows null.'
        );

        $request = $this->createMock(RequestInterface::class);

        $match = $this->createMock(RouteMatchInterface::class);
        $match
            ->method('getParameters')
            ->willReturn([
                'action' => 'fooFancyAction',
            ]);

        /**
         * @var RequestInterface    $request
         * @var RouteMatchInterface $match
         */
        $controller = new ControllerStub();
        $controller->execute($request, $match);
    }
}
