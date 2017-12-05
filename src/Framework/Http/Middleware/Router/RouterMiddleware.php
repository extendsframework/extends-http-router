<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Router;

use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Middleware\MiddlewareInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\Response;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Exception\NotFound;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\Query\Exception\InvalidQueryString;
use ExtendsFramework\Router\Route\Query\Exception\QueryParameterMissing;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\Router\RouterInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * Router to route request.
     *
     * @var RouterInterface
     */
    protected $router;

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
     */
    public function process(RequestInterface $request, MiddlewareChainInterface $chain): ResponseInterface
    {
        try {
            $match = $this->router->route($request);
        } catch (MethodNotAllowed $exception) {
            return $this->getMethodNotAllowedResponse($exception);
        } catch (NotFound $exception) {
            return $this->getNotFoundResponse($exception);
        } catch (InvalidQueryString $exception) {
            return $this->getInvalidQueryStringResponse($exception);
        } catch (QueryParameterMissing $exception) {
            return $this->getQueryParameterMissingResponse($exception);
        }

        if ($match instanceof RouteMatchInterface) {
            $request = $request->andAttribute('routeMatch', $match);
        }

        return $chain->proceed($request);
    }

    /**
     * Get response for MethodNotAllowed exception.
     *
     * @param MethodNotAllowed $exception
     * @return ResponseInterface
     */
    protected function getMethodNotAllowedResponse(MethodNotAllowed $exception): ResponseInterface
    {
        return (new Response())
            ->withStatusCode(405)
            ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()))
            ->withBody([
                'type' => '',
                'title' => 'Method not allowed.',
                'method' => $exception->getMethod(),
                'allowed_methods' => $exception->getAllowedMethods(),
            ]);
    }

    /**
     * Get response for NotFound exception.
     *
     * @param NotFound $exception
     * @return ResponseInterface
     */
    protected function getNotFoundResponse(NotFound $exception): ResponseInterface
    {
        return (new Response())
            ->withStatusCode(404)
            ->withBody([
                'type' => '',
                'title' => 'Not found.',
                'path' => $exception
                    ->getRequest()
                    ->getUri()
                    ->toRelative(),
            ]);
    }

    /**
     * Get response for InvalidQueryString exception.
     *
     * @param InvalidQueryString $exception
     * @return ResponseInterface
     */
    protected function getInvalidQueryStringResponse(InvalidQueryString $exception): ResponseInterface
    {
        return (new Response())
            ->withStatusCode(400)
            ->withBody([
                'type' => '',
                'title' => 'Invalid query string.',
                'parameter' => $exception->getParameter(),
                'reason' => $exception->getResult(),
            ]);
    }

    /**
     * Get response for QueryParameterMissing exception.
     *
     * @param QueryParameterMissing $exception
     * @return ResponseInterface
     */
    protected function getQueryParameterMissingResponse(QueryParameterMissing $exception): ResponseInterface
    {
        return (new Response())
            ->withStatusCode(400)
            ->withBody([
                'type' => '',
                'title' => 'Query parameter missing.',
                'parameter' => $exception->getParameter(),
            ]);
    }
}
