<?php

namespace Norotaro\Enumata\Tests\TestModels;

use Illuminate\Database\Eloquent\Model;
use Norotaro\Enumata\Traits\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    protected $casts = [
        'status'          => OrderStatus::class,
        'delivery_status' => OrderDeliveryStatus::class,
    ];
}
