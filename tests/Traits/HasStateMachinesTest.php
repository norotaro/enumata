<?php

use Illuminate\Database\Eloquent\Model;
use Norotaro\Enumaton\Contracts\StateMachine;
use Norotaro\Enumaton\Tests\Examples\StateNullable;
use Norotaro\Enumaton\Tests\Examples\StateValues;
use Norotaro\Enumaton\Traits\HasStateMachines;

it('set default state values', function () {
    $model = Mockery::mock(HasStateMachines::class);
    $model
        ->shouldReceive('getCasts')
        ->andReturn([
            'status' => StateValues::class,
            'nullable_status' => StateNullable::class,
        ]);

    $model->initStateMachines();

    expect($model->status)->toBe(StateValues::Default);
    expect($model->nullable_status)->toBe(null);
});

it('creates macros', function () {
    $model = new class() extends Model
    {
        use HasStateMachines;

        protected $casts = [
            'status' => StateValues::class,
            'nullable_status' => StateNullable::class,
        ];
    };

    expect($model->status())->toBeInstanceOf(StateMachine::class);
    expect($model->nullable_status())->toBeInstanceOf(StateMachine::class);
    expect($model->nullableStatus())->toBeInstanceOf(StateMachine::class);
});
