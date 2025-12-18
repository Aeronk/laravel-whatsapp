<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('profile_name')->nullable();
            $table->string('language', 10)->default('en');
            $table->json('metadata')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();

            $table->index('phone_number');
            $table->index('is_blocked');
            $table->index('last_interaction_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_users');
    }
};