<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A log of every SMS / WhatsApp broadcast sent to parents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('session_id');
            $table->unsignedInteger('sender_id');
            $table->string('audience');
            $table->unsignedInteger('class_id')->nullable();
            $table->text('body');
            $table->string('channel');
            $table->json('recipients');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->string('status');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_messages');
    }
};
