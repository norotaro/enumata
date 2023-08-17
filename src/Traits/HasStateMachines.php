<?php

namespace Norotaro\Enumata\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Str;
use Javoscript\MacroableModels\Facades\MacroableModels;
use Norotaro\Enumata\Contracts\DefineStates;
use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Events\TransitionedState;
use Norotaro\Enumata\Events\TransitioningState;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use Norotaro\Enumata\StateMachine;
use ReflectionEnum;
use UnhandledMatchError;

trait HasStateMachines
{
    public bool $defaultTransitionMethods = true;

    protected array $stateMachines = [];

    public static function bootHasStateMachines()
    {
        $model = new static();
        foreach ($model->getCasts() as $field => $castTo) {
            if (self::itDefineStates($castTo)) {

                // state machine getter definition
                $getStateMachine = function () use ($field) {
                    $this->initStateMachineFor($field);

                    return $this->stateMachines[$field];
                };

                MacroableModels::addMacro(static::class, $field, $getStateMachine);

                $camelField = Str::of($field)->camel();
                if ($field !== $camelField) {
                    MacroableModels::addMacro(static::class, $camelField, $getStateMachine);
                }

                // create transition methods
                if ($model->defaultTransitionMethods) {
                    $states = $castTo::cases();

                    foreach ($states as $state) {
                        try {
                            $transitions = $state->transitions();
                        } catch (UnhandledMatchError $th) {
                            $transitions = [];
                        }

                        foreach ($transitions as $transition => $nextState) {
                            MacroableModels::addMacro(static::class, $transition, function (bool $force = false) use ($field, $nextState) {
                                $this->{$field}()->transitionTo($nextState, force: $force);
                            });
                        }

                        if (in_array(Nullable::class, class_implements($state))) {
                            $initialTransitions = $state->initialTransitions();
                            foreach ($initialTransitions as $transition => $nextState) {
                                MacroableModels::addMacro(static::class, $transition, function (bool $force = false) use ($field, $nextState) {
                                    $this->{$field}()->transitionTo($nextState, force: $force);
                                });
                            }
                        }
                    }
                }
            }
        }

        MacroableModels::addMacro(static::class, 'fireTransitioningEvent', function ($field) {
            // fire Eloquent event
            $this->fireModelEvent("transitioning:$field", false);

            // fire package event
            TransitioningState::dispatch($this, $field);
        });

        MacroableModels::addMacro(static::class, 'fireTransitionedEvent', function ($field, $from) {
            // fire Eloquent event
            $this->fireModelEvent("transitioned:$field", false);

            // fire package event
            TransitionedState::dispatch($this, $field, $from);
        });

        self::creating(function (Model $model) {
            $model->initEnumata(true);
        });

        self::updating(function (Model $model) {
            $model->initEnumata();

            /** @var StateMachine */
            foreach ($model->getStateMachines() as $stateMachine) {
                $field = $stateMachine->getField();

                if ($model->isDirty($field)) {
                    $from = $model->getOriginal($field);
                    $to   = $model->{$field};

                    /** TODO: unify the validation logic of transitions */
                    $transitions = $from?->transitions();
                    if (!$transitions && in_array(Nullable::class, class_implements($to))) {
                        /** @var Nullable */
                        $nullableState = $to;

                        $transitions = $nullableState->initialTransitions();
                    }

                    if (!in_array($to, $transitions ?? [])) {
                        throw new TransitionNotAllowedException($from, $to, $model);
                    }
                }
            }
        });
    }

    public function initEnumata(bool $setDefaultValues = true): void
    {
        foreach ($this->getCasts() as $field => $castTo) {
            if (self::itDefineStates($castTo)) {
                $this->initStateMachineFor($field);
                if ($setDefaultValues) {
                    $this->{$field} = $this->{$field} ?? $castTo::default();
                }
            }
        }
    }

    public function getStateMachines(): array
    {
        return $this->stateMachines;
    }

    /**
     * Register a transitioning model event with the dispatcher.
     */
    public static function transitioning(string $field, QueuedClosure|Closure|string|array $callback): void
    {
        static::registerModelEvent("transitioning:$field", $callback);
    }

    /**
     * Register a transitioned model event with the dispatcher.
     */
    public static function transitioned(string $field, QueuedClosure|Closure|string|array $callback): void
    {
        static::registerModelEvent("transitioned:$field", $callback);
    }

    private static function itDefineStates(string $enumClass): bool
    {
        $enum = enum_exists($enumClass) ? new ReflectionEnum($enumClass) : null;

        return $enum && $enum->implementsInterface(DefineStates::class);
    }

    private function initStateMachineFor(string $field): void
    {
        if (empty($this->stateMachines[$field])) {
            $this->stateMachines[$field] = new StateMachine($this, $field);
        }
    }
}
