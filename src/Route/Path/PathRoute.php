<?php
declare(strict_types=1);

namespace ExtendsFramework\Http\Router\Route\Path;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Http\Router\Route\Path\Exception\PathParameterMissing;
use ExtendsFramework\Http\Router\Route\RouteInterface;
use ExtendsFramework\Http\Router\Route\RouteMatch;
use ExtendsFramework\Http\Router\Route\RouteMatchInterface;
use ExtendsFramework\ServiceLocator\Resolver\StaticFactory\StaticFactoryInterface;
use ExtendsFramework\ServiceLocator\ServiceLocatorInterface;
use ExtendsFramework\Validator\ValidatorInterface;

class PathRoute implements RouteInterface, StaticFactoryInterface
{
    /**
     * Validators for matching the URI variables.
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
     * Path to match.
     *
     * @var string
     */
    protected $path;

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
        $this->validators = $validators ?? [];
        $this->parameters = $parameters ?? [];
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request, int $pathOffset): ?RouteMatchInterface
    {
        if ((bool)preg_match($this->getPattern(), $request->getUri()->getPath(), $matches, PREG_OFFSET_CAPTURE, $pathOffset) === true) {
            foreach ($this->validators as $parameter => $validator) {
                $result = $validator->validate($matches[$parameter][0]);
                if ($result->isValid() === false) {
                    return null;
                }
            }

            return new RouteMatch($this->getParameters($matches), end($matches)[1]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function assemble(RequestInterface $request, array $path, array $parameters): RequestInterface
    {
        $parameters = array_replace_recursive($this->parameters, $parameters);

        $uri = $request->getUri();
        $current = $uri->getPath();
        $addition = preg_replace_callback('~:([a-z][a-z0-9\_]+)~i', function ($match) use ($parameters) {
            $parameter = $match[1];
            if (array_key_exists($parameter, $parameters) === false) {
                throw new PathParameterMissing($parameter);
            }

            return $parameters[$parameter];
        }, $this->path);

        return $request->withUri(
            $uri->withPath(rtrim($current, '/') . '/' . ltrim($addition, '/'))
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

        return new static($extra['path'], $validators, $extra['parameters'] ?? []);
    }

    /**
     * Get the parameters when the route is matches.
     *
     * The $matches will be filtered for integer keys and merged into the default parameters.
     *
     * @param array $matches
     * @return array
     */
    protected function getParameters(array $matches): array
    {
        $parameters = [];
        foreach ($matches as $key => $match) {
            if (is_string($key)) {
                $parameters[$key] = $match[0];
            }
        }

        return array_replace_recursive($this->parameters, $parameters);
    }

    /**
     * Get pattern to match request path.
     *
     * @return string
     */
    protected function getPattern(): string
    {
        $path = preg_replace_callback('~:([a-z][a-z0-9\_]+)~i', function ($match) {
            return sprintf('(?<%s>%s)', $match[1], '[^\/]*');
        }, $this->path);

        return sprintf('~\G(%s)(/|\z)~', $path);
    }
}
