<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Route\Path;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\Route\Path\Exception\PathParameterMissing;
use ExtendsFramework\Router\Route\RouteInterface;
use ExtendsFramework\Router\Route\RouteMatch;
use ExtendsFramework\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorException;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\ValidatorInterface;

class PathRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * Validators for matching the URI variables.
     *
     * @var ValidatorInterface[]|null
     */
    private $validators;

    /**
     * Default parameters to return when route is matched.
     *
     * @var array|null
     */
    private $parameters;

    /**
     * Path to match.
     *
     * @var string
     */
    private $path;

    /**
     * Create new path route.
     *
     * Value of $path must be a part of the, or the whole, request URI path to match. Variables can be used and must
     * start with a semicolon followed by a name. The name must start with a letter and can only consist of
     * alphanumeric characters. When this condition is not matched, the variable will be skipped.
     *
     * The variable name will be checked for the validator given in the $validators array. When the variable name is
     * not found as array key, the default validator \w+ will be used.
     *
     * For example: /foo/:bar/:baz/qux
     *
     * @param string $path
     * @param array  $validators
     * @param array  $parameters
     */
    public function __construct(string $path, array $validators = null, array $parameters = null)
    {
        $this->path = $path;
        $this->validators = $validators;
        $this->parameters = $parameters;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        if (preg_match(
            $this->getPattern(),
            $request->getUri()->getPath(),
            $matches,
            PREG_OFFSET_CAPTURE,
            $pathOffset
        )) {
            foreach ($this->getValidators() as $parameter => $validator) {
                $result = $validator->validate($matches[$parameter][0]);
                if (!$result->isValid()) {
                    return null;
                }
            }

            return new RouteMatch($this->getMatchedParameters($matches), end($matches)[1]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        $parameters = array_replace_recursive($this->getParameters(), $parameters);

        $uri = $request->getUri();
        $current = $uri->getPath();
        $addition = preg_replace_callback('~:([a-z][a-z0-9_]+)~i', static function ($match) use ($parameters) {
            $parameter = $match[1];
            if (!array_key_exists($parameter, $parameters)) {
                throw new PathParameterMissing($parameter);
            }

            return $parameters[$parameter];
        }, $this->getPath());

        return $request->withUri(
            $uri->withPath(rtrim($current, '/') . '/' . ltrim($addition, '/'))
        );
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

        return new static($extra['path'], $validators, $extra['parameters'] ?? []);
    }

    /**
     * Get the parameters when the route is matched.
     *
     * The $matches will be filtered for integer keys and merged into the default parameters.
     *
     * @param array $matches
     * @return array
     */
    private function getMatchedParameters(array $matches): array
    {
        $parameters = [];
        foreach ($matches as $key => $match) {
            if (is_string($key)) {
                $parameters[$key] = $match[0];
            }
        }

        return array_replace_recursive($this->getParameters(), $parameters);
    }

    /**
     * Get pattern to match request path.
     *
     * @return string
     */
    private function getPattern(): string
    {
        $path = preg_replace_callback('~:([a-z][a-z0-9_]+)~i', static function ($match) {
            return sprintf('(?<%s>%s)', $match[1], '[^\/]*');
        }, $this->getPath());

        return sprintf('~\G(%s)(/|\z)~', $path);
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

    /**
     * Get validators.
     *
     * @return ValidatorInterface[]
     */
    private function getValidators(): array
    {
        if ($this->validators === null) {
            $this->validators = [];
        }

        return $this->validators;
    }

    /**
     * Get path.
     *
     * @return string
     */
    private function getPath(): string
    {
        return $this->path;
    }
}
