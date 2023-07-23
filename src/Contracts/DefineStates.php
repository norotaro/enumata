<?php

namespace Norotaro\Enumaton\Contracts;

interface DefineStates
{
    public function transitions(): array;

    public static function default(): ?self;
}
