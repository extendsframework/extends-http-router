<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route;

class RouteMatch implements RouteMatchInterface
{
    /**
     * Matched parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Request URI path offset.
     *
     * @var int
     */
    protected $pathOffset;

    /**
     * Create a route match.
     *
     * @param array $parameters
     * @param int   $pathOffset
     */
    public function __construct(array $parameters, int $pathOffset)
    {
        $this->parameters = $parameters;
        $this->pathOffset = $pathOffset;
    }

    /**
     * @inheritDoc
     */
    public function getPathOffset(): int
    {
        return $this->pathOffset;
    }

    /**
     * @inheritDoc
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function merge(RouteMatchInterface $routeMatch): RouteMatchInterface
    {
        $merged = clone $this;
        $merged->parameters = array_replace_recursive($this->getParameters(), $routeMatch->getParameters());
        $merged->pathOffset = $routeMatch->getPathOffset();

        return $merged;
    }
}
