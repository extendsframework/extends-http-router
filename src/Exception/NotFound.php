<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Exception;

use ExtendsFramework\Http\Request\RequestInterface;
use ExtendsFramework\Router\RouterException;
use LogicException;

class NotFound extends LogicException implements RouterException
{
    /**
     * Request.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * NotFound constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        parent::__construct('Request could not be matched by a route.');

        $this->request = $request;
    }

    /**
     * Get request.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
