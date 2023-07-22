<?php

namespace Norotaro\Enumaton\Contracts;

use Illuminate\Database\Eloquent\Model;
use UnitEnum;

interface StateMachine
{
    public function __construct(Model $model, string $field);

    /**
     * Return current state
     *
     * @return null|StateDefinitions&UnitEnum
     */
    public function currentState();

    public function canBe(StateDefinitions&UnitEnum $status): bool;

    public function transitionTo(StateDefinitions&UnitEnum $state): void;
}
