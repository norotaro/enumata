<?php

namespace Norotaro\Enumaton;

use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Norotaro\Enumaton\Contracts\Nullable;
use Norotaro\Enumaton\Contracts\StateDefinitions;
use Norotaro\Enumaton\Exceptions\TransitionNotAllowedException;
use UnitEnum;

class StateMachine implements Contracts\StateMachine
{
    public function __construct(
        protected Model $model,
        protected string $field
    ) {
    }

    public function currentState()
    {
        return $this->model->{$this->field};
    }

    /**
     * Check if a transition is available
     */
    public function canBe(StateDefinitions&UnitEnum $state): bool
    {
        $validStates = $this->currentState()?->allowedTransitions();

        $validInitialStates = [];
        if (in_array(Nullable::class, class_implements($state))) {
            /** @var Nullable */
            $nullableState = $state;

            $validInitialStates = $nullableState->validInitialStates();
        }

        return in_array($state, $validStates ?? $validInitialStates);
    }

    /**
     * Transition to a new state
     *
     * @throws TransitionNotAllowedException
     * @throws InvalidArgumentException
     * @throws InvalidCastException
     */
    public function transitionTo(StateDefinitions&UnitEnum $state): void
    {
        if ($state === $this->currentState()) {
            return;
        }

        if (!$this->canBe($state)) {
            throw new TransitionNotAllowedException(
                $this->currentState(),
                $state,
                $this->model
            );
        }

        $this->model->{$this->field} = $state;

        $this->model->fireTransitioningEvent($this->field);

        $this->model->save();

        $this->model->fireTransitionedEvent($this->field);
    }
}
