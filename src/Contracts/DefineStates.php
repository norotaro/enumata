<?php

namespace Norotaro\Enumata\Contracts;

interface DefineStates
{
    public function transitions(): array;

    public static function default(): ?self;
}
