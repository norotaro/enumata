<?php

namespace Norotaro\Enumata\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Norotaro\Enumata\Contracts\DefineStates;
use UnitEnum;

class TransitionedState
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Model $model,
        public string $field,
        public DefineStates&UnitEnum $from,
    ) {
    }
}
