<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Controller;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\Response;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use PHPUnit\Framework\TestCase;

class AbstractControllerTest extends TestCase
{
    /**
     * Dispatch.
     *
     * Test that $request can be dispatched to $controller and $response will be returned.
     *
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::dispatch()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::getMethod()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::getAction()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::normalizeAction()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::getArguments()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::getRequest()
     * @covers \ExtendsFramework\Http\Router\Controller\AbstractController::getRouteMatch()
     */
    public function testDispatch(): void
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
        $response = $controller->dispatch($request, $match);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        if ($response instanceof ResponseInterface) {
            $this->assertSame([
                'request' => $request,
                'routeMatch' => $match,
                'someId' => 33,
                'allowsNull' => null,
                'defaultValue' => 'string',
            ], $response->getBody());
        }
    }

    /**
     * Action not found.
     *
     * Test that action attribute can not be found in $request and an exception will be thrown.
     *
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::dispatch()
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::getAction()
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::getMethod()
     * @covers                   \ExtendsFramework\Http\Router\Controller\Exception\ActionNotFound::__construct()
     * @expectedException        \ExtendsFramework\Http\Router\Controller\Exception\ActionNotFound
     * @expectedExceptionMessage No controller action was found in request.
     */
    public function testActionNotFound(): void
    {
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
        $controller->dispatch($request, $match);
    }

    /**
     * Parameter not found.
     *
     * Test that parameter value can not be determined and an exception will be thrown.
     *
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::dispatch()
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::getAction()
     * @covers                   \ExtendsFramework\Http\Router\Controller\AbstractController::getMethod()
     * @covers                   \ExtendsFramework\Http\Router\Controller\Exception\ParameterNotFound::__construct()
     * @expectedException        \ExtendsFramework\Http\Router\Controller\Exception\ParameterNotFound
     * @expectedExceptionMessage Parameter with name "someId" can not be found in route match parameters and has no
     *                           default value or allows null.
     */
    public function testParameterNotFound(): void
    {
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
        $controller->dispatch($request, $match);
    }
}

class ControllerStub extends AbstractController
{
    /**
     * @param int       $someId
     * @param bool|null $allowsNull
     * @param string    $defaultValue
     * @return ResponseInterface
     */
    public function fooFancyActionAction(int $someId, ?bool $allowsNull, string $defaultValue = 'string'): ResponseInterface
    {
        return (new Response())
            ->withBody([
                'request' => $this->getRequest(),
                'routeMatch' => $this->getRouteMatch(),
                'someId' => $someId,
                'allowsNull' => $allowsNull,
                'defaultValue' => $defaultValue,
            ]);
    }
}
