<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route\Scheme;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Router\Route\RouteInterface;
use ExtendsFramework\Http\Router\Route\RouteMatch;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
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
        if (strtoupper($request->getUri()->getScheme()) === $this->scheme) {
            return new RouteMatch($this->parameters, $pathOffset);
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
                ->withScheme($this->scheme)
        );
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): RouteInterface
    {
        return new static($extra['scheme'], $extra['parameters'] ?? []);
    }
}
