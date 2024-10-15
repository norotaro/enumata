<?php

namespace Norotaro\Enumata\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\QueuedClosure;
use Javoscript\MacroableModels\Facades\MacroableModels;
use Norotaro\Enumata\Contracts\DefineStates;
use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\StateMachine;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use ReflectionEnum;

trait EloquentHasStateMachines
{
    public bool $defaultTransitionMethods = true;

    protected array $stateMachines = [];

    public static function bootEloquentHasStateMachines(): void
    {
        $model = new static();

        foreach ($model->getCasts() as $field => $castTo) {
            if (!self::itDefineStates($castTo)) {
                continue;
            }

            self::defineStateMachineGetter($field);

            if ($model->defaultTransitionMethods) {
                self::createTransitionMethods($field, $castTo);
            }
        }

        static::creating(fn (Model $model) => $model->initEnumata(setDefaultValues: true));

        static::updating(fn (Model $model) => static::guardStateMachine($model));
    }

    protected static function defineStateMachineGetter(string $field): void
    {
        $getStateMachine = function () use ($field) {
            $this->initStateMachineFor($field);

            return $this->stateMachines[$field];
        };

        $camelCase = str($field)->camel();
        MacroableModels::addMacro(static::class, $camelCase, $getStateMachine);
    }

    protected static function createTransitionMethods($field, $castTo): void
    {
        foreach ($castTo::cases() as $state) {
            try {
                $transitions = $state->transitions();
            } catch (\UnhandledMatchError) {
                $transitions = [];
            }

            $transitions ??= [];

            if (in_array(Nullable::class, class_implements($state))) {
                $transitions = [
                    ...$transitions,
                    ...$state->initialTransitions(),
                ];
            }

            foreach ($transitions as $transition => $nextState) {
                MacroableModels::addMacro(
                    static::class,
                    $transition,
                    function (bool $force = false) use ($field, $nextState) {
                        $this->{$field}()->transitionTo($nextState, $force);
                    }
                );
            }
        }
    }

    protected static function guardStateMachine(Model $model): void
    {
        $model->initEnumata(setDefaultValues: false);

        /** @var StateMachine $stateMachine */
        foreach ($model->getStateMachines() as $stateMachine) {
            $field = $stateMachine->getField();

            if (!$model->isDirty($field)) {
                continue;
            }

            if ($stateMachine->isTransitioning()) {
                continue;
            }

            $from = $model->getOriginal($field);
            $to = $model->{$field};

            if (!$stateMachine->canBe($to)) {
                throw new TransitionNotAllowedException($from, $to, $stateMachine, self::class);
            }
        }
    }

    public function initEnumata(bool $setDefaultValues = true): void
    {
        foreach ($this->getCasts() as $field => $castTo) {
            if (!self::itDefineStates($castTo)) {
                continue;
            }

            $this->initStateMachineFor($field);

            if ($setDefaultValues) {
                $this->{$field} = $this->{$field} ?? $castTo::default();
            }
        }
    }

    public function getStateMachines(): array
    {
        return $this->stateMachines;
    }

    public static function transitioning(string $field, QueuedClosure|Closure|string|array $callback): void
    {
        static::registerModelEvent("transitioning:{$field}", $callback);
    }

    public static function transitioned(string $field, QueuedClosure|Closure|string|array $callback): void
    {
        static::registerModelEvent("transitioned:{$field}", $callback);
    }

    protected static function itDefineStates($castTo): bool
    {
        $enum = enum_exists($castTo)
            ? new ReflectionEnum($castTo)
            : null;

        return $enum && $enum->implementsInterface(DefineStates::class);
    }

    protected function initStateMachineFor(string $field): void
    {
        if (!array_key_exists($field, $this->stateMachines)) {
            $this->stateMachines[$field] = app()->makeWith(StateMachine::class, [
                'hasStateMachine' => $this,
                'field' => $field,
            ]);
        }
    }

    public function persist(): void
    {
        $this->save();
    }

    public function fireTransitioningEvent(string $field): void
    {
        $this->fireModelEvent("transitioning:{$field}", false);
    }

    public function fireTransitionedEvent(string $field): void
    {
        $this->fireModelEvent("transitioned:{$field}", false);
    }
}
