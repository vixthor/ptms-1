<?php

namespace App\Repositories;

use App\Interfaces\AssignedTeacherInterface;
use App\Models\AssignedTeacher;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use Illuminate\Support\Collection;

/**
 * Teachers are assigned to a whole class (primary school "class teacher")
 * and can manage every subject of that class.
 */
class AssignedTeacherRepository implements AssignedTeacherInterface
{
    /**
     * @param  array{teacher_id: int, class_id: int, session_id: int}  $request
     */
    public function assign($request)
    {
        try {
            AssignedTeacher::updateOrCreate(
                [
                    'session_id' => $request['session_id'],
                    'class_id' => $request['class_id'],
                ],
                [
                    'teacher_id' => $request['teacher_id'],
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to assign teacher. '.$e->getMessage());
        }
    }

    /**
     * Every subject (for the given term) of every class the teacher is assigned to.
     * Each row exposes ->course, ->schoolClass, ->section and ->course_id.
     */
    public function getTeacherCourses($session_id, $teacher_id, $semester_id): Collection
    {
        if ($semester_id == 0) {
            $semester_id = optional(Semester::where('session_id', $session_id)->first())->id;
        }

        $assignments = AssignedTeacher::with('schoolClass')
            ->where('session_id', $session_id)
            ->where('teacher_id', $teacher_id)
            ->get()
            ->filter(fn (AssignedTeacher $assignment) => $assignment->schoolClass !== null)
            ->unique('class_id');

        return $assignments->flatMap(function (AssignedTeacher $assignment) use ($semester_id) {
            $section = Section::defaultForClass($assignment->schoolClass);

            return Course::where('class_id', $assignment->class_id)
                ->where('semester_id', $semester_id)
                ->orderBy('course_name')
                ->get()
                ->map(function (Course $course) use ($assignment, $section) {
                    $row = new AssignedTeacher([
                        'teacher_id' => $assignment->teacher_id,
                        'class_id' => $assignment->class_id,
                        'section_id' => $section->id,
                        'course_id' => $course->id,
                        'semester_id' => $course->semester_id,
                        'session_id' => $assignment->session_id,
                    ]);

                    return $row->setRelation('course', $course)
                        ->setRelation('schoolClass', $assignment->schoolClass)
                        ->setRelation('section', $section);
                });
        })->values();
    }

    /**
     * The class teacher of a class; term, section and subject no longer
     * narrow the assignment and are accepted only for interface compatibility.
     */
    public function getAssignedTeacher($session_id, $semester_id, $class_id, $section_id, $course_id)
    {
        return AssignedTeacher::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->first();
    }

    /**
     * The class teacher of every class in the session, keyed by class id.
     *
     * @return Collection<int, AssignedTeacher>
     */
    public function getClassTeachersBySession($session_id): Collection
    {
        return AssignedTeacher::with('teacher')
            ->where('session_id', $session_id)
            ->get()
            ->keyBy('class_id');
    }
}
