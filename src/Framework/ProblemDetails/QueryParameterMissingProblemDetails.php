<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\ProblemDetails;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\ProblemDetails\ProblemDetails;
use ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing;

class QueryParameterMissingProblemDetails extends ProblemDetails
{
    /**
     * QueryParameterMissingProblemDetails constructor.
     *
     * @param RequestInterface $request
     * @param QueryParameterMissing $exception
     */
    public function __construct(RequestInterface $request, QueryParameterMissing $exception)
    {
        parent::__construct(
            '/problems/router/query-parameter-missing',
            'Query parameter missing',
            sprintf(
                'Query parameter "%s" is missing.',
                $exception->getParameter()
            ),
            409,
            $request->getUri()->toRelative(),
            [
                'parameter' => $exception->getParameter(),
            ]
        );
    }
}
