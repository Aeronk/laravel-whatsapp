<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $blueprint) {
            // We use raw SQL because modifying enums in Laravel migrations can be tricky with some drivers
            $table = config('database.default') === 'mysql' ? 'whatsapp_messages' : 'whatsapp_messages';

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN status ENUM('pending', 'sent', 'delivered', 'read', 'failed', 'received', 'deleted') DEFAULT 'pending'");
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE whatsapp_messages MODIFY COLUMN status ENUM('pending', 'sent', 'delivered', 'read', 'failed') DEFAULT 'pending'");
        });
    }
};
