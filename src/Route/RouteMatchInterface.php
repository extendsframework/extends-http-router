<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route;

interface RouteMatchInterface
{
    /**
     * Get merged parameters from route.
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Get request URI path offset.
     *
     * @return int
     */
    public function getPathOffset(): int;

    /**
     * Merge with other $routeMatch.
     *
     * Used for nested routes.
     *
     * @param RouteMatchInterface $routeMatch
     * @return RouteMatchInterface
     */
    public function merge(RouteMatchInterface $routeMatch): RouteMatchInterface;
}
