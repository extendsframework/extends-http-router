<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route;

use ExtendsFramework\Http\Request\RequestInterface;

interface RouteInterface
{
    /**
     * Match route against $request.
     *
     * Parameter $pathOffset is used to pass the request uri path offset to other routes.
     *
     * @param RequestInterface $request
     * @param int              $pathOffset
     * @return RouteMatchInterface
     * @throws RouteException
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface;

    /**
     * Assemble $path into $request.
     *
     * An exception will be thrown when routes for $path can not be found.
     *
     * @param RequestInterface $uri
     * @param array            $path
     * @param array            $parameters
     * @return RequestInterface
     */
    public function assemble(RequestInterface $uri, array $path, array $parameters): RequestInterface;
}
