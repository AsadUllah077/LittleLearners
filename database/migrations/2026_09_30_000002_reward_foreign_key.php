<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = collect(Schema::getForeignKeys('child_rewards'))->contains(fn ($key) => in_array('reward_id', $key['columns']));
        if (! $exists) {
            Schema::table('child_rewards', fn (Blueprint $table) => $table->foreign('reward_id')->references('id')->on('rewards')->cascadeOnDelete());
        }
    }

    public function down(): void
    {
        $key = collect(Schema::getForeignKeys('child_rewards'))->first(fn ($key) => in_array('reward_id', $key['columns']));
        if ($key) {
            Schema::table('child_rewards', fn (Blueprint $table) => $table->dropForeign($key['name']));
        }
    }
};
