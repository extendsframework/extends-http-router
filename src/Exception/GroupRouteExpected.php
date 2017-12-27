<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Exception;

use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\RouterException;
use LogicException;

class GroupRouteExpected extends LogicException implements RouterException
{
    /**
     * GroupRouteExpected constructor.
     *
     * @param RouteInterface $route
     */
    public function __construct(RouteInterface $route)
    {
        parent::__construct(sprintf(
            'A group route was expected, but an instance of "%s" was returned.',
            get_class($route)
        ));
    }
}
