<?php
declare(strict_types=1);

namespace ExtendsFramework\Router;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Exception\GroupRouteExpected;
use ExtendsFramework\Router\Exception\RouteNotFound;
use ExtendsFramework\Router\Route\RouteException;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;

class RoutesStub
{
    use Routes;

    /**
     * @param RequestInterface $request
     * @return RouteMatchInterface|null
     * @throws RouteException
     */
    public function match(RequestInterface $request): ?RouteMatchInterface
    {
        return $this->matchRoutes($request, 0);
    }

    /**
     * @param string    $name
     * @param bool|null $groupRoute
     * @return RouteInterface
     * @throws GroupRouteExpected
     * @throws RouteNotFound
     */
    public function route(string $name, bool $groupRoute = null): RouteInterface
    {
        return $this->getRoute($name, $groupRoute);
    }
}
