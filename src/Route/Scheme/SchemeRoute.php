<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Scheme;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatch;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class SchemeRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * Parameters to return when route is matched.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Scheme to match.
     *
     * @var string
     */
    protected $scheme;

    /**
     * Create a new scheme route.
     *
     * @param string $scheme
     * @param array  $parameters
     */
    public function __construct(string $scheme, array $parameters = null)
    {
        $this->scheme = strtoupper(trim($scheme));
        $this->parameters = $parameters ?? [];
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        if (strtoupper($request->getUri()->getScheme()) === $this->getScheme()) {
            return new RouteMatch($this->getParameters(), $pathOffset);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        return $request->withUri(
            $request
                ->getUri()
                ->withScheme($this->getScheme())
        );
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        return new static($extra['scheme'], $extra['parameters'] ?? []);
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    protected function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get scheme.
     *
     * @return string
     */
    protected function getScheme(): string
    {
        return $this->scheme;
    }
}
