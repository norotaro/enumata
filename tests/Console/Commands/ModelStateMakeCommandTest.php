<?php

use Illuminate\Support\Facades\File;
use Norotaro\Enumaton\Console\Commands\ModelStateMakeCommand;

it('can run the command successfully', function () {
    $this
        ->artisan(ModelStateMakeCommand::class, ['name' => 'Test'])
        ->assertSuccessful();
});

it('create the state class when called', function () {
    $this
        ->artisan(ModelStateMakeCommand::class, ['name' => 'Test'])
        ->assertSuccessful();

    $fileExists = File::exists(app_path('Models/Test.php'));

    expect($fileExists)->toBe(true);
});
