<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Controller\Exception;

use Exception;
use ExtendsFramework\Http\Router\Controller\ControllerException;

class ActionNotFound extends Exception implements ControllerException
{
    /**
     * When action is missing in request.
     */
    public function __construct()
    {
        parent::__construct('No controller action was found in request.');
    }
}
