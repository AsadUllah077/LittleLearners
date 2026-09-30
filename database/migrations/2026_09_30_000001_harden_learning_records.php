<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('is_active')->default(true));
        Schema::table('child_progress', function (Blueprint $t) {
            $t->unsignedInteger('last_item_index')->default(0);
            $t->unique(['child_profile_id', 'lesson_id']);
        });
        Schema::table('child_rewards', fn (Blueprint $t) => $t->unique(['child_profile_id', 'reward_id']));
        Schema::table('learning_streaks', fn (Blueprint $t) => $t->unique('child_profile_id'));
        Schema::table('child_stars', fn (Blueprint $t) => $t->unique(['child_profile_id', 'reference_type', 'reference_id'], 'star_reference_unique'));
        Schema::table('learning_categories', fn (Blueprint $t) => $t->index(['is_active', 'sort_order']));
        Schema::table('lessons', fn (Blueprint $t) => $t->index(['learning_category_id', 'is_active', 'sort_order'], 'lesson_listing'));
    }

    public function down(): void
    {
        Schema::table('lessons', fn (Blueprint $t) => $t->dropIndex('lesson_listing'));
        Schema::table('learning_categories', fn (Blueprint $t) => $t->dropIndex(['is_active', 'sort_order']));
        Schema::table('child_stars', fn (Blueprint $t) => $t->dropUnique('star_reference_unique'));
        Schema::table('learning_streaks', fn (Blueprint $t) => $t->dropUnique(['child_profile_id']));
        Schema::table('child_rewards', fn (Blueprint $t) => $t->dropUnique(['child_profile_id', 'reward_id']));
        Schema::table('child_progress', function (Blueprint $t) {
            $t->dropUnique(['child_profile_id', 'lesson_id']);
            $t->dropColumn('last_item_index');
        });
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_active'));
    }
};
