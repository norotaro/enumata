<?php

namespace Norotaro\Enumaton\Contracts;

interface StateDefinitions
{
    public function allowedTransitions(): array;

    public static function default(): ?self;
}
