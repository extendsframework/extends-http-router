<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Query\Exception;

use ExtendsFramework\Router\Route\RouteException;
use LogicException;

class QueryParameterMissing extends LogicException implements RouteException
{
    /**
     * Query parameter.
     *
     * @var string
     */
    protected $parameter;

    /**
     * RequiredParameterMissing constructor.
     *
     * @param string $parameter
     */
    public function __construct(string $parameter)
    {
        parent::__construct(sprintf(
            'Query string parameter "%s" value is required.',
            $parameter
        ));

        $this->parameter = $parameter;
    }

    /**
     * Get query parameter.
     *
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }
}
