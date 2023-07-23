<?php

namespace Norotaro\Enumata\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Str;
use Javoscript\MacroableModels\Facades\MacroableModels;
use Norotaro\Enumata\Contracts\DefineStates;
use Norotaro\Enumata\Contracts\Nullable;
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
                    if (empty($this->stateMachines[$field])) {
                        $this->stateMachines[$field] = new StateMachine($this, $field);
                    }

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
                            MacroableModels::addMacro(static::class, $transition, function () use ($field, $nextState) {
                                $this->{$field}()->transitionTo($nextState);
                            });
                        }

                        if (in_array(Nullable::class, class_implements($state))) {
                            $initialTransitions = $state->initialTransitions();
                            foreach ($initialTransitions as $transition => $nextState) {
                                MacroableModels::addMacro(static::class, $transition, function () use ($field, $nextState) {
                                    $this->{$field}()->transitionTo($nextState);
                                });
                            }
                        }
                    }
                }
            }
        }

        MacroableModels::addMacro(static::class, 'fireTransitioningEvent', function ($field) {
            $this->fireModelEvent("transitioning:$field", false);
        });

        MacroableModels::addMacro(static::class, 'fireTransitionedEvent', function ($field) {
            $this->fireModelEvent("transitioned:$field", false);
        });

        self::creating(function (Model $model) {
            $model->initStateMachines();
        });
    }

    public function initStateMachines(): void
    {
        foreach ($this->getCasts() as $field => $castTo) {
            if (self::itDefineStates($castTo)) {
                $this->{$field} = $this->{$field} ?? $castTo::default();
            }
        }
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
}
