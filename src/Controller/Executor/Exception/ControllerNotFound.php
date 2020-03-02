<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Executor\Exception;

use Exception;
use ExtendsFramework\Router\Controller\Executor\ExecutorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;

class ControllerNotFound extends Exception implements ExecutorException
{
    /**
     * When controller can not be found.
     *
     * @param string $key
     * @param ServiceLocatorException $exception
     */
    public function __construct(string $key, ServiceLocatorException $exception)
    {
        parent::__construct(
            sprintf(
                'Controller for key "%s" can not be retrieved from service locator.',
                $key
            ),
            0,
            $exception
        );
    }
}
