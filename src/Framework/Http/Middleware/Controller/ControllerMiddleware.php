<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller;

use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Middleware\MiddlewareInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Http\Router\Controller\ControllerException;
use ExtendsFramework\Http\Router\Controller\ControllerInterface;
use ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\Exception\ControllerDispatchFailed;
use ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\Exception\ControllerNotFound;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    /**
     * Service locator.
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * ControllerMiddleware constructor.
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
    public function process(RequestInterface $request, MiddlewareChainInterface $chain): ResponseInterface
    {
        $match = $request->getAttribute('routeMatch');
        if ($match instanceof RouteMatchInterface) {
            $parameters = $match->getParameters();
            if (array_key_exists('controller', $parameters) === true) {
                try {
                    $controller = $this->getController($parameters['controller']);
                } catch (ServiceLocatorException $exception) {
                    throw new ControllerNotFound($parameters['controller'], $exception);
                }

                try {
                    return $controller->dispatch($request, $match);
                } catch (ControllerException $exception) {
                    throw new ControllerDispatchFailed($exception);
                }
            }
        }

        return $chain->proceed($request);
    }

    /**
     * Get controller for $key from the service locator.
     *
     * @param string $key
     * @return ControllerInterface
     * @throws ServiceLocatorException
     */
    protected function getController(string $key): ControllerInterface
    {
        return $this->serviceLocator->getService($key);
    }
}
