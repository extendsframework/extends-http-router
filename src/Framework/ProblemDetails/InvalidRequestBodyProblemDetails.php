<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ProblemDetails;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\ProblemDetails\ProblemDetails;
use ExtendsFramework\Router\Route\Method\Exception\InvalidRequestBody;

class InvalidRequestBodyProblemDetails extends ProblemDetails
{
    /**
     * InvalidRequestBodyProblemDetails constructor.
     *
     * @param RequestInterface $request
     * @param InvalidRequestBody $exception
     */
    public function __construct(RequestInterface $request, InvalidRequestBody $exception)
    {
        parent::__construct(
            '/problems/router/invalid-request-body',
            'Invalid request body',
            'Request body is invalid.',
            400,
            $request->getUri()->toRelative(),
            [
                'errors' => $exception->getResult(),
            ]
        );
    }
}
