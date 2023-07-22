<?php

namespace Norotaro\Enumaton\Tests\Examples;

use Norotaro\Enumaton\Contracts\StateDefinitions;

enum StateValues implements StateDefinitions
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

    public static function default(): self
    {
        return self::Default;
    }
}
