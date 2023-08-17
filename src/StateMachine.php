<?php

namespace Norotaro\Enumata;

use Illuminate\Database\Eloquent\InvalidCastException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\DefineStates;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use UnhandledMatchError;
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
    public function canBe(DefineStates&UnitEnum $state): bool
    {
        try {
            $transitions = $this->currentState()?->transitions();
        } catch (UnhandledMatchError $th) {
            $transitions = null;
        }

        if (!$transitions && in_array(Nullable::class, class_implements($state))) {
            /** @var Nullable */
            $nullableState = $state;

            $transitions = $nullableState->initialTransitions();
        }

        return in_array($state, $transitions ?? []);
    }

    /**
     * Transition to a new state
     *
     * @throws TransitionNotAllowedException
     * @throws InvalidArgumentException
     * @throws InvalidCastException
     */
    public function transitionTo(DefineStates&UnitEnum $to, bool $force = false): void
    {
        if ($to === $this->currentState()) {
            return;
        }

        /** TODO: unify the validation logic of transitions */
        if (!$force && !$this->canBe($to)) {
            throw new TransitionNotAllowedException(
                $this->currentState(),
                $to,
                $this->model
            );
        }

        $this->model->{$this->field} = $to;

        $this->model->fireTransitioningEvent($this->field);

        $this->model->save();

        $this->model->fireTransitionedEvent($this->field, $to);
    }

    public function getField(): string
    {
        return $this->field;
    }
}
