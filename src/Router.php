<?php
declare(strict_types=1);

namespace ExtendsFramework\Router;

use ExtendsFramework\Http\Request\Request;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Exception\InvalidRoutePath;
use ExtendsFramework\Router\Exception\NotFound;
use ExtendsFramework\Router\Route\RouteMatchInterface;

class Router implements RouterInterface
{
    use Routes;

    /**
     * Pattern for route path.
     *
     * @var string
     */
    protected $pattern = '/^([a-z0-9\-\_]+)((?:\/([a-z0-9\-\_]+))*)$/i';

    /**
     * @inheritDoc
     */
    public function route(RequestInterface $request): RouteMatchInterface
    {
        $match = $this->matchRoutes($request, 0);
        if ($match instanceof RouteMatchInterface) {
            if ($match->getPathOffset() === strlen($request->getUri()->getPath())) {
                $parameters = $match->getParameters();
                $query = $request->getUri()->getQuery();

                if (empty(array_diff_key($query, $parameters)) === true) {
                    return $match;
                }
            }
        }

        throw new NotFound($request);
    }

    /**
     * @inheritDoc
     */
    public function assemble(string $path, array $parameters = null): RequestInterface
    {
        if (preg_match($this->pattern, $path) === 0) {
            throw new InvalidRoutePath($path);
        }

        $routes = explode('/', $path);
        $route = $this->getRoute(array_shift($routes), empty($routes) === false);

        return $route->assemble(new Request(), $routes, $parameters ?? []);
    }
}
