<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ProblemDetails;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\ProblemDetails\ProblemDetails;
use ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity;

class UnprocessableEntityProblemDetails extends ProblemDetails
{
    /**
     * UnprocessableEntityProblemDetails constructor.
     *
     * @param RequestInterface $request
     * @param UnprocessableEntity $exception
     */
    public function __construct(RequestInterface $request, UnprocessableEntity $exception)
    {
        parent::__construct(
            '/problems/router/unprocessable-entity',
            'Unprocessable Entity',
            'Request body is invalid.',
            422,
            $request->getUri()->toRelative(),
            [
                'result' => $exception->getResult(),
            ]
        );
    }
}
