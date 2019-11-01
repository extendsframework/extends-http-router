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
                ->withStatusCode(405)
                ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()))
                ->withBody([
                    'type' => '',
                    'title' => 'Method not allowed.',
                    'method' => $exception->getMethod(),
                    'allowed_methods' => $exception->getAllowedMethods(),
                ]);
        } catch (NotFound $exception) {
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
        } catch (InvalidQueryString $exception) {
            return (new Response())
                ->withStatusCode(400)
                ->withBody([
                    'type' => '',
                    'title' => 'Invalid query string.',
                    'parameter' => $exception->getParameter(),
                    'reason' => $exception->getResult(),
                ]);
        } catch (QueryParameterMissing $exception) {
            return (new Response())
                ->withStatusCode(400)
                ->withBody([
                    'type' => '',
                    'title' => 'Query parameter missing.',
                    'parameter' => $exception->getParameter(),
                ]);
        }

        if ($match instanceof RouteMatchInterface) {
            $request = $request->andAttribute('routeMatch', $match);
        }

        return $chain->proceed($request);
    }
}
