<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller\Exception;

use ExtendsFramework\Router\Controller\ControllerException;
use InvalidArgumentException;

class ParameterNotFound extends InvalidArgumentException implements ControllerException
{
    /**
     * ParameterNotFound constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf(
            'Parameter with name "%s" can not be found in route match parameters and has no default value or allows null.',
            $name
        ));
    }
}
