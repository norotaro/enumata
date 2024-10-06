<?php

use Javoscript\MacroableModels\Facades\MacroableModels;
use Norotaro\Enumata\Contracts\StateMachine;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use Norotaro\Enumata\Tests\TestModels\Order;
use Norotaro\Enumata\Tests\TestModels\OrderStatus;

beforeEach(function () {
    $this->model = new Order();
});

it('set default state values', function () {
    $this->model->initEnumata(true);

    expect($this->model->status)->toBe(OrderStatus::Default);
    expect($this->model->deliveryStatus)->toBe(null);
});

it('validate direct changes without transitions', function () {
    $this->model->save();

    $this->model->status = OrderStatus::Finished;
    $this->model->save();
})->throws(TransitionNotAllowedException::class);

describe('macros creation', function () {

    it('creates state machine getters', function () {
        expect(MacroableModels::modelHasMacro($this->model::class, 'status'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'deliveryStatus'))->toBe(true);

        expect($this->model->status())->toBeInstanceOf(StateMachine::class);
        expect($this->model->deliveryStatus())->toBeInstanceOf(StateMachine::class);
    });

    it('creates transition methods', function () {
        expect(MacroableModels::modelHasMacro($this->model::class, 'pay'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'end'))->toBe(true);

        expect(MacroableModels::modelHasMacro($this->model::class, 'initState'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'isPending'))->toBe(true);
        expect(MacroableModels::modelHasMacro($this->model::class, 'finish'))->toBe(true);
    });

    it('change status with transition methods', function () {
        $this->model->save();
        $this->model->pay();

        expect($this->model->status)->toBe(OrderStatus::Pending);
    });

    it('creates transition methods that allows forced transitions', function () {
        $this->model->save();
        $this->model->end(force: true);

        expect($this->model->status)->toBe(OrderStatus::Finished);
    });
});
