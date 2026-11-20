<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Final-year pupils' Common Entrance (secondary school entrance exam)
 * registration, score and secondary school placement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('common_entrance_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('session_id');
            $table->string('registration_number')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('admitted_school')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('common_entrance_candidates');
    }
};
