<?php

namespace Norotaro\Enumaton\Tests\Examples;

use Norotaro\Enumaton\Contracts\Nullable;
use Norotaro\Enumaton\Contracts\StateDefinitions;

enum StateNullable implements StateDefinitions, Nullable
{
    case Default;
    case Pending;
    case Finished;

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Default => [
                self::Pending,
            ],
            self::Pending => [
                self::Finished,
            ]
        };
    }

    public static function default(): ?self
    {
        return null;
    }

    public static function validInitialStates(): array
    {
        return [
            self::Default,
        ];
    }
}
