<?php

use Illuminate\Database\Eloquent\Model;
use Norotaro\Enumata\Exceptions\TransitionNotAllowedException;
use Norotaro\Enumata\StateMachine;
use Norotaro\Enumata\Tests\Examples\StateNullable;
use Norotaro\Enumata\Tests\Examples\StateValues;

beforeEach(function () {
    $this->model = Mockery::mock(Model::class);
    $this->stateMachine = new StateMachine($this->model, 'status');
});

describe('with Status', function () {
    beforeEach(function () {
        $this->defaultState = StateValues::default();
        $this->model
            ->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn($this->defaultState);
    });

    it('returns the right current state', function () {
        $currentState = $this->stateMachine->currentState();

        expect($currentState)->toBe($this->defaultState);
    });

    it('returns true if a transition can be applied', function () {
        $canBePending = $this->stateMachine->canBe(StateValues::Pending);

        expect($canBePending)->toBe(true);
    });

    it('returns false if a transition cannot be applied', function () {
        $canBeFinished = $this->stateMachine->canBe(StateValues::Finished);

        expect($canBeFinished)->toBe(false);
    });

    it('allows transition to a correct state', function () {
        $to = StateValues::Pending;
        $this->model
            ->shouldReceive('setAttribute')
            ->once()
            ->with('status', $to)
            ->andSet('status', $to)
            ->shouldReceive('fireTransitioningEvent')
            ->once()
            ->shouldReceive('fireTransitionedEvent')
            ->once()
            ->shouldReceive('save')
            ->once();

        $this->stateMachine->transitionTo($to);

        $newState = $this->stateMachine->currentState();

        expect($newState)->toBe($to);
    });

    it('throws exception when transitioning to an incorrect state', function () {
        $this->stateMachine->transitionTo(StateValues::Finished);
    })->throws(TransitionNotAllowedException::class);

    it('does nothing when transitioning to the same state', function () {
        $this->stateMachine->transitionTo($this->defaultState);
    })->throwsNoExceptions();
});

describe('with NullableStatus', function () {
    beforeEach(function () {
        $this->model
            ->shouldReceive('getAttribute')
            ->with('status')
            ->andReturn(null);
    });

    it('returns the right current state', function () {
        $currentState = $this->stateMachine->currentState();

        expect($currentState)->toBe(null);
    });

    it('returns true if an initial transition can be applied', function () {
        $canBePending = $this->stateMachine->canBe(StateNullable::Default);

        expect($canBePending)->toBe(true);
    });

    it('returns false if an initial transition cannot be applied', function () {
        $canBeFinished = $this->stateMachine->canBe(StateNullable::Pending);

        expect($canBeFinished)->toBe(false);
    });

    it('allows transition to a correct initial state', function () {
        $to = StateNullable::Default;
        $this->model
            ->shouldReceive('setAttribute')
            ->once()
            ->with('status', $to)
            ->andSet('status', $to)
            ->shouldReceive('fireTransitioningEvent')
            ->once()
            ->shouldReceive('fireTransitionedEvent')
            ->once()
            ->shouldReceive('save')
            ->once();

        $this->stateMachine->transitionTo($to);

        $newState = $this->stateMachine->currentState();

        expect($newState)->toBe($to);
    });

    it('throws exception when transitioning to an incorrect initial state', function () {
        $this->stateMachine->transitionTo(StateValues::Finished);
    })->throws(TransitionNotAllowedException::class);
});
