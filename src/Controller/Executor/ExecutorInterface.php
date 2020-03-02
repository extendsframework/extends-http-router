<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Executor;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;

interface ExecutorInterface
{
    /**
     * Execute request and route match to controller and return response.
     *
     * @param RequestInterface $request
     * @param RouteMatchInterface $routeMatch
     *
     * @return ResponseInterface
     * @throws ExecutorException
     */
    public function execute(RequestInterface $request, RouteMatchInterface $routeMatch): ResponseInterface;
}
