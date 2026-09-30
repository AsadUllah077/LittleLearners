<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user=App\Models\User::where('email','admin@example.com')->where('role','admin')->first();
if($user && Illuminate\Support\Facades\Hash::check('password123',$user->password)) {
    $user->is_active=false;$user->save();echo "Disabled legacy administrator using the old default password. Run learning:admin to set a new password.\n";
}
