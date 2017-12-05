<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Exception;

use ExtendsFramework\Router\RouterException;
use InvalidArgumentException;

class RouteNotFound extends InvalidArgumentException implements RouterException
{
    /**
     * RouteNotFound constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf(
            'Route for name "%s" can not be found.',
            $name
        ));
    }
}
