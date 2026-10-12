<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('section_id');
            $table->unsignedInteger('semester_id');
            $table->unsignedInteger('session_id');
            $table->string('house')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->unsignedInteger('attendance_days')->nullable();
            $table->unsignedInteger('days_school_opened')->nullable();
            $table->date('school_resumes_on')->nullable();
            $table->json('behavioural_ratings')->nullable();
            $table->text('class_teacher_comment')->nullable();
            $table->text('headteacher_comment')->nullable();
            $table->string('class_teacher_signature')->nullable();
            $table->string('headteacher_signature')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id', 'session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
