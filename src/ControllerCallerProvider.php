<?php

namespace Weebel\ControllerCaller;

use Weebel\Contracts\Bootable;
use Weebel\Contracts\EventDispatcher;
use Weebel\Router\RouteResolved;

class ControllerCallerProvider implements Bootable
{
    public function __construct(protected EventDispatcher $eventDispatcher)
    {
    }

    public function boot(): void
    {
        $this->eventDispatcher->addListener(RouteResolved::class, CallController::class);
    }
}
