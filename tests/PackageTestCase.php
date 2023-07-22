<?php

namespace Norotaro\Enumaton\Tests;

use Javoscript\MacroableModels\MacroableModelsServiceProvider;
use Norotaro\Enumaton\Providers\EnumatonServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EnumatonServiceProvider::class,
            MacroableModelsServiceProvider::class,
        ];
    }
}
