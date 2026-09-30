<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date_of_birth')->nullable();
            $table->string('avatar')->nullable();
            $table->string('learning_level')->default('level-1');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profiles');
    }
};
