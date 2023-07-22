<?php

namespace Norotaro\Enumaton\Providers;

use Illuminate\Support\ServiceProvider;
use Norotaro\Enumaton\Console\Commands\ModelStateMakeCommand;

final class EnumatonServiceProvider extends ServiceProvider
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
