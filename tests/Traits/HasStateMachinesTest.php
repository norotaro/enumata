<?php

use Illuminate\Database\Eloquent\Model;
use Javoscript\MacroableModels\Facades\MacroableModels;
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

describe('macros creation', function () {
    beforeEach(function () {
        $this->model = new class() extends Model
        {
            use HasStateMachines;

            protected $casts = [
                'status' => StateValues::class,
                'nullable_status' => StateNullable::class,
            ];
        };
    });

    it('creates state machine getters', function () {
        expect(MacroableModels::modelHasMacro($this->model::class, 'status'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'nullable_status'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'nullableStatus'))->toBe(true);

        expect($this->model->status())->toBeInstanceOf(StateMachine::class);
        expect($this->model->nullable_status())->toBeInstanceOf(StateMachine::class);
        expect($this->model->nullableStatus())->toBeInstanceOf(StateMachine::class);
    });

    it('creates transition methods', function () {
        expect(MacroableModels::modelHasMacro($this->model::class, 'pay'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'end'))->toBe(true);

        expect(MacroableModels::modelHasMacro($this->model::class, 'initState'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'isPending'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'finish'))->toBe(true);
    });
});
