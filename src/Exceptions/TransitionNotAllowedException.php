<?php

namespace Norotaro\Enumata\Exceptions;

use Exception;
use Norotaro\Enumata\Contracts\StateMachine;
use UnitEnum;

class TransitionNotAllowedException extends Exception
{
    public function __construct(
        ?UnitEnum $from,
        UnitEnum $to,
        StateMachine $stateMachine,
        mixed $hasStateMachine
    ) {
        $message = sprintf(
            'Transition from %s to %s is not allowed for %s in %s',
            $from->value ?? $from->name ?? 'null',
            $to->value ?? $to->name,
            $stateMachine->getField(),
            $hasStateMachine
        );

        parent::__construct($message);
    }
}
