<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Path\Exception;

use ExtendsFramework\Router\Route\RouteException;
use InvalidArgumentException;

class PathParameterMissing extends InvalidArgumentException implements RouteException
{
    /**
     * ParameterMissing constructor.
     *
     * @param string $parameter
     */
    public function __construct(string $parameter)
    {
        parent::__construct(sprintf(
            'Failed to assemble route, path parameter "%s" is missing.',
            $parameter
        ));
    }
}
