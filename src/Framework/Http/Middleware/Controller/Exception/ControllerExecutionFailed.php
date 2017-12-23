<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\Exception;

use Exception;
use ExtendsFramework\Http\Router\Controller\ControllerException;
use ExtendsFramework\Http\Router\Framework\Http\Middleware\Controller\ControllerMiddlewareException;

class ControllerExecutionFailed extends Exception implements ControllerMiddlewareException
{
    /**
     * When controller execution throws $exception.
     *
     * @param ControllerException $exception
     */
    public function __construct(ControllerException $exception)
    {
        parent::__construct(
            'Failed to execute request to controller. See previous exception for more details.',
            0,
            $exception
        );
    }
}
