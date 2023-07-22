<?php

namespace Norotaro\Enumaton\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\QueuedClosure;
use Illuminate\Support\Str;
use Javoscript\MacroableModels\Facades\MacroableModels;
use Norotaro\Enumaton\Contracts\StateDefinitions;
use Norotaro\Enumaton\StateMachine;
use ReflectionEnum;

trait HasStateMachines
{
    protected array $stateMachines = [];

    public static function bootHasStateMachines()
    {
        // define state machine getters
        $model = new static();
        foreach ($model->getCasts() as $field => $castTo) {
            if (self::isEnumAndImplementsState($castTo)) {
                $getStateMachine = function () use ($field) {
                    if (empty($this->stateMachines[$field])) {
                        $this->stateMachines[$field] = new StateMachine($this, $field);
                    }

                    return $this->stateMachines[$field];
                };

                $camelField = Str::of($field)->camel();

                MacroableModels::addMacro(static::class, $field, $getStateMachine);

                if ($field !== $camelField) {
                    MacroableModels::addMacro(static::class, $camelField, $getStateMachine);
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
            if (self::isEnumAndImplementsState($castTo)) {
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

    private static function isEnumAndImplementsState(string $enumClass): bool
    {
        $enum = enum_exists($enumClass) ? new ReflectionEnum($enumClass) : null;

        return $enum && $enum->implementsInterface(StateDefinitions::class);
    }
}
