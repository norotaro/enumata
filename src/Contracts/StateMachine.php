<?php

namespace Norotaro\Enumata\Contracts;

use UnitEnum;

interface StateMachine
{
    public function __construct(HasStateMachine $hasStateMachine, string $field);

    public function currentState(): null|(DefineStates&UnitEnum);

    public function canBe(DefineStates&UnitEnum $status): bool;

    public function transitionTo(DefineStates&UnitEnum $state, bool $force = false): void;

    public function getField(): string;

    public function isTransitioning(): bool;
}
