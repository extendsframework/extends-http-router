<?php
declare(strict_types=1);

namespace ExtendsFramework\Router\Controller;

use ExtendsFramework\Http\Response\Response;
use ExtendsFramework\Http\Response\ResponseInterface;

class ControllerStub extends AbstractController
{
    /**
     * @param int       $someId
     * @param bool|null $allowsNull
     * @param string    $defaultValue
     * @return ResponseInterface
     */
    public function fooFancyActionAction(
        int $someId,
        ?bool $allowsNull,
        string $defaultValue = 'string'
    ): ResponseInterface {
        return (new Response())->withBody([
            'request' => $this->getRequest(),
            'routeMatch' => $this->getRouteMatch(),
            'someId' => $someId,
            'allowsNull' => $allowsNull,
            'defaultValue' => $defaultValue,
        ]);
    }
}
