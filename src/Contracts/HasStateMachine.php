<?php

namespace Norotaro\Enumata\Contracts;

interface HasStateMachine
{
    public function fireTransitioningEvent(string $field): void;

    public function fireTransitionedEvent(string $field): void;

    public function persist(): void;
}
