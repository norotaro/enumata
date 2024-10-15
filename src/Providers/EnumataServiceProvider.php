<?php

namespace Norotaro\Enumata\Providers;

use Illuminate\Support\ServiceProvider;
use Norotaro\Enumata\Console\Commands\ModelStateMakeCommand;
use Norotaro\Enumata\Contracts\StateMachine;
use Norotaro\Enumata\EnumStateMachine;

final class EnumataServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(StateMachine::class, EnumStateMachine::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelStateMakeCommand::class,
            ]);
        }
    }
}
