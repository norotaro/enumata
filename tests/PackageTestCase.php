<?php

namespace Norotaro\Enumata\Tests;

use Javoscript\MacroableModels\MacroableModelsServiceProvider;
use Norotaro\Enumata\Providers\EnumataServiceProvider;
use Orchestra\Testbench\TestCase;

class PackageTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            EnumataServiceProvider::class,
            MacroableModelsServiceProvider::class,
        ];
    }
}
