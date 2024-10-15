<?php

namespace Norotaro\Enumata;

use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\DefineStates;
use Norotaro\Enumata\Contracts\HasStateMachine;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use UnitEnum;

class EnumStateMachine implements Contracts\StateMachine
{
    protected bool $isTransitioning = false;

    public function __construct(
        protected HasStateMachine $hasStateMachine,
        protected string $field
    ) {
    }

    public function currentState(): null|(DefineStates&UnitEnum)
    {
        return $this->hasStateMachine->{$this->field};
    }

    public function canBe(DefineStates&UnitEnum $state): bool
    {
        try {
            $transitions = $this->currentState()?->transitions();
        } catch (\UnhandledMatchError) {
            $transitions = [];
        }

        if (!$transitions && in_array(Nullable::class, class_implements($state))) {
            /** @var Nullable $state */
            $nullableState = $state;

            $transitions = $nullableState->initialTransitions();
        }

        return in_array($state, $transitions ?? []);
    }

    public function transitionTo(DefineStates&UnitEnum $state, bool $force = false): void
    {
        if ($state === $this->currentState()) {
            return;
        }

        if (!$force && !$this->canBe($state)) {
            throw new TransitionNotAllowedException($this->currentState(), $state, $this, get_class($this->hasStateMachine));
        }

        $this->isTransitioning = true;

        $this->hasStateMachine->{$this->field} = $state;

        $this->hasStateMachine->fireTransitioningEvent($this->field);

        $this->hasStateMachine->persist();

        $this->hasStateMachine->fireTransitionedEvent($this->field);

        $this->isTransitioning = false;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function isTransitioning(): bool
    {
        return $this->isTransitioning;
    }
}
