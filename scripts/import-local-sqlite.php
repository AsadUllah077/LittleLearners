<?php
// One-time local migration; the SQLite source is never modified.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

if (app()->environment('production')) throw new RuntimeException('Local import is disabled in production.');
config(['database.connections.sqlite.database'=>database_path('database.sqlite')]);
DB::purge('sqlite');
$source=DB::connection('sqlite');
DB::purge('mysql');
$target=DB::connection('mysql');
$tables=['users','child_profiles','learning_categories','lessons','learning_items','learning_activities','child_progress','activity_attempts','rewards','child_rewards','child_stars','learning_streaks'];
$target->transaction(function() use($source,$target,$tables) {
    foreach($tables as $table) {
        if($target->table($table)->count()) throw new RuntimeException('Target must be empty: '.$table);
        foreach($source->table($table)->orderBy('id')->get()->chunk(100) as $chunk) $target->table($table)->insert($chunk->map(fn($row)=>(array)$row)->all());
    }
});
foreach($tables as $table) {
    if($source->table($table)->count()!==$target->table($table)->count()) throw new RuntimeException('Count mismatch: '.$table);
}
echo "Imported and verified all learning tables. SQLite retained.\n";
