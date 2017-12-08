<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route\Group\Exception;

use ExtendsFramework\Http\Router\Route\RouteException;
use LogicException;

class AssembleAbstractGroupRoute extends LogicException implements RouteException
{
    /**
     * AssembleAbstractGroupRoute constructor.
     */
    public function __construct()
    {
        parent::__construct('Can not assemble a abstract route.');
    }
}
