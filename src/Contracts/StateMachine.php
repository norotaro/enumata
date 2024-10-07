<?php

namespace Norotaro\Enumata\Contracts;

use UnitEnum;

interface StateMachine
{
    /**
     * Return current state
     *
     * @return null|DefineStates&UnitEnum
     */
    public function currentState();

    public function canBe(DefineStates&UnitEnum $status): bool;

    public function transitionTo(DefineStates&UnitEnum $state, bool $force = false): void;

    public function getField(): string;

    public function isTransitioning(): bool;
}
