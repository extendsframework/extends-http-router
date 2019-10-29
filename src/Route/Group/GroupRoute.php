<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Group;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\Group\Exception\AssembleAbstractGroupRoute;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\Router\Routes;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class GroupRoute implements RouteInterface, StaticFactoryInterface
{
    use Routes;

    /**
     * If this can be matched.
     *
     * @var bool
     */
    private $abstract;

    /**
     * Route to match.
     *
     * @var RouteInterface
     */
    private $innerRoute;

    /**
     * Create a group route.
     *
     * @param RouteInterface $route
     * @param bool           $abstract
     */
    public function __construct(RouteInterface $route, bool $abstract = null)
    {
        $this->innerRoute = $route;
        $this->abstract = $abstract ?? true;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        $outer = $this
            ->getInnerRoute()
            ->match($request, $pathOffset);
        if (! $outer instanceof RouteMatchInterface) {
            return null;
        }

        $inner = $this->matchRoutes($request, $outer->getPathOffset());
        if ($inner instanceof RouteMatchInterface) {
            return $outer->merge($inner);
        }

        if (!$this->isAbstract()) {
            return $outer;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        $request = $this
            ->getInnerRoute()
            ->assemble($request, $path, $parameters);
        if (empty($path)) {
            if ($this->isAbstract()) {
                throw new AssembleAbstractGroupRoute();
            }

            return $request;
        }

        return $this
            ->getRoute(array_shift($path), !empty($path))
            ->assemble($request, $path, $parameters);
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        return new static($extra['route'], $extra['abstract'] ?? null);
    }

    /**
     * Get abstract.
     *
     * @return bool
     */
    private function isAbstract(): bool
    {
        return $this->abstract;
    }

    /**
     * Get inner route.
     *
     * @return RouteInterface
     */
    private function getInnerRoute(): RouteInterface
    {
        return $this->innerRoute;
    }
}
