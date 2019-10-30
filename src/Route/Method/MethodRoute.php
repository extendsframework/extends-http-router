<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Method;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatch;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;

class MethodRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
     */
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_GET = 'GET';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';

    /**
     * Methods to match.
     *
     * @var string
     */
    private $method;

    /**
     * Default parameters to return.
     *
     * @var array|null
     */
    private $parameters;

    /**
     * Create a method route.
     *
     * @param string     $method
     * @param array|null $parameters
     */
    public function __construct(string $method, array $parameters = null)
    {
        $this->method = $method;
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        $method = $request->getMethod();
        if (strtoupper($method) === $this->getMethod()) {
            return new RouteMatch($this->getParameters(), $pathOffset);
        }

        throw new MethodNotAllowed($method, [$this->getMethod()]);
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        return $request->withMethod($this->getMethod());
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        return new static($extra['method'], $extra['parameters'] ?? []);
    }

    /**
     * Get method.
     *
     * @return string
     */
    private function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get parameters.
     *
     * @return array
     */
    private function getParameters(): array
    {
        if ($this->parameters === null) {
            $this->parameters = [];
        }

        return $this->parameters;
    }
}
