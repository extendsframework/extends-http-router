<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Method\Exception;

use ExtendsFramework\Router\Route\Method\MethodRouteException;
use ExtendsFramework\Validator\Result\ResultInterface;
use RuntimeException;

class InvalidRequestBody extends RuntimeException implements MethodRouteException
{
    /**
     * Validation result.
     *
     * @var ResultInterface
     */
    private $result;

    /**
     * RequestBodyInvalid constructor.
     *
     * @param ResultInterface $result
     */
    public function __construct(ResultInterface $result)
    {
        parent::__construct('Request body is invalid.');

        $this->result = $result;
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
