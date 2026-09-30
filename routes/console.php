<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('learning:admin {email} {name=Administrator}', function () {
    $password = $this->secret('Choose a password (at least 12 characters)');
    if (strlen($password ?? '') < 12) {
        $this->error('Password must contain at least 12 characters.');

        return 1;
    }
    if (! filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL)) {
        $this->error('A valid email address is required.');

        return 1;
    }
    $user = User::firstOrNew(['email' => $this->argument('email')]);
    $user->fill(['name' => $this->argument('name'), 'password' => $password, 'role' => 'admin']);
    $user->is_active = true;
    $user->save();
    $this->info('Administrator account saved.');
})->purpose('Create or update an administrator with an interactive password');
