<?php

use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use Norotaro\Enumata\EnumStateMachine;
use Norotaro\Enumata\Tests\TestModels\Order;
use Norotaro\Enumata\Tests\TestModels\OrderDeliveryStatus;
use Norotaro\Enumata\Tests\TestModels\OrderStatus;

beforeEach(function () {
    $this->model = new Order();
    $this->model->initEnumata();
});

describe('with not nullable status', function () {
    beforeEach(function () {
        $this->stateMachine = new EnumStateMachine($this->model, 'status');
    });

    it('returns the right current state', function () {
        $currentState = $this->stateMachine->currentState();

        expect($currentState)->toBe(OrderStatus::default());
    });

    it('returns true if a transition can be applied', function () {
        $canBePending = $this->stateMachine->canBe(OrderStatus::Pending);

        expect($canBePending)->toBe(true);
    });

    it('returns false if a transition cannot be applied', function () {
        $canBeFinished = $this->stateMachine->canBe(OrderStatus::Finished);

        expect($canBeFinished)->toBe(false);
    });

    it('allows transition to a correct state', function () {
        $to = OrderStatus::Pending;

        $this->stateMachine->transitionTo($to);

        $newState = $this->stateMachine->currentState();

        expect($newState)->toBe($to);
    });

    it('throws exception when transitioning to an incorrect state', function () {
        $this->stateMachine->transitionTo(OrderStatus::Finished);
    })->throws(TransitionNotAllowedException::class);

    it('does nothing when transitioning to the same state', function () {
        $this->stateMachine->transitionTo(OrderStatus::default());
    })->throwsNoExceptions();

    it('throws exception when transitioning from a final state', function () {
        $this->stateMachine->transitionTo(OrderStatus::Pending);
        $this->stateMachine->transitionTo(OrderStatus::Finished); // final state
        $this->stateMachine->transitionTo(OrderStatus::Pending);
    })->throws(TransitionNotAllowedException::class);

    it('allows forced transitions', function () {
        $this->stateMachine->transitionTo(OrderStatus::Finished, force: true);

        expect($this->stateMachine->currentState())->toBe(OrderStatus::Finished);
    });
});

describe('with nullable status', function () {
    beforeEach(function () {
        $this->stateMachine = new EnumStateMachine($this->model, 'delivery_status');
    });

    it('returns the right current state', function () {
        $currentState = $this->stateMachine->currentState();

        expect($currentState)->toBe(null);
    });

    it('returns true if an initial transition can be applied', function () {
        $canBePending = $this->stateMachine->canBe(OrderDeliveryStatus::Default);

        expect($canBePending)->toBe(true);
    });

    it('returns false if an initial transition cannot be applied', function () {
        $canBeFinished = $this->stateMachine->canBe(OrderDeliveryStatus::Pending);

        expect($canBeFinished)->toBe(false);
    });

    it('allows transition to a correct initial state', function () {
        $to = OrderDeliveryStatus::Default;

        $this->stateMachine->transitionTo($to);

        $newState = $this->stateMachine->currentState();

        expect($newState)->toBe($to);
    });

    it('throws exception when transitioning to an incorrect initial state', function () {
        $this->stateMachine->transitionTo(OrderStatus::Finished);
    })->throws(TransitionNotAllowedException::class);
});
