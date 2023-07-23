<?php

namespace Norotaro\Enumata\Providers;

use Illuminate\Support\ServiceProvider;
use Norotaro\Enumata\Console\Commands\ModelStateMakeCommand;

final class EnumataServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelStateMakeCommand::class,
            ]);
        }
    }
}
