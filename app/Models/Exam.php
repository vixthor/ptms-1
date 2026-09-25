<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    /**
     * The fixed set of continuous-assessment exam types a course is graded
     * on. Report cards render one column per name that exists for a course,
     * in this order, so exam_name is restricted to these instead of free
     * text.
     */
    public const CA_EXAM_NAMES = ['1st CA', '2nd CA', '3rd CA', 'Exam'];

    protected $fillable = [
        'exam_name',
        'start_date',
        'end_date',
        'semester_id',
        'class_id',
        'course_id',
        'session_id',
    ];

    /**
     * Get the course.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the semester.
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
