<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Framework\Http\Middleware\Controller;

use ExtendsFramework\Http\Middleware\Chain\MiddlewareChainInterface;
use ExtendsFramework\Http\Middleware\MiddlewareInterface;
use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Response\ResponseInterface;
use ExtendsFramework\Router\Controller\Executor\ExecutorException;
use ExtendsFramework\Router\Controller\Executor\ExecutorInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    /**
     * Controller executor.
     *
     * @var ExecutorInterface
     */
    private $executor;

    /**
     * ControllerMiddleware constructor.
     *
     * @param ExecutorInterface $executor
     */
    public function __construct(ExecutorInterface $executor)
    {
        $this->executor = $executor;
    }

    /**
     * @inheritDoc
     * @throws ExecutorException
     */
    public function process(RequestInterface $request, MiddlewareChainInterface $chain): ResponseInterface
    {
        $match = $request->getAttribute('routeMatch');
        if ($match instanceof RouteMatchInterface) {
            $parameters = $match->getParameters();
            if (isset($parameters['controller'])) {
                return $this->executor->execute($request, $match);
            }
        }

        return $chain->proceed($request);
    }
}
