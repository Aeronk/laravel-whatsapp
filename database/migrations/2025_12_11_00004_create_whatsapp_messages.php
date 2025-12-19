<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_user_id')->constrained()->onDelete('cascade');
            $table->foreignId('whatsapp_session_id')->nullable()->constrained()->onDelete('set null');
            $table->string('message_id')->unique();
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact', 'interactive', 'template', 'reaction', 'sticker', 'unknown'])->default('text');
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed', 'received', 'deleted'])->default('pending');
            $table->json('content');
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['whatsapp_user_id', 'direction']);
            $table->index(['whatsapp_session_id']);
            $table->index('message_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};