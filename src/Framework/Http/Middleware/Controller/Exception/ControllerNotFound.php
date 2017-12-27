<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Controller\Exception;

use Exception;
use ExtendsFramework\Router\Framework\Http\Middleware\Controller\ControllerMiddlewareException;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;

class ControllerNotFound extends Exception implements ControllerMiddlewareException
{
    /**
     * When controller can not be found.
     *
     * @param string                  $key
     * @param ServiceLocatorException $exception
     */
    public function __construct(string $key, ServiceLocatorException $exception)
    {
        parent::__construct(sprintf(
            'Controller for key "%s" can not be retrieved from service locator.',
            $key
        ), 0, $exception);
    }
}
