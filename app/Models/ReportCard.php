<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'semester_id',
        'session_id',
        'house',
        'weight',
        'height',
        'attendance_days',
        'days_school_opened',
        'school_resumes_on',
        'behavioural_ratings',
        'class_teacher_comment',
        'headteacher_comment',
        'class_teacher_signature',
        'headteacher_signature',
    ];

    protected function casts(): array
    {
        return [
            'behavioural_ratings' => 'array',
            'school_resumes_on' => 'date',
        ];
    }

    /**
     * Get the student.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the schoolClass.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the semester.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
