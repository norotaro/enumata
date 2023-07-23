<?php

namespace Norotaro\Enumata\Contracts;

use Illuminate\Database\Eloquent\Model;
use UnitEnum;

interface StateMachine
{
    public function __construct(Model $model, string $field);

    /**
     * Return current state
     *
     * @return null|DefineStates&UnitEnum
     */
    public function currentState();

    public function canBe(DefineStates&UnitEnum $status): bool;

    public function transitionTo(DefineStates&UnitEnum $state): void;
}
