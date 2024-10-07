<?php

namespace Norotaro\Enumata\Tests\TestModels;

use Illuminate\Database\Eloquent\Model;
use Norotaro\Enumata\Contracts\HasStateMachine;
use Norotaro\Enumata\Traits\EloquentHasStateMachines;

class Order extends Model implements HasStateMachine
{
    use EloquentHasStateMachines;

    protected $casts = [
        'status'          => OrderStatus::class,
        'delivery_status' => OrderDeliveryStatus::class,
    ];
}
