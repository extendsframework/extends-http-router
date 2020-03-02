<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Executor;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Controller\ControllerException;
use ExtendsFramework\Router\Controller\ControllerInterface;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerExecutionFailed;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerNotFound;
use ExtendsFramework\Router\Controller\Executor\Exception\ControllerParameterMissing;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class Executor implements ExecutorInterface
{
    /**
     * Service locator.
     *
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * Executor constructor.
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @inheritDoc
     */
    public function execute(RequestInterface $request, RouteMatchInterface $routeMatch): ResponseInterface
    {
        $parameters = $routeMatch->getParameters();
        if (!isset($parameters['controller'])) {
            throw new ControllerParameterMissing();
        }

        $key = $parameters['controller'];
        try {
            /** @var ControllerInterface $controller */
            $controller = $this->serviceLocator->getService($key);
        } catch (ServiceLocatorException $exception) {
            throw new ControllerNotFound($key, $exception);
        }

        try {
            return $controller->execute($request, $routeMatch);
        } catch (ControllerException $exception) {
            throw new ControllerExecutionFailed($exception);
        }
    }
}
