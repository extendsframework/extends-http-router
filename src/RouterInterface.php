<?php
declare(strict_types=1);

namespace ExtendsFramework\Router;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;

interface RouterInterface
{
    /**
     * Route $request to corresponding controller.
     *
     * An exception will be thrown when $request can not be matched. A route can throw an more detailed exception.
     *
     * @param RequestInterface $request
     * @return RouteMatchInterface
     * @throws RouterException
     */
    public function route(RequestInterface $request): RouteMatchInterface;

    /**
     * Assemble route $path into a request.
     *
     * @param string     $path       Consecutive route names separated with a forward slash.
     * @param array|null $parameters Parameters to use when assembling routes.
     * @return RequestInterface
     * @throws RouterException       When $path can not be found.
     */
    public function assemble(string $path, array $parameters = null): RequestInterface;
}
