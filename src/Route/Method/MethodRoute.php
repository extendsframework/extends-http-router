<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Method;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\Method\Exception\MethodNotAllowed;
use ExtendsFramework\Router\Route\Method\Exception\UnprocessableEntity;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatch;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\ValidatorInterface;

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
     * @var array
     */
    private $parameters;

    /**
     * Validators for matching the request body.
     *
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * Create a method route.
     *
     * @param string $method
     * @param array|null $parameters
     * @param array|null $validators
     */
    public function __construct(string $method, array $parameters = null, array $validators = null)
    {
        $this->method = $method;
        $this->parameters = $parameters ?? [];
        $this->validators = $validators ?? [];
    }

    /**
     * @inheritDoc
     * @throws ServiceLocatorException
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): object
    {
        $validators = [];
        foreach ($extra['validators'] ?? [] as $parameter => $validator) {
            if (is_string($validator)) {
                $validator = [
                    'name' => $validator,
                ];
            }

            $validators[$parameter] = $serviceLocator->getService($validator['name'], $validator['options'] ?? []);
        }

        return new static($extra['method'], $extra['parameters'] ?? null, $validators);
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        $method = $request->getMethod();
        if (strtoupper($method) === $this->method) {
            foreach ($this->validators as $validator) {
                $result = $validator->validate($request->getBody());
                if (!$result->isValid()) {
                    throw new UnprocessableEntity($result);
                }
            }

            return new RouteMatch($this->parameters, $pathOffset);
        }

        throw new MethodNotAllowed($method, [$this->method]);
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        return $request->withMethod($this->method);
    }
}
