<?php

use App\Models\Section;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Primary school setup: teachers are assigned to a whole class (not to a
 * subject/term/section), and every class has exactly one hidden default
 * section so section-keyed records keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assigned_teachers', function (Blueprint $table) {
            $table->unsignedInteger('semester_id')->nullable()->change();
            $table->unsignedInteger('section_id')->nullable()->change();
            $table->unsignedInteger('course_id')->nullable()->change();
        });

        $classesWithoutSection = DB::table('school_classes')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('sections')
                    ->whereColumn('sections.class_id', 'school_classes.id');
            })
            ->get(['id', 'session_id']);

        foreach ($classesWithoutSection as $schoolClass) {
            DB::table('sections')->insert([
                'section_name' => Section::DEFAULT_NAME,
                'room_no' => '',
                'class_id' => $schoolClass->id,
                'session_id' => $schoolClass->session_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('assigned_teachers')->whereNull('semester_id')->update(['semester_id' => 0]);
        DB::table('assigned_teachers')->whereNull('section_id')->update(['section_id' => 0]);
        DB::table('assigned_teachers')->whereNull('course_id')->update(['course_id' => 0]);

        Schema::table('assigned_teachers', function (Blueprint $table) {
            $table->unsignedInteger('semester_id')->nullable(false)->change();
            $table->unsignedInteger('section_id')->nullable(false)->change();
            $table->unsignedInteger('course_id')->nullable(false)->change();
        });
    }
};
