<?php

namespace Weebel\ControllerCaller;

use Symfony\Component\HttpFoundation\Response;
use Weebel\Container\ContainerException;
use Weebel\Contracts\Container;
use Weebel\Contracts\EventDispatcher;
use Weebel\ControllerCaller\Events\ControllerProcessed;
use Weebel\Router\RouteResolved;

class CallController
{
    public function __construct(
        protected Container       $container,
        protected EventDispatcher $eventDispatcher,
        protected Response        $response
    )
    {
    }

    /**
     * @throws ContainerException
     * @throws ControllerException
     * @throws \JsonException
     */
    public function __invoke(RouteResolved $event): void
    {
        try {
            $controller = $event->_controller;
            $method = $event->_method;
        } catch (\Throwable $exception) {
            throw new ControllerException(sprintf(
                "Controller and method is not set for the path %s. %s",
                $event->route->getPath(),
                $exception->getMessage()
            ));
        }

        if (!$controller || !$method) {
            throw new ControllerException(sprintf(
                "Controller and method is not set for the path %s.",
                $event->route->getPath()
            ));
        }

        $this->eventDispatcher->dispatchByTag('controller_processing');

        $result = $this->container->call($controller, $method);


        $controllerProcessed = new ControllerProcessed($result);

        $this->eventDispatcher->dispatch($controllerProcessed);


        if ($result instanceof Response) {
            throw new ContainerException("Controller $controller should not return a Response class; instead it should manipulate the response which is registered in the container");
        }

        if (is_array($result)) {
            $this->response->setContent(json_encode($result, JSON_THROW_ON_ERROR))->setStatusCode(200);
            $this->response->headers->set('Content-Type', 'application/json');
        } elseif (is_string($result)) {
            $this->response->setContent($result)->setStatusCode(200);
            $this->response->headers->set('Content-Type', 'text/html');
        } else {
            throw new ControllerException(sprintf("Controller %s::%s is not returning a valid output to be rendered", $controller, $method));
        }

        $this->eventDispatcher->dispatchByTag('response_finalized');
    }
}
