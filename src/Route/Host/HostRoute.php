<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Host;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatch;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class HostRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * Host to match.
     *
     * @var string
     */
    private $host;

    /**
     * Default parameters to return.
     *
     * @var array
     */
    private $parameters;

    /**
     * Create a method route.
     *
     * @param string $host
     * @param array  $parameters
     */
    public function __construct(string $host, array $parameters = null)
    {
        $this->host = $host;
        $this->parameters = $parameters ?? [];
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        return new static($extra['host'], $extra['parameters'] ?? []);
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        if ($this->host === $request
                ->getUri()
                ->getHost()) {
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
                ->withHost($this->host)
        );
    }
}
