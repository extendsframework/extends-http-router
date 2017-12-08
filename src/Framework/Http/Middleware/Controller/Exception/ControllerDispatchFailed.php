<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\Exception;

use Exception;
use ExtendsFramework\Http\Router\Controller\ControllerException;
use ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\ControllerMiddlewareException;

class ControllerDispatchFailed extends Exception implements ControllerMiddlewareException
{
    /**
     * When controller dispatch throws $exception.
     *
     * @param ControllerException $exception
     */
    public function __construct(ControllerException $exception)
    {
        parent::__construct(
            'Failed to dispatch request to controller. See previous exception for more details.',
            0,
            $exception
        );
    }
}
