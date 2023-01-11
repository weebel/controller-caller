<?php

namespace Weebel\ControllerCaller\Events;

class ControllerProcessed
{
    public mixed $result;

    /**
     * @param mixed $result
     */
    public function __construct(mixed $result)
    {
        $this->result = $result;
    }
}
