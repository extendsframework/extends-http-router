<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Executor\Exception;

use Exception;
use ExtendsFramework\Router\Controller\Executor\ExecutorException;
use ExtendsFramework\Router\Controller\ControllerException;

class ControllerExecutionFailed extends Exception implements ExecutorException
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
