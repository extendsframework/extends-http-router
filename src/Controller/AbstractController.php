<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Controller\Exception\ActionNotFound;
use ExtendsFramework\Router\Controller\Exception\ParameterNotFound;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ReflectionException;
use ReflectionMethod;

abstract class AbstractController implements ControllerInterface
{
    /**
     * String to append to the action.
     *
     * @var string
     */
    private $postfix = 'Action';

    /**
     * Request.
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Route match.
     *
     * @var RouteMatchInterface
     */
    private $routeMatch;

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function execute(RequestInterface $request, RouteMatchInterface $routeMatch): ResponseInterface
    {
        $this->request = $request;
        $this->routeMatch = $routeMatch;

        $parameters = $routeMatch->getParameters();
        if (!array_key_exists('action', $parameters)) {
            throw new ActionNotFound();
        }

        $action = str_replace(['_', '-', '.',], ' ', strtolower($parameters['action']));
        $action = lcfirst(str_replace(' ', '', ucwords($action)));
        $method = new ReflectionMethod($this, $action . $this->postfix);

        $parameters = $routeMatch->getParameters();
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (!array_key_exists($name, $parameters)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $arguments[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $arguments[] = null;
                } else {
                    throw new ParameterNotFound($name);
                }
            } else {
                $arguments[] = $parameters[$name];
            }
        }

        return $method->invokeArgs($this, $arguments);
    }

    /**
     * Get request.
     *
     * @return RequestInterface
     */
    protected function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Get route match.
     *
     * @return RouteMatchInterface
     */
    protected function getRouteMatch(): RouteMatchInterface
    {
        return $this->routeMatch;
    }
}
