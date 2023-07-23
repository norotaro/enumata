<?php

namespace Norotaro\Enumata\Tests\Examples;

use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\DefineStates;

enum StateNullable implements DefineStates, Nullable
{
    case Default;
    case Pending;
    case Finished;

    public function transitions(): array
    {
        return match ($this) {
            self::Default => [
                'isPending' => self::Pending,
            ],
            self::Pending => [
                'finish' => self::Finished,
            ],
        };
    }

    public static function default(): ?self
    {
        return null;
    }

    public static function initialTransitions(): array
    {
        return [
            'initState' => self::Default,
        ];
    }
}
