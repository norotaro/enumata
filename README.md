# Enumaton

State Machines for Eloquent models using Enums.

## Description

This package helps to implement State Machines to Eloquent models in an easy way using Enum files to represent all possible states and also to configure transitions.

## Instalation

```bash
composer require norotaro/enumaton
```

## Usage

Having a model with two state fields:

```php
$order->status; // 'pending', 'approved', 'declined' or 'processed'

$order->fulfillment; // null, 'pending', 'completed'
```

We need to create an `enum` file with the State Definitions for each field.
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

use Norotaro\Enumaton\Contracts\Nullable;
use Norotaro\Enumaton\Contracts\StateDefinitions;

enum OrderStatus implements StateDefinitions
{
    case Pending;
    case Approved;
    case Declined;
    case Processed;

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                self::Approved,
                self::Delined,
            ],
            self::Approved => [
                self::Processed,
            ]
        };
    }

    public static function default(): self
    {
        return self::Pending;
    }
}
```

### OrderFulfillment definition

And these are the status definitions for the `fulfillment` attribute which can be null:

```php
namespace App\Models;

use Norotaro\Enumaton\Contracts\Nullable;
use Norotaro\Enumaton\Contracts\StateDefinitions;

enum OrderFulfillment implements StateDefinitions, Nullable
{
    case Pending;
    case Completed;

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [
                self::Completed,
            ]
        };
    }

    public static function default(): ?self
    {
        return null;
    }

    public static function validInitialStates(): array
    {
        return [
            self::Pending,
        ];
    }
}
```
> `validInitialStates()` returns the list of valid states when the field is null.

### Configuring the model

In the model we have to register the `HasStateMachines` trait and then each `enum` in the `$casts` property:

```php
use Norotaro\Enumaton\Traits\HasStateMachines;

class Order extends Model
{
    use HasStateMachines;

    protected $casts = [
        'status' => OrderStatus::class,
        'fulfillment' => OrderFulfillment::class,
    ];
}
```

That's it! Now we can transition the states using the State Machines.

## Access the current state

If we access the attributes, Eloquent will return the `enum` object with the current state:

```php
$model = new Order;
$model->save();

$model->status; // App\Model\OrderStatus{name: "Pending"}
$model->fulfillment; // null
```

## Access the State Machine

To access the State Machine we only need to add parentheses to the attribute names:

```php
$model->status(); // Norotaro\Enumaton\StateMachine
$model->fulfillment(); // Norotaro\Enumaton\StateMachine
```

## Using the State Machine

### Transitioning

We can transition between states with the `transitionTo($state)` method:

```php
$model->status()->transitionTo(OrderStatus::Approved);
```

### Checking available transitions

```php
$model->status; // App\Model\OrderStatus{name: "Pending"}

$model->status()->canBe(OrderStatus::Approved); // true
$model->status()->canBe(OrderStatus::Processed); // false
```
