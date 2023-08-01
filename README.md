# Enumata

[![Latest Version](https://img.shields.io/packagist/v/norotaro/enumata.svg?label=release)](https://packagist.org/packages/norotaro/enumata)
[![Tests](https://github.com/norotaro/enumata/actions/workflows/test.yaml/badge.svg)](https://github.com/norotaro/enumata/actions/workflows/test.yaml)
[![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/norotaro/enumata/php?logo=php&color=%23AEB2D5)](https://php.net)
[![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/norotaro/enumata/illuminate%2Fsupport?logo=laravel&label=Laravel&color=%23F05340)](https://laravel.com)

State Machines for Eloquent models using Enums.

## Description

This package helps to implement State Machines to Eloquent models in an easy way using Enum files to represent all possible states and also to configure transitions.

## Live demo

You can check the [norotaro/enumata-demo](https://github.com/norotaro/enumata-demo) repository or go to the live version of the demo in this [PHP Sandbox](https://phpsandbox.io/e/x/ultlf?layout=EditorPreview&defaultPath=%2F&theme=dark&showExplorer=no&openedFiles=#app/Models/OrderStatus.php).

## Installation

```bash
composer require norotaro/enumata
```

## Usage

Having a model with two state fields:

```php
$order->status; // 'pending', 'approved', 'declined' or 'processed'

$order->fulfillment; // null, 'pending', 'completed'
```

We need to create an `enum` file with the State Definitions for each field.
The file to define the `status` field will be called `OrderStatus` and the file to define the `fulfillment` field will be called `OrderFulfillment`.
We can do this with the `make:model-state` command:

```bash
php artisan make:model-state OrderStatus
```

```bash
php artisan make:model-state OrderFulfillment --nullable
```
> Since the `fulfillment` attribute can be null, we use the `--nullable` option to generate a more appropriate file.

### OrderStatus definition

The above command will create a default file that we can adapt to meet our requirements:

```php
namespace App\Models;

use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\DefineStates;

enum OrderStatus implements DefineStates
{
    case Pending;
    case Approved;
    case Declined;
    case Processed;

    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [
                'approve' => self::Approved,
                'decline' => self::Delined,
            ],
            self::Approved => [
                'processOrder' => self::Processed,
            ],
        };
    }

    public static function default(): self
    {
        return self::Pending;
    }
}
```

The `transitions()` method must return an array with `key=>value` where the key is the name of the transition and the value is the state to apply in that transition.

> Note that, by default, methods will be created in the model for each transition. In the case of the example, the `approve()`, `decline()` and `processOrder()` methods will be created.

### OrderFulfillment definition

And these are the states definitions for the `fulfillment` attribute which can be null:

```php
namespace App\Models;

use Norotaro\Enumata\Contracts\Nullable;
use Norotaro\Enumata\Contracts\DefineStates;

enum OrderFulfillment implements DefineStates, Nullable
{
    case Pending;
    case Completed;

    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [
                'completeFulfillment' => self::Completed,
            ],
        };
    }

    public static function default(): ?self
    {
        return null;
    }

    public static function initialTransitions(): array
    {
        return [
            'initFulfillment' => self::Pending,
        ];
    }
}
```
The `initialTransitions()` method must return the list of available transitions when the field is null.

> As with `transitions()`, by default methods will be created with the name of the keys returned by `initialTransitions()`.

### Configuring the model

In the model we have to register the `HasStateMachines` trait and then each `enum` in the `$casts` property:

```php
use Norotaro\Enumata\Traits\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    protected $casts = [
        'status'      => OrderStatus::class,
        'fulfillment' => OrderFulfillment::class,
    ];
}
```

That's it! Now we can transition between the states.

## Access the current state

If you access the attributes, Eloquent will return the `enum` object with the current state:

```php
$model = new Order;
$model->save();

$model->status; // App\Model\OrderStatus{name: "Pending"}
$model->fulfillment; // null
```

## Transitioning

By default this package will create methods in the model for each transition returned by `transitions()` and `initialTransitions()` so, for this example, we will have these methods available:

```php
$model->approve(); // Change status to OrderStatus::Approved
$model->decline(); // Change status to OrderStatus::Declined
$model->processOrder(); // Change status to OrderStatus::Processed

$model->initFulfillment(); // Change fulfillment to OrderFulfillment::Pending
$model->completeFulfillment(); // Change fulfillment to OrderFulfillment::Completed
```

### Disable default transition methods

You can disable the creation of transition methods by making the `$defaultTransitionMethods` attribute of the model `false`.

Internally these methods use the `transitionTo($state)` method available in the `StateMachine` class, so you can implement your custom transition methods with it.

```php
class Order extends Model
{
    use HasStateMachines;

    public bool $defaultTransitionMethods = false;

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function approve(): void {
        $this->status()->transitionTo(OrderStatus::Approved);
        //...
    }
}
```

### Transition not allowed exception

If a transition is applied and the current state does not allow it, the `TransitionNotAllowedException` will be thrown.

```php
$model->status; // App\Model\OrderStatus{name: "Pending"}
$model->processOrder(); // throws Norotaro\Enumata\Exceptions\TransitionNotAllowedException
```

## The State Machine

To access the State Machine we only need to add parentheses to the attribute names:

```php
$model->status(); // Norotaro\Enumata\StateMachine
$model->fulfillment(); // Norotaro\Enumata\StateMachine
```

> If the attribute uses underscore such as `my_attribute`, you can access the state machine using `my_attribute()` or `myAttribute()`.

### Using the State Machine

#### Transitioning

We can transition between states with the `transitionTo($state)` method:

```php
$model->status()->transitionTo(OrderStatus::Approved);
```

#### Checking available transitions

```php
$model->status; // App\Model\OrderStatus{name: "Pending"}

$model->status()->canBe(OrderStatus::Approved); // true
$model->status()->canBe(OrderStatus::Processed); // false
```
## Events

This package adds two new events to those dispatched by Eloquent by default and can be used in the same way.

> More information about Eloquent Events can be found in the [official documentation](https://laravel.com/docs/10.x/eloquent#events).

- `transitioning:{attribute}`: This event is dispatched before saving the transition to a new state.
- `transitioned:{attribute}`: This event is dispatched after saving the transition to a new state.

In the `transitioning` event you can access the original and the new state in this way:

```php
$from = $order->getOriginal('fulfillment'); // App\Model\OrderFulfillment{name: "Pending"}
$to   = $order->fulfillment; // App\Model\OrderFulfillment{name: "Complete"}
```

### Listening to events using `$dispatchesEvents`

```php
use App\Events\TransitionedOrderFulfillment;
use App\Events\TransitioningOrderStatus;
use Norotaro\Enumata\Traits\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    protected $casts = [
        'status'      => OrderStatus::class,
        'fulfillment' => OrderFulfillment::class,
    ];

    protected $dispatchesEvents = [
        'transitioning:status'     => TransitioningOrderStatus::class,
        'transitioned:fulfillment' => TransitionedOrderFulfillment::class,
    ];
}
```

### Listening to events using Closures

The `transitioning($field, $callback)` and `transitioned($field, $callback)` methods help to register closures.

> Note that the first parameter must be the name of the field we want to listen to.

```php
use App\Events\TransitionedOrderFulfillment;
use App\Events\TransitioningOrderStatus;
use Norotaro\Enumata\Traits\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    protected $casts = [
        'status'      => OrderStatus::class,
        'fulfillment' => OrderFulfillment::class,
    ];

    protected static function booted(): void
    {
        static::transitioning('fulfillment', function (Order $order) {
            $from = $order->getOriginal('fulfillment');
            $to   = $order->fulfillment;

            \Log::debug('Transitioning fulfillment field', [
                'from' => $from->name,
                'to' => $to->name,
            ]);
        });

        static::transitioned('status', function (Order $order) {
            \Log::debug('Order status transitioned to ' . $order->status->name);
        });
    }
}
```

## Testing

To run the test suite:

```php
composer run test
```

## Inspiration

This package was inspired by [asantibanez/laravel-eloquent-state-machines](https://github.com/asantibanez/laravel-eloquent-state-machines).

## LICENSE

The MIT License (MIT). Please see [License File](./LICENSE) for more information.
