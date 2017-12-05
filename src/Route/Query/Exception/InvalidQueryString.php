<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Query\Exception;

use ExtendsFramework\Router\Route\Query\QueryRouteException;
use ExtendsFramework\Validator\Result\ResultInterface;
use InvalidArgumentException;

class InvalidQueryString extends InvalidArgumentException implements QueryRouteException
{
    /**
     * Query string parameter.
     *
     * @var string
     */
    protected $parameter;

    /**
     * Validation result.
     *
     * @var ResultInterface
     */
    protected $result;

    /**
     * InvalidParameterValue constructor.
     *
     * @param string          $parameter
     * @param ResultInterface $result
     */
    public function __construct(string $parameter, ResultInterface $result)
    {
        parent::__construct(sprintf(
            'Query string parameter "%s" failed to validate.',
            $parameter
        ));

        $this->parameter = $parameter;
        $this->result = $result;
    }

    /**
     * Get query string parameter.
     *
     * @return string
     */
    public function getParameter(): string
    {
        return $this->parameter;
    }

    /**
     * Get validation result.
     *
     * @return ResultInterface
     */
    public function getResult(): ResultInterface
    {
        return $this->result;
    }
}
