<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route\Query;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Router\Route\Query\Exception\InvalidQueryString;
use ExtendsFramework\Http\Router\Route\Query\Exception\QueryParameterMissing;
use ExtendsFramework\Http\Router\Route\RouteInterface;
use ExtendsFramework\Http\Router\Route\RouteMatch;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\ValidatorInterface;

class QueryRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * Validators for matching the query parameters.
     *
     * @var ValidatorInterface[]
     */
    protected $validators;

    /**
     * Default parameters to return when route is matched.
     *
     * @var array
     */
    protected $parameters;

    /**
     * @param array $validators
     * @param array $parameters
     */
    public function __construct(array $validators, array $parameters = null)
    {
        $this->validators = $validators;
        $this->parameters = $parameters ?? [];
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        $query = $request->getUri()->getQuery();

        $matched = [];
        foreach ($this->validators as $path => $validator) {
            if (array_key_exists($path, $query) === true) {
                $value = $query[$path];

                $result = $validator->validate($value, $query);
                if ($result->isValid() === false) {
                    throw new InvalidQueryString($path, $result);
                }

                $matched[$path] = $value;
            } elseif (array_key_exists($path, $this->parameters) === false) {
                throw new QueryParameterMissing($path);
            }
        }

        return new RouteMatch($this->getParameters($matched), $pathOffset);
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        $query = [];
        $uri = $request->getUri();

        $parameters = array_replace($this->parameters, $uri->getQuery(), $parameters);
        foreach ($this->validators as $parameter => $validator) {
            if (array_key_exists($parameter, $parameters) === false) {
                throw new QueryParameterMissing($parameter);
            }

            $result = $validator->validate($parameters[$parameter]);
            if ($result->isValid() === false) {
                throw new InvalidQueryString($parameter, $result);
            }

            if (($this->parameters[$parameter] ?? null) !== ($parameters[$parameter] ?? null)) {
                $query[$parameter] = $parameters[$parameter];
            }
        }

        return $request->withUri(
            $uri->withQuery(array_replace($uri->getQuery(), $query))
        );
    }

    /**
     * @inheritDoc
     */
    public static function factory(string $key, ServiceLocatorInterface $serviceLocator, array $extra = null): RouteInterface
    {
        $validators = [];
        foreach ($extra['validators'] ?? [] as $parameter => $validator) {
            if (is_string($validator) === true) {
                $validator = [
                    'name' => $validator,
                ];
            }

            $validators[$parameter] = $serviceLocator->getService($validator['name'], $validator['options'] ?? []);
        }

        return new static($validators, $extra['parameters'] ?? []);
    }

    /**
     * Get the parameters when the route is matches.
     *
     * @param array $matches
     * @return array
     */
    protected function getParameters(array $matches): array
    {
        return array_replace($this->parameters, $matches);
    }
}
