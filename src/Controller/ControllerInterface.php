<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;

interface ControllerInterface
{
    /**
     * Execute controller with request and route match.
     *
     * Method must return result as an array. When there is no result to result, this method must return an empty
     * array. When no method can be found, an exception will be thrown.
     *
     * @param RequestInterface    $request
     * @param RouteMatchInterface $routeMatch
     * @return ResponseInterface
     * @throws ControllerException
     */
    public function execute(RequestInterface $request, RouteMatchInterface $routeMatch): ResponseInterface;
}
