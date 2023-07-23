<?php

namespace Norotaro\Enumata\Tests\Examples;

use Norotaro\Enumata\Contracts\DefineStates;

enum StateValues implements DefineStates
{
    case Default;
    case Pending;
    case Finished;

    public function transitions(): array
    {
        return match ($this) {
            self::Default => [
                'pay' => self::Pending,
            ],
            self::Pending => [
                'end' => self::Finished,
            ]
        };
    }

    public static function default(): self
    {
        return self::Default;
    }
}
