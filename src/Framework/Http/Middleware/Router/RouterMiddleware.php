<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Router;

use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Middleware\MiddlewareInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\Response;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Exception\NotFound;
use ExtendsFramework\Router\Framework\ProblemDetails\InvalidQueryStringProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\MethodNotAllowedProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\NotFoundProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\QueryParameterMissingProblemDetails;
use ExtendsFramework\Router\Framework\ProblemDetails\UnprocessableEntityProblemDetails;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity;
use ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString;
use ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\Router\RouterException;
use ExtendsFramework\Router\RouterInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * Router to route request.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Create a new router middleware.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     * @throws RouterException
     */
    public function process(RequestInterface $request, MiddlewareChainInterface $chain): ResponseInterface
    {
        try {
            $match = $this->router->route($request);
        } catch (MethodNotAllowed $exception) {
            return (new Response())
                ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()))
                ->withBody(
                    new MethodNotAllowedProblemDetails($request, $exception)
                );
        } catch (NotFound $exception) {
            return (new Response())->withBody(
                new NotFoundProblemDetails($request)
            );
        } catch (InvalidQueryString $exception) {
            return (new Response())->withBody(
                new InvalidQueryStringProblemDetails($request, $exception)
            );
        } catch (QueryParameterMissing $exception) {
            return (new Response())->withBody(
                new QueryParameterMissingProblemDetails($request, $exception)
            );
        } catch (UnprocessableEntity $exception) {
            return (new Response())->withBody(
                new UnprocessableEntityProblemDetails($request, $exception)
            );
        }

        if ($match instanceof RouteMatchInterface) {
            $request = $request->andAttribute('routeMatch', $match);
        }

        return $chain->proceed($request);
    }
}
